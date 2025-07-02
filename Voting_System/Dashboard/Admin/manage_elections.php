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
        if ($_POST['action'] === 'add') {
            $election_name = trim($_POST['election_name']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];

            // Validate dates
            if (strtotime($end_date) <= strtotime($start_date)) {
                $error = "End date must be after start date";
            } else {
                $sql = "INSERT INTO election_settings (election_name, start_date, end_date, status, created_by) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $election_name, $start_date, $end_date, $status, $_SESSION['user_id']);

                if ($stmt->execute()) {
                    $success = "Election period added successfully";
                } else {
                    $error = "Error adding election period";
                }
            }
        } elseif ($_POST['action'] === 'update' && isset($_POST['setting_id'])) {
            $setting_id = $_POST['setting_id'];
            $election_name = trim($_POST['election_name']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];

            // Validate dates
            if (strtotime($end_date) <= strtotime($start_date)) {
                $error = "End date must be after start date";
            } else {
                $sql = "UPDATE election_settings 
                        SET election_name = ?, start_date = ?, end_date = ?, status = ? 
                        WHERE setting_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $election_name, $start_date, $end_date, $status, $setting_id);

                if ($stmt->execute()) {
                    $success = "Election period updated successfully";
                } else {
                    $error = "Error updating election period";
                }
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['setting_id'])) {
            $setting_id = $_POST['setting_id'];
            $sql = "DELETE FROM election_settings WHERE setting_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $setting_id);

            if ($stmt->execute()) {
                $success = "Election period deleted successfully";
            } else {
                $error = "Error deleting election period";
            }
        }
    }
}

// Fetch all election periods
$elections = $conn->query("SELECT * FROM election_settings ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections - Admin Dashboard</title>
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
                            <a class="nav-link active" href="manage_elections.php">
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
                            <a class="nav-link" href="voters.php">
                                <i class="fas fa-users me-2"></i> Manage Voters
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="results.php">
                                <i class="fas fa-chart-bar me-2"></i> View Results
                            </a>
                        </li>
                       
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="../../admin/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Elections</h2>
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

                <!-- Add Election Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Election Period</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label for="election_name" class="form-label">Election Name</label>
                                <input type="text" class="form-control" id="election_name" name="election_name" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Add Election Period</button>
                        </form>
                    </div>
                </div>

                <!-- Elections List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Election Periods</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($election = $elections->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($election['election_name']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($election['start_date'])); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($election['end_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $election['status'] === 'active' ? 'success' : 
                                                        ($election['status'] === 'pending' ? 'warning' : 
                                                        ($election['status'] === 'completed' ? 'info' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst($election['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($election['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $election['setting_id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this election period?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="setting_id" value="<?php echo $election['setting_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $election['setting_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Election Period</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="setting_id" value="<?php echo $election['setting_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="edit_election_name<?php echo $election['setting_id']; ?>" class="form-label">Election Name</label>
                                                                <input type="text" class="form-control" id="edit_election_name<?php echo $election['setting_id']; ?>" 
                                                                       name="election_name" value="<?php echo htmlspecialchars($election['election_name']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_start_date<?php echo $election['setting_id']; ?>" class="form-label">Start Date</label>
                                                                <input type="datetime-local" class="form-control" id="edit_start_date<?php echo $election['setting_id']; ?>" 
                                                                       name="start_date" value="<?php echo date('Y-m-d\TH:i', strtotime($election['start_date'])); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_end_date<?php echo $election['setting_id']; ?>" class="form-label">End Date</label>
                                                                <input type="datetime-local" class="form-control" id="edit_end_date<?php echo $election['setting_id']; ?>" 
                                                                       name="end_date" value="<?php echo date('Y-m-d\TH:i', strtotime($election['end_date'])); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_status<?php echo $election['setting_id']; ?>" class="form-label">Status</label>
                                                                <select class="form-select" id="edit_status<?php echo $election['setting_id']; ?>" name="status" required>
                                                                    <option value="pending" <?php echo $election['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="active" <?php echo $election['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                                    <option value="completed" <?php echo $election['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                    <option value="cancelled" <?php echo $election['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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