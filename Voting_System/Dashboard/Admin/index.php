<?php
session_start();
require_once '../../Database/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../Auth/index.php');
    exit();
}

// Fetch statistics
$stats = [
    'total_voters' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'")->fetch_assoc()['count'],
    'total_candidates' => $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'],
    'total_positions' => $conn->query("SELECT COUNT(*) as count FROM positions")->fetch_assoc()['count'],
    'total_votes' => $conn->query("SELECT COUNT(*) as count FROM votes")->fetch_assoc()['count']
];

// Fetch active election status
$election_status = $conn->query("SELECT * FROM election_settings WHERE status = 'active' LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Zetech Voting System</title>
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
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
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
                            <a class="nav-link active" href="index.php">
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
                    <h2>Dashboard</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Voters</h5>
                                <h2><?php echo $stats['total_voters']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Candidates</h5>
                                <h2><?php echo $stats['total_candidates']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Positions</h5>
                                <h2><?php echo $stats['total_positions']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Votes</h5>
                                <h2><?php echo $stats['total_votes']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Election Status -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Active Election Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($election_status): ?>
                            <div class="alert alert-success">
                                <h6>Election is currently active</h6>
                                <p class="mb-0">Start Date: <?php echo date('Y-m-d H:i:s', strtotime($election_status['start_date'])); ?></p>
                                <p class="mb-0">End Date: <?php echo date('Y-m-d H:i:s', strtotime($election_status['end_date'])); ?></p>
                                <p class="mb-0">Election Name: <?php echo htmlspecialchars($election_status['election_name']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                No active election at the moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>