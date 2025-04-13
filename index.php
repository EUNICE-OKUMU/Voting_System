<?php
require_once 'auth_check.php';
require_once '../Database/db.php';

// Fetch some basic statistics
$stats = [
    'total_voters' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'")->fetch_assoc()['count'],
    'total_candidates' => $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'],
    'total_positions' => $conn->query("SELECT COUNT(*) as count FROM positions")->fetch_assoc()['count'],
    'total_votes' => $conn->query("SELECT COUNT(*) as count FROM votes")->fetch_assoc()['count']
];

// Fetch active election if any
$active_election = $conn->query("SELECT * FROM election_settings WHERE status = 'active' ORDER BY setting_id DESC LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Zetech University Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #1C1D3C;
            color: white;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.2);
        }
        .main-content {
            padding: 20px;
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
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <img src="../Assets/Img/logo11.png" alt="Zetech Logo" style="width: 60px;">
                    <h5 class="mt-2">Admin Panel</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="elections.php">
                        <i class="fas fa-vote-yea me-2"></i> Manage Elections
                    </a>
                    <a class="nav-link" href="../dashboard/admin/candidates.php">
                        <i class="fas fa-user-tie me-2"></i> Manage Candidates
                    </a>
                    <a class="nav-link" href="../dashboard/admin/positions.php">
                        <i class="fas fa-briefcase me-2"></i> Manage Positions
                    </a>
                    <a class="nav-link" href="../dashboard/admin/voters.php">
                        <i class="fas fa-users me-2"></i> Manage Voters
                    </a>
                    <a class="nav-link" href="../results.php">
                        <i class="fas fa-chart-bar me-2"></i> View Results
                    </a>
                   
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
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
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Active Election Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($active_election): ?>
                            <div class="alert alert-success">
                                <h6>Current Active Election:</h6>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($active_election['election_name']); ?></p>
                                <p class="mb-1"><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($active_election['start_date'])); ?></p>
                                <p class="mb-0"><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($active_election['end_date'])); ?></p>
                            </div>
                            <a href="elections.php" class="btn btn-primary">Manage Election</a>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p class="mb-0">No active election at the moment.</p>
                            </div>
                            <a href="elections.php" class="btn btn-primary">Create New Election</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 