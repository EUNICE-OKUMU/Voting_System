<?php
include 'includes/header.php';
require_once 'Database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/index.php');
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate current password if trying to change password
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }
    }
    
    // Validate email if changed
    if ($email !== $user['email']) {
        $sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    if (empty($errors)) {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?";
        $params = [$first_name, $last_name, $email];
        $types = "sss";
        
        if (!empty($new_password)) {
            $sql .= ", password = ?";
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        $sql .= " WHERE user_id = ?";
        $params[] = $user_id;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully";
            // Refresh user data
            $sql = "SELECT * FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $errors[] = "Error updating profile";
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">My Profile</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="admission_number" class="form-label">Admission Number</label>
                            <input type="text" class="form-control" id="admission_number" 
                                   value="<?php echo htmlspecialchars($user['admission_number']); ?>" readonly>
                        </div>

                        <hr>

                        <h4 class="mb-3">Change Password</h4>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Voting History -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="mb-0">Voting History</h2>
                </div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT v.*, c.first_name, c.last_name, p.title as position_title 
                            FROM votes v 
                            JOIN candidates c ON v.candidate_id = c.candidate_id 
                            JOIN positions p ON c.Position = p.title 
                            WHERE v.user_id = ? 
                            ORDER BY v.created_at DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $votes = $stmt->get_result();
                    ?>

                    <?php if ($votes->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Candidate</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($vote = $votes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($vote['position_title']); ?></td>
                                            <td><?php echo htmlspecialchars($vote['first_name'] . ' ' . $vote['last_name']); ?></td>
                                            <td><?php echo date('F j, Y', strtotime($vote['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No voting history found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?> 