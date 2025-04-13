<?php
session_start();
include 'Database/db.php';

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: auth/index.php");
//     exit();
// }

// Check if election is active
$sql = "SELECT * FROM election_settings WHERE status = 'active' AND NOW() BETWEEN start_date AND end_date";
$result = mysqli_query($conn, $sql);
$election = mysqli_fetch_assoc($result);

if (!$election) {
    $_SESSION['error'] = "No active election at the moment.";
    header("Location: vote.php");
    exit();
}

// Check if user has already voted
$user_id = $_SESSION['user_id'];
$check_vote_sql = "SELECT position FROM votes WHERE user_id = ?";
$check_stmt = mysqli_prepare($conn, $check_vote_sql);
mysqli_stmt_bind_param($check_stmt, "i", $user_id);
mysqli_stmt_execute($check_stmt);
$voted_positions = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($voted_positions) > 0) {
    $_SESSION['error'] = "You have already cast your vote.";
    header("Location: vote.php");
    exit();
}

// Process votes
$success = true;
$error_message = "";

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Get all positions
    $positions_sql = "SELECT * FROM positions";
    $positions_result = mysqli_query($conn, $positions_sql);

    while ($position = mysqli_fetch_assoc($positions_result)) {
        $vote_field = "vote_" . $position['position_id'];
        
        if (isset($_POST[$vote_field])) {
            $candidate_id = (int)$_POST[$vote_field];
            
            // Validate candidate exists and is running for this position
            $validate_sql = "SELECT candidate_id FROM candidates WHERE candidate_id = ? AND Position = ?";
            $validate_stmt = mysqli_prepare($conn, $validate_sql);
            mysqli_stmt_bind_param($validate_stmt, "is", $candidate_id, $position['title']);
            mysqli_stmt_execute($validate_stmt);
            $validate_result = mysqli_stmt_get_result($validate_stmt);

            if (mysqli_num_rows($validate_result) === 0) {
                throw new Exception("Invalid candidate selection for position: " . $position['title']);
            }

            // Insert vote
            $vote_sql = "INSERT INTO votes (user_id, candidate_id, position) VALUES (?, ?, ?)";
            $vote_stmt = mysqli_prepare($conn, $vote_sql);
            mysqli_stmt_bind_param($vote_stmt, "iis", $user_id, $candidate_id, $position['title']);
            
            if (!mysqli_stmt_execute($vote_stmt)) {
                throw new Exception("Error recording vote for position: " . $position['title']);
            }
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    $_SESSION['success'] = "Your vote has been recorded successfully!";
    header("Location: results.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error processing your vote: " . $e->getMessage();
    header("Location: vote.php");
    exit();
}
?> 