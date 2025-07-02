<?php
session_start();
require_once '../../Database/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || 
    ($_SESSION['user_role'] != 'voter')) {
    header('Location: ../../Auth/index.php');
    exit();
}


// Fetch all positions
$positions = $conn->query("SELECT * FROM positions WHERE status = 'active' ORDER BY title");

// Get selected position or default to first position
$selected_position = isset($_GET['position_id']) ? (int)$_GET['position_id'] : null;
if (!$selected_position && $positions->num_rows > 0) {
    $first_position = $positions->fetch_assoc();
    $selected_position = $first_position['position_id'];
    $positions->data_seek(0); // Reset pointer
}

// Fetch results for selected position
$results = [];
if ($selected_position) {
    $sql = "SELECT c.*, p.title as position_title, 
            COUNT(v.vote_id) as vote_count,
            (SELECT COUNT(*) FROM votes WHERE position_id = ?) as total_votes
            FROM candidates c
            JOIN positions p ON c.position_id = p.position_id
            LEFT JOIN votes v ON c.candidate_id = v.candidate_id
            WHERE c.position_id = ?
            GROUP BY c.candidate_id
            ORDER BY vote_count DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $selected_position, $selected_position);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .result-card {
            transition: transform 0.3s;
        }
        .result-card:hover {
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
                    <h4 class="text-center mb-4">Voter Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                      
                        <li class="nav-item">
                            <a class="nav-link active" href="results.php">
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
                    <h2>Election Results</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                </div>

                <!-- Position Selector -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="position_id" class="form-label">Select Position</label>
                                <select class="form-select" id="position_id" name="position_id">
                                    <?php while($position = $positions->fetch_assoc()): ?>
                                        <option value="<?php echo $position['position_id']; ?>" 
                                                <?php echo $selected_position == $position['position_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($position['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">View Results</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selected_position && count($results) > 0): ?>
                    <!-- Results Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card result-card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Votes Cast</h5>
                                    <h2><?php echo $results[0]['total_votes']; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card result-card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Candidates</h5>
                                    <h2><?php echo count($results); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card result-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Leading Candidate</h5>
                                    <h2><?php echo htmlspecialchars($results[0]['First_Name'] . ' ' . $results[0]['Last_Name']); ?></h2>
                                    <p class="mb-0"><?php echo $results[0]['vote_count']; ?> votes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Chart -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <canvas id="resultsChart"></canvas>
                        </div>
                    </div>

                    <!-- Detailed Results Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Detailed Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Candidate</th>
                                            <th>Course</th>
                                            <th>Year</th>
                                            <th>Votes</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($results as $index => $result): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($result['First_Name'] . ' ' . $result['Last_Name']); ?></td>
                                                <td><?php echo htmlspecialchars($result['Course']); ?></td>
                                                <td><?php echo htmlspecialchars($result['Year_of_Study']); ?></td>
                                                <td><?php echo $result['vote_count']; ?></td>
                                                <td>
                                                    <?php 
                                                    $percentage = $result['total_votes'] > 0 
                                                        ? round(($result['vote_count'] / $result['total_votes']) * 100, 1)
                                                        : 0;
                                                    echo $percentage . '%';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Script -->
                    <script>
                        const ctx = document.getElementById('resultsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_map(function($r) {
                                    return $r['First_Name'] . ' ' . $r['Last_Name'];
                                }, $results)); ?>,
                                datasets: [{
                                    label: 'Votes',
                                    data: <?php echo json_encode(array_map(function($r) {
                                        return $r['vote_count'];
                                    }, $results)); ?>,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">
                        No results available for this position.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 