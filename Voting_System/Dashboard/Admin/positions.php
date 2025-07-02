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
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $max_winners = (int)$_POST['max_winners'];
            $status = $_POST['status'];

            $sql = "INSERT INTO positions (title, description, max_winners, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssis", $title, $description, $max_winners, $status);

            if ($stmt->execute()) {
                $success = "Position added successfully";
            } else {
                $error = "Error adding position";
            }
        } elseif ($_POST['action'] === 'update' && isset($_POST['position_id'])) {
            $position_id = $_POST['position_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $max_winners = (int)$_POST['max_winners'];
            $status = $_POST['status'];

            $sql = "UPDATE positions SET title = ?, description = ?, max_winners = ?, status = ? WHERE position_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisi", $title, $description, $max_winners, $status, $position_id);

            if ($stmt->execute()) {
                $success = "Position updated successfully";
            } else {
                $error = "Error updating position";
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['position_id'])) {
            $position_id = $_POST['position_id'];

            // Check if position has candidates
            $check_sql = "SELECT COUNT(*) as count FROM candidates WHERE position_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $position_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();

            if ($result['count'] > 0) {
                $error = "Cannot delete position that has candidates";
            } else {
                $sql = "DELETE FROM positions WHERE position_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $position_id);

                if ($stmt->execute()) {
                    $success = "Position deleted successfully";
                } else {
                    $error = "Error deleting position";
                }
            }
        }
    }
}

// Fetch all positions
$positions = $conn->query("SELECT * FROM positions ORDER BY title");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Positions - Admin Dashboard</title>
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
                            <a class="nav-link active" href="positions.php">
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
                    <h2>Manage Positions</h2>
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

                <!-- Add Position Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Position</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Position Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_winners" class="form-label">Maximum Winners</label>
                                    <input type="number" class="form-control" id="max_winners" name="max_winners" min="1" value="1" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Add Position</button>
                        </form>
                    </div>
                </div>

                <!-- Positions List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Positions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Max Winners</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($position = $positions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($position['title']); ?></td>
                                            <td><?php echo htmlspecialchars($position['description']); ?></td>
                                            <td><?php echo $position['max_winners']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $position['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($position['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($position['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $position['position_id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this position?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="position_id" value="<?php echo $position['position_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $position['position_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Position</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="position_id" value="<?php echo $position['position_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="edit_title<?php echo $position['position_id']; ?>" class="form-label">Position Title</label>
                                                                <input type="text" class="form-control" id="edit_title<?php echo $position['position_id']; ?>" 
                                                                       name="title" value="<?php echo htmlspecialchars($position['title']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="edit_description<?php echo $position['position_id']; ?>" class="form-label">Description</label>
                                                                <textarea class="form-control" id="edit_description<?php echo $position['position_id']; ?>" 
                                                                          name="description" rows="3" required><?php echo htmlspecialchars($position['description']); ?></textarea>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="edit_max_winners<?php echo $position['position_id']; ?>" class="form-label">Maximum Winners</label>
                                                                    <input type="number" class="form-control" id="edit_max_winners<?php echo $position['position_id']; ?>" 
                                                                           name="max_winners" min="1" value="<?php echo $position['max_winners']; ?>" required>
                                                                </div>

                                                                <div class="col-md-6 mb-3">
                                                                    <label for="edit_status<?php echo $position['position_id']; ?>" class="form-label">Status</label>
                                                                    <select class="form-select" id="edit_status<?php echo $position['position_id']; ?>" name="status" required>
                                                                        <option value="active" <?php echo $position['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                                        <option value="inactive" <?php echo $position['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                    </select>
                                                                </div>
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