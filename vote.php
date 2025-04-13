<?php
// Database connection
include "../../Database/db.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user from uid
if (!isset($_GET['uid'])) {
    header('location: ../../auth/index.php');
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['uid']);

// Fetch user details
$get_user = "SELECT * FROM users WHERE SN = ?";
$stmt = $conn->prepare($get_user);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('location: ../../auth/index.php');
    exit;
}

$adm = $user['Adm_No'];
$course = $user['Course'];

// Fetch candidates for the course
$get_candidates = "SELECT * FROM candidates WHERE Course = ?";
$stmt = $conn->prepare($get_candidates);
$stmt->bind_param("s", $course);
$stmt->execute();
$candidates = $stmt->get_result();

$candidate = $candidates->fetch_all(MYSQLI_ASSOC);

// Check if user has already voted for any position
$check_vote = "SELECT * FROM votes WHERE user_adm = ?";
$stmt = $conn->prepare($check_vote);
$stmt->bind_param("s", $adm);
$stmt->execute();
$vote_result = $stmt->get_result();
$has_voted = $vote_result->num_rows > 0;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['vote']) && !$has_voted) {
    foreach ($_POST as $key => $cand_adm) {
        if (strpos($key, 'cand_adm_') === 0) {
            // Get position from input name
            $position = substr($key, 9);

            // Check if user already voted for this position
            $check_position_vote = "SELECT * FROM votes WHERE user_adm = ? AND $position IS NOT NULL";
            $stmt = $conn->prepare($check_position_vote);
            $stmt->bind_param("s", $adm);
            $stmt->execute();
            $pos_vote_result = $stmt->get_result();

            if ($pos_vote_result->num_rows > 0) {
                echo "<script>alert('You have already voted for the $position position.'); window.location.href='vote.php?uid=$id';</script>";
                exit;
            }

            // Insert or update vote
            $insert_vote = "INSERT INTO votes (Course, user_adm, $position) VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE $position = VALUES($position)";
            $stmt = $conn->prepare($insert_vote);
            $stmt->bind_param("sss", $course, $adm, $cand_adm);

            if ($stmt->execute()) {
                echo "<script>alert('Vote submitted successfully!'); window.location.href='vote.php?uid=$id';</script>";
                exit;
            } else {
                echo "<script>alert('Error submitting vote: " . $conn->error . "');</script>";
            }
        }
    }
}

?>

<?php include "../../includes/header.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function enforceSingleSelection(position) {
            let checkboxes = document.querySelectorAll(`input[name="cand_adm_${position}"]`);
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener("change", function () {
                    checkboxes.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                });
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            let positions = new Set();
            document.querySelectorAll("input[type='checkbox']").forEach((checkbox) => {
                positions.add(checkbox.getAttribute("data-position"));
            });

            positions.forEach((position) => enforceSingleSelection(position));
        });
    </script>
</head>
<body class="container mt-5">
    <h2 class="text-center">Vote for Your Candidate</h2>

    <?php if ($has_voted) { ?>
        <p class="alert alert-danger text-center">You have already voted. You cannot vote again.</p>
    <?php } elseif (!empty($candidate)) { ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?uid=' . $id; ?>">
            <div class="list-group">
                <?php 
                $positions = [];
                foreach ($candidate as $cand) { 
                    $pos = htmlspecialchars($cand['Position']);
                    if (!in_array($pos, $positions)) {
                        echo "<h5 class='mt-3'>" . $pos . "</h5>";
                        $positions[] = $pos;
                    }
                ?>
                    <label class="list-group-item">
                        <input type="checkbox" name="cand_adm_<?= $pos ?>" value="<?= htmlspecialchars($cand['Adm_No']) ?>" data-position="<?= $pos ?>">
                        <?= htmlspecialchars($cand['First_Name']) ?> <?= htmlspecialchars($cand['Last_Name']) ?>
                    </label>
                <?php } ?>
            </div>
            <button type="submit" name="vote" class="btn btn-primary mt-3">Submit Vote</button>
        </form>
    <?php } else { ?>
        <p class="alert alert-warning text-center">No candidates available for your course.</p>
    <?php } ?>
</body>
</html>

<?php include "../../includes/footer.php"; ?>
