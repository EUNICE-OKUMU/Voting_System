<?php
session_start();
require_once '../../Database/db.php';

// Check if user is logged in as voter
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
    header('Location: ../../Auth/index.php');
    exit();
}

$error = '';
$success = '';

// Fetch user details
$user_sql = "SELECT * FROM users WHERE SN = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year = (int)$_POST['year'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($course)) {
        $error = "Please fill in all required fields";
    } elseif ($year < 1 || $year > 4) {
        $error = "Invalid year of study";
    } elseif (!empty($new_password)) {
        if (empty($current_password)) {
            $error = "Please enter your current password";
        } elseif (!password_verify($current_password, $user['Pass'])) {
            $error = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long";
        } else {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET First_Name = ?, Last_Name = ?, Email = ?, Course = ?, Year_of_Study = ?, Pass = ? WHERE SN = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssisi", $first_name, $last_name, $email, $course, $year, $hashed_password, $_SESSION['user_id']);
        }
    } else {
        // Update without changing password
        $sql = "UPDATE users SET First_Name = ?, Last_Name = ?, Email = ?, Course = ?, Year_of_Study = ? WHERE SN = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $first_name, $last_name, $email, $course, $year, $_SESSION['user_id']);
    }

    if (empty($error) && isset($stmt)) {
        if ($stmt->execute()) {
            $success = "Profile updated successfully";
            // Update session variables
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            // Refresh user data
            $user_stmt->execute();
        } else {
            $error = "Error updating profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Voter Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.8);
        }
        .nav-link:hover {
            color: white;
        }
        .nav-link.active {
            background: rgba(255,255,255,.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 position-fixed sidebar">
                <div class="p-3">
                    <h4 class="text-center mb-4">Voter Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                       
                        <li class="nav-item">
                            <a class="nav-link active" href="profile.php">
                                <i class="fas fa-user me-2"></i> My Profile
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="../../Auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Profile</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['First_Name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['Last_Name']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="adm_no" class="form-label">Student ID Number</label>
                                <input type="text" class="form-control" id="adm_no" value="<?php echo htmlspecialchars($user['Adm_No']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">University Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="course" class="form-label">Course</label>
                                <input type="text" class="form-control" id="course" name="course" 
                                       value="<?php echo htmlspecialchars($user['Course']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="year" class="form-label">Year of Study</label>
                                <input type="number" class="form-control" id="year" name="year" 
                                       value="<?php echo htmlspecialchars($user['Year_of_Study']); ?>" 
                                       min="1" max="4" required>
                            </div>

                            <hr>

                            <h5 class="mb-3">Change Password</h5>
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

                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 