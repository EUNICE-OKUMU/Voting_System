<?php
session_start();
require_once '../../Database/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../Auth/index.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update' && isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $course = trim($_POST['course']);
            $year_of_study = (int)$_POST['year_of_study'];
            $status = $_POST['status'];

            // Check if email is already taken by another user
            $check_sql = "SELECT SN FROM users WHERE Email = ? AND SN != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email is already taken by another user";
            } else {
                $sql = "UPDATE users SET First_Name = ?, Last_Name = ?, Email = ?, Course = ?, Year_of_Study = ?, status = ? WHERE SN = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssisi", $first_name, $last_name, $email, $course, $year_of_study, $status, $user_id);

                if ($stmt->execute()) {
                    $success = "Voter information updated successfully";
                } else {
                    $error = "Error updating voter information";
                }
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];

            // Check if user has voted
            $check_sql = "SELECT COUNT(*) as count FROM votes WHERE user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();

            if ($result['count'] > 0) {
                $error = "Cannot delete voter who has already voted";
            } else {
                $sql = "DELETE FROM users WHERE SN = ? AND role = 'voter'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);

                if ($stmt->execute()) {
                    $success = "Voter deleted successfully";
                } else {
                    $error = "Error deleting voter";
                }
            }
        }
    }
}

// Fetch all voters
$voters = $conn->query("SELECT * FROM users WHERE role = 'voter' ORDER BY First_Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters - Admin Dashboard</title>
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
                    <h4 class="text-center mb-4">Admin Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_elections.php">
                                <i class="fas fa-poll me-2"></i> Manage Elections
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="candidates.php">
                                <i class="fas fa-user-tie me-2"></i> Manage Candidates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="positions.php">
                                <i class="fas fa-briefcase me-2"></i> Manage Positions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="voters.php">
                                <i class="fas fa-users me-2"></i> Manage Voters
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="results.php">
                                <i class="fas fa-chart-bar me-2"></i> View Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i> Settings
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
                    <h2>Manage Voters</h2>
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

                <!-- Voters List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Registered Voters</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Admission No.</th>
                                        <th>Email</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($voter = $voters->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($voter['First_Name'] . ' ' . $voter['Last_Name']); ?></td>
                                            <td><?php echo htmlspecialchars($voter['Adm_No']); ?></td>
                                            <td><?php echo htmlspecialchars($voter['Email']); ?></td>
                                            <td><?php echo htmlspecialchars($voter['Course']); ?></td>
                                            <td><?php echo $voter['Year_of_Study']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $voter['status'] === 'active' ? 'success' : 
                                                        ($voter['status'] === 'inactive' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($voter['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($voter['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $voter['SN']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this voter?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $voter['SN']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $voter['SN']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Voter Information</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="user_id" value="<?php echo $voter['SN']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="edit_first_name<?php echo $voter['SN']; ?>" class="form-label">First Name</label>
                                                                <input type="text" class="form-control" id="edit_first_name<?php echo $voter['SN']; ?>" 
                                                                       name="first_name" value="<?php echo htmlspecialchars($voter['First_Name']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_last_name<?php echo $voter['SN']; ?>" class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" id="edit_last_name<?php echo $voter['SN']; ?>" 
                                                                       name="last_name" value="<?php echo htmlspecialchars($voter['Last_Name']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_email<?php echo $voter['SN']; ?>" class="form-label">Email</label>
                                                                <input type="email" class="form-control" id="edit_email<?php echo $voter['SN']; ?>" 
                                                                       name="email" value="<?php echo htmlspecialchars($voter['Email']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_course<?php echo $voter['SN']; ?>" class="form-label">Course</label>
                                                                <input type="text" class="form-control" id="edit_course<?php echo $voter['SN']; ?>" 
                                                                       name="course" value="<?php echo htmlspecialchars($voter['Course']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_year_of_study<?php echo $voter['SN']; ?>" class="form-label">Year of Study</label>
                                                                <input type="number" class="form-control" id="edit_year_of_study<?php echo $voter['SN']; ?>" 
                                                                       name="year_of_study" value="<?php echo $voter['Year_of_Study']; ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_status<?php echo $voter['SN']; ?>" class="form-label">Status</label>
                                                                <select class="form-select" id="edit_status<?php echo $voter['SN']; ?>" name="status" required>
                                                                    <option value="active" <?php echo $voter['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                                    <option value="inactive" <?php echo $voter['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                    <option value="suspended" <?php echo $voter['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 