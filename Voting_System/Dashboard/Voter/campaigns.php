<?php
session_start();
require_once '../../Database/db.php';

// Check if user is logged in as voter
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
    header('Location: ../../Auth/index.php');
    exit();
}

// Fetch all active campaigns with candidate details
$campaigns_sql = "SELECT c.*, cm.*, 
                  ca.First_Name, ca.Last_Name, ca.Course, ca.Year_of_Study,
                  p.title as position_title
                  FROM campaigns c
                  JOIN campaign_materials cm ON c.campaign_id = cm.campaign_id
                  JOIN candidates ca ON c.candidate_id = ca.candidate_id
                  JOIN positions p ON ca.position_id = p.position_id
                  WHERE c.status = 'active'
                  ORDER BY c.created_at DESC";
$campaigns = $conn->query($campaigns_sql);

// Group campaigns by candidate
$grouped_campaigns = [];
while ($campaign = $campaigns->fetch_assoc()) {
    $candidate_id = $campaign['candidate_id'];
    if (!isset($grouped_campaigns[$candidate_id])) {
        $grouped_campaigns[$candidate_id] = [
            'candidate' => [
                'name' => $campaign['First_Name'] . ' ' . $campaign['Last_Name'],
                'course' => $campaign['Course'],
                'year' => $campaign['Year_of_Study'],
                'position' => $campaign['position_title']
            ],
            'materials' => []
        ];
    }
    $grouped_campaigns[$candidate_id]['materials'][] = $campaign;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - Voter Dashboard</title>
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
        .campaign-card {
            transition: transform 0.3s;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
        }
        .material-image {
            max-height: 200px;
            object-fit: cover;
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
                            <a class="nav-link" href="profile.php">
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
                    <h2>Campaign Materials</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                </div>

                <?php if (empty($grouped_campaigns)): ?>
                    <div class="alert alert-info">
                        No active campaigns at the moment.
                    </div>
                <?php else: ?>
                    <?php foreach ($grouped_campaigns as $candidate_id => $data): ?>
                        <div class="card campaign-card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <?php echo htmlspecialchars($data['candidate']['name']); ?>
                                    <small class="d-block">
                                        <?php echo htmlspecialchars($data['candidate']['position']); ?> | 
                                        <?php echo htmlspecialchars($data['candidate']['course'] . ' - Year ' . $data['candidate']['year']); ?>
                                    </small>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($data['materials'] as $material): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <?php if ($material['type'] === 'image'): ?>
                                                    <img src="<?php echo htmlspecialchars($material['content']); ?>" 
                                                         class="card-img-top material-image" 
                                                         alt="Campaign Image">
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <?php echo htmlspecialchars($material['title']); ?>
                                                    </h6>
                                                    <?php if ($material['type'] === 'text'): ?>
                                                        <p class="card-text">
                                                            <?php echo nl2br(htmlspecialchars($material['content'])); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($material['type'] === 'video'): ?>
                                                        <div class="ratio ratio-16x9">
                                                            <iframe src="<?php echo htmlspecialchars($material['content']); ?>" 
                                                                    title="Campaign Video"
                                                                    allowfullscreen></iframe>
                                                        </div>
                                                    <?php endif; ?>
                                                    <small class="text-muted">
                                                        Posted on <?php echo date('F j, Y', strtotime($material['created_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 