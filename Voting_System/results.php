<?php
session_start();
include 'Database/db.php';

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: auth/index.php");
//     exit();
// }

// Get election status
$sql = "SELECT * FROM election_settings WHERE status = 'active' OR status = 'completed' ORDER BY setting_id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$election = mysqli_fetch_assoc($result);

if (!$election) {
    $error = "No election results available at the moment.";
} else {
    // Get all positions
    $positions_sql = "SELECT * FROM positions ORDER BY position_id";
    $positions_result = mysqli_query($conn, $positions_sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Zetech University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .results-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        .candidate-result {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .candidate-result:last-child {
            border-bottom: none;
        }
        .candidate-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }
        .progress {
            height: 25px;
            margin-top: 10px;
        }
        .winner-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .election-status {
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="Assets/Img/logo11.png" alt="Zetech Logo" height="40">
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard/voter/index.php">Dashboard</a>
                <a class="nav-link" href="auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="text-center mb-4">Election Results</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-info"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="election-status <?php echo $election['status'] === 'active' ? 'status-active' : 'status-completed'; ?>">
                <strong>Election Status:</strong> <?php echo ucfirst($election['status']); ?>
                <?php if ($election['status'] === 'active'): ?>
                    <br>Ends: <?php echo date('F j, Y, g:i a', strtotime($election['end_date'])); ?>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php while ($position = mysqli_fetch_assoc($positions_result)): ?>
                <div class="results-card">
                    <h3><?php echo htmlspecialchars($position['title']); ?></h3>
                    <p class="text-muted"><?php echo htmlspecialchars($position['description']); ?></p>
                    
                    <?php
                    // Get candidates and their vote counts for this position
                    $candidates_sql = "SELECT c.*, COUNT(v.vote_id) as vote_count 
                                     FROM candidates c 
                                     LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
                                     WHERE c.Position = ? 
                                     GROUP BY c.candidate_id 
                                     ORDER BY vote_count DESC";
                    $candidates_stmt = mysqli_prepare($conn, $candidates_sql);
                    mysqli_stmt_bind_param($candidates_stmt, "s", $position['title']);
                    mysqli_stmt_execute($candidates_stmt);
                    $candidates_result = mysqli_stmt_get_result($candidates_stmt);

                    // Calculate total votes for percentage
                    $total_votes = 0;
                    $candidates = [];
                    while ($candidate = mysqli_fetch_assoc($candidates_result)) {
                        $total_votes += $candidate['vote_count'];
                        $candidates[] = $candidate;
                    }
                    ?>

                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate-result">
                            <img src="<?php echo htmlspecialchars($candidate['Photo'] ?? 'Assets/images/default-avatar.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($candidate['First_Name']); ?>" 
                                 class="candidate-photo">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">
                                    <?php echo htmlspecialchars($candidate['First_Name'] . ' ' . $candidate['Last_Name']); ?>
                                    <?php if ($candidate['vote_count'] > 0 && $candidate['vote_count'] === max(array_column($candidates, 'vote_count'))): ?>
                                        <span class="winner-badge">Winner</span>
                                    <?php endif; ?>
                                </h5>
                                <p class="text-muted mb-1">
                                    <?php echo htmlspecialchars($candidate['Course']); ?> - Year <?php echo $candidate['Year_of_Study']; ?>
                                </p>
                                <div class="progress">
                                    <?php 
                                    $percentage = $total_votes > 0 ? ($candidate['vote_count'] / $total_votes) * 100 : 0;
                                    ?>
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%"
                                         aria-valuenow="<?php echo $percentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo $candidate['vote_count']; ?> votes (<?php echo round($percentage, 1); ?>%)
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 