<?php
session_start();
require_once '../../Database/db.php';

// Check if user is logged in as voter
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
    header('Location: ../../Auth/index.php');
    exit();
}

// Check if election is active
$election_status = $conn->query("SELECT * FROM election_settings WHERE status = 'active' LIMIT 1")->fetch_assoc();

// Check if user has already voted
$has_voted = false;
if ($election_status) {
    $check_sql = "SELECT COUNT(*) as count FROM votes WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $_SESSION['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result()->fetch_assoc();
    $has_voted = $result['count'] > 0;
}

// Fetch active positions
$positions = $conn->query("SELECT * FROM positions WHERE status = 'active' ORDER BY title");

// Fetch candidates for each position
$candidates = [];
while ($position = $positions->fetch_assoc()) {
    $candidates[$position['position_id']] = $conn->query("
        SELECT c.* 
        FROM candidates c 
        WHERE c.position_id = {$position['position_id']}
    ")->fetch_all(MYSQLI_ASSOC);
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$has_voted && $election_status) {
    $success = true;
    $error = '';

    foreach ($_POST['votes'] as $position_id => $candidate_id) {
        $sql = "INSERT INTO votes (user_id, position_id, candidate_id, timestamp) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $_SESSION['user_id'], $position_id, $candidate_id);
        
        if (!$stmt->execute()) {
            $success = false;
            $error = "Error recording vote. Please try again.";
            break;
        }
    }

    if ($success) {
        header("Location: index.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard - Zetech Voting System</title>
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
        .candidate-card {
            transition: transform 0.3s;
        }
        .candidate-card:hover {
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
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i> My Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="results.php">
                                <i class="fas fa-user me-2"></i> View Result
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
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                    <div>
                        <?php if ($has_voted): ?>
                            <span class="badge bg-success">Voted</span>
                        <?php elseif ($election_status): ?>
                            <span class="badge bg-warning">Election Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Election Not Active</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Your vote has been recorded successfully!
                    </div>
                <?php endif; ?>

                <?php if ($election_status && !$has_voted): ?>
                    <form method="POST" action="">
                        <?php foreach ($candidates as $position_id => $position_candidates): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <?php 
                                        $position = $conn->query("SELECT title FROM positions WHERE position_id = $position_id")->fetch_assoc();
                                        echo htmlspecialchars($position['title']);
                                        ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($position_candidates as $candidate): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card candidate-card h-100">
                                                    <div class="card-body">
                                                        <?php if ($candidate['Photo']): ?>
                                                            <img src="<?php echo htmlspecialchars($candidate['Photo']); ?>" 
                                                                 class="img-fluid rounded mb-3" 
                                                                 alt="Candidate Photo"
                                                                 style="max-height: 150px; width: auto;">
                                                        <?php endif; ?>
                                                        
                                                        <h5 class="card-title">
                                                            <?php echo htmlspecialchars($candidate['First_Name'] . ' ' . $candidate['Last_Name']); ?>
                                                        </h5>
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($candidate['Course'] . ' - Year ' . $candidate['Year_of_Study']); ?>
                                                            </small>
                                                        </p>
                                                        <p class="card-text">
                                                            <?php echo nl2br(htmlspecialchars($candidate['Manifesto'])); ?>
                                                        </p>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="votes[<?php echo $position_id; ?>]" 
                                                                   value="<?php echo $candidate['candidate_id']; ?>" 
                                                                   required>
                                                            <label class="form-check-label">
                                                                Select this candidate
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
                        <?php endforeach; ?>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Submit My Votes
                            </button>
                        </div>
                    </form>
                <?php elseif ($has_voted): ?>
                    <div class="alert alert-info">
                        You have already cast your vote. Thank you for participating!
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        The election is not currently active. Please check back later.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
