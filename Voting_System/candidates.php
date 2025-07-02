<?php
include 'includes/header.php';
require_once 'Database/db.php';

// Get candidate ID from URL if viewing specific candidate
$candidate_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($candidate_id) {
    // Fetch specific candidate details
    $sql = "SELECT c.*, u.first_name, u.last_name, u.email, p.title as position_title, p.description as position_description 
            FROM candidates c 
            JOIN users u ON c.user_id = u.user_id 
            JOIN positions p ON c.Position = p.title 
            WHERE c.candidate_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();

    if ($candidate) {
        // Display single candidate view
        ?>
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <?php if($candidate['photo']): ?>
                            <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>"
                                 style="height: 300px; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="card-title"><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?></h1>
                            <h4 class="text-muted mb-4"><?php echo htmlspecialchars($candidate['position_title']); ?></h4>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Personal Information</h5>
                                    <p><strong>Course:</strong> <?php echo htmlspecialchars($candidate['course']); ?></p>
                                    <p><strong>Year of Study:</strong> <?php echo htmlspecialchars($candidate['year_of_study']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($candidate['email']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Position Details</h5>
                                    <p><?php echo nl2br(htmlspecialchars($candidate['position_description'])); ?></p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5>Manifesto</h5>
                                <p><?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?></p>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="campaigns.php" class="btn btn-secondary">Back to Campaigns</a>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <a href="vote.php" class="btn btn-primary">Cast Your Vote</a>
                                <?php else: ?>
                                    <a href="auth/index.php" class="btn btn-primary">Login to Vote</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        // Candidate not found
        ?>
        <div class="container py-5">
            <div class="alert alert-danger text-center">
                <h4>Candidate Not Found</h4>
                <p>The requested candidate could not be found.</p>
                <a href="candidates.php" class="btn btn-primary">View All Candidates</a>
            </div>
        </div>
        <?php
    }
} else {
    // Fetch all candidates grouped by position
    $sql = "SELECT c.*, u.first_name, u.last_name, p.title as position_title 
            FROM candidates c 
            JOIN users u ON c.user_id = u.user_id 
            JOIN positions p ON c.Position = p.title 
            ORDER BY p.title, u.first_name";
    $result = $conn->query($sql);
    
    // Group candidates by position
    $candidates_by_position = [];
    while($row = $result->fetch_assoc()) {
        $candidates_by_position[$row['position_title']][] = $row;
    }
    ?>
    <div class="container py-5">
        <h1 class="text-center mb-5">Candidates</h1>

        <?php foreach($candidates_by_position as $position => $candidates): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="mb-0"><?php echo htmlspecialchars($position); ?></h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach($candidates as $candidate): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <?php if($candidate['photo']): ?>
                                        <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?></h5>
                                        <p class="card-text">
                                            <strong>Course:</strong> <?php echo htmlspecialchars($candidate['course']); ?><br>
                                            <strong>Year:</strong> <?php echo htmlspecialchars($candidate['year_of_study']); ?>
                                        </p>
                                        <a href="candidates.php?id=<?php echo $candidate['candidate_id']; ?>" 
                                           class="btn btn-primary">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>

<?php
include 'includes/footer.php';
?> 