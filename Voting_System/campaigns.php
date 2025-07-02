<?php
include 'includes/header.php';
require_once 'Database/db.php';

// Fetch active campaigns
$sql = "SELECT c.*, cm.*, u.first_name, u.last_name, p.title as position_title 
    FROM campaigns c 
    JOIN candidates cm ON c.candidate_id = cm.candidate_id 
    JOIN users u ON cm.user_id = u.user_id 
    JOIN positions p ON cm.Position = p.title 
    WHERE c.status = 'active' 
    ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>

<div class="container py-5">
    <h1 class="text-center mb-5">Active Campaigns</h1>

    <?php if ($result->num_rows > 0): ?>
    <div class="row">
        <?php while($campaign = $result->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
            <?php if($campaign['photo']): ?>
                <img src="<?php echo htmlspecialchars($campaign['photo']); ?>" 
                 class="card-img-top" 
                 alt="<?php echo htmlspecialchars($campaign['first_name'] . ' ' . $campaign['last_name']); ?>"
                 style="height: 200px; object-fit: cover;">
            <?php endif; ?>
            
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($campaign['first_name'] . ' ' . $campaign['last_name']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($campaign['position_title']); ?></h6>
                
                <p class="card-text"><?php echo nl2br(htmlspecialchars($campaign['manifesto'])); ?></p>
                
                <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Campaign ends: <?php echo date('F j, Y', strtotime($campaign['end_date'])); ?>
                </small>
                <a href="candidates.php?id=<?php echo $campaign['candidate_id']; ?>" 
                   class="btn btn-primary">View Profile</a>
                </div>
            </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center">
        <p class="mb-0">No active campaigns at the moment. Please check back later.</p>
    </div>
    <?php endif; ?>

    <!-- Upcoming Campaigns Section -->
    <h2 class="text-center mt-5 mb-4">Upcoming Campaigns</h2>
    <?php
    $sql = "SELECT c.*, cm.*, u.first_name, u.last_name, p.title as position_title 
        FROM campaigns c 
        JOIN candidates cm ON c.candidate_id = cm.candidate_id 
        JOIN users u ON cm.user_id = u.user_id 
        JOIN positions p ON cm.Position = p.title 
        WHERE c.status = 'scheduled' 
        ORDER BY c.start_date ASC";
    $result = $conn->query($sql);
    ?>

    <?php if ($result->num_rows > 0): ?>
    <div class="row">
        <?php while($campaign = $result->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($campaign['first_name'] . ' ' . $campaign['last_name']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($campaign['position_title']); ?></h6>
                
                <div class="mt-3">
                <p class="mb-1"><strong>Starts:</strong> <?php echo date('F j, Y', strtotime($campaign['start_date'])); ?></p>
                <p class="mb-0"><strong>Ends:</strong> <?php echo date('F j, Y', strtotime($campaign['end_date'])); ?></p>
                </div>
            </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center">
        <p class="mb-0">No upcoming campaigns scheduled at the moment.</p>
    </div>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>