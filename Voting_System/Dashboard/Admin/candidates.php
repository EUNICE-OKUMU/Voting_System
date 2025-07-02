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
            $user_id = $_POST['user_id'];
            $position_id = $_POST['position_id'];
            $manifesto = trim($_POST['manifesto']);
            $photo = trim($_POST['photo']);

            // Check if candidate already exists for this position
            $check_sql = "SELECT * FROM candidates WHERE First_Name = ? AND Last_Name = ? AND position_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ssi", $user['First_Name'], $user['Last_Name'], $position_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
                $error = "This user is already a candidate for this position";
    } else {
                // Get user details
                $user_sql = "SELECT First_Name, Last_Name, Course, Year_of_Study FROM users WHERE SN = ?";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user = $user_stmt->get_result()->fetch_assoc();

        if ($user) {
                    // Insert candidate
                    $sql = "INSERT INTO candidates (First_Name, Last_Name, position_id, Course, Year_of_Study, Manifesto, Photo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssissss", $user['First_Name'], $user['Last_Name'], 
                                    $position_id, $user['Course'], $user['Year_of_Study'], $manifesto, $photo);

                    if ($stmt->execute()) {
                        $success = "Candidate added successfully";
                    } else {
                        $error = "Error adding candidate";
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['candidate_id'])) {
            $candidate_id = $_POST['candidate_id'];
            $sql = "DELETE FROM candidates WHERE candidate_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $candidate_id);

            if ($stmt->execute()) {
                $success = "Candidate removed successfully";
            } else {
                $error = "Error removing candidate";
            }
        }
    }
}

// Fetch all candidates with their positions
$candidates_sql = "SELECT c.*, p.title as position_title 
                   FROM candidates c 
                   JOIN positions p ON c.position_id = p.position_id 
                   ORDER BY p.title, c.First_Name";
$candidates = $conn->query($candidates_sql);

// Fetch all positions for the dropdown
$positions = $conn->query("SELECT * FROM positions WHERE status = 'active' ORDER BY title");

// Fetch all voters for the dropdown
$voters = $conn->query("SELECT SN, First_Name, Last_Name, Adm_No FROM users WHERE role = 'voter' ORDER BY First_Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Admin Dashboard</title>
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
                            <a class="nav-link active" href="candidates.php">
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
                    <h2>Manage Candidates</h2>
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

                <!-- Add Candidate Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Candidate</h5>
                    </div>
                    <div class="card-body">
    <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="user_id" class="form-label">Select Voter</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">Choose a voter...</option>
                                        <?php while($voter = $voters->fetch_assoc()): ?>
                                            <option value="<?php echo $voter['SN']; ?>">
                                                <?php echo htmlspecialchars($voter['First_Name'] . ' ' . $voter['Last_Name'] . ' (' . $voter['Adm_No'] . ')'); ?>
                                            </option>
                                        <?php endwhile; ?>
            </select>
        </div>

                                <div class="col-md-6 mb-3">
                                    <label for="position_id" class="form-label">Select Position</label>
                                    <select class="form-select" id="position_id" name="position_id" required>
                                        <option value="">Choose a position...</option>
                                        <?php while($position = $positions->fetch_assoc()): ?>
                                            <option value="<?php echo $position['position_id']; ?>">
                                                <?php echo htmlspecialchars($position['title']); ?>
                                            </option>
                                        <?php endwhile; ?>
            </select>
        </div>
                            </div>

        <div class="mb-3">
            <label for="manifesto" class="form-label">Manifesto</label>
                                <textarea class="form-control" id="manifesto" name="manifesto" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Photo URL</label>
            <input type="text" class="form-control" id="photo" name="photo">
        </div>

        <button type="submit" class="btn btn-primary">Add Candidate</button>
    </form>
                    </div>
                </div>

                <!-- Candidates List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Candidates</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($candidate = $candidates->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($candidate['First_Name'] . ' ' . $candidate['Last_Name']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['position_title']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['Course']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['Year_of_Study']); ?></td>
                                            <td>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this candidate?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
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
