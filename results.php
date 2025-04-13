<?php

require_once '../Database/db.php';

// Fetch all candidates
$candidate_sql = "SELECT * FROM candidates";
$candidate_result = $conn->query($candidate_sql);

// Fetch votes and count them, joining with candidates using Adm_No
$vote_sql = "
    SELECT c.Adm_No, 'President' AS Position, COUNT(v.President) AS Votes
    FROM votes v
    JOIN candidates c ON v.President = c.Adm_No
    GROUP BY c.Adm_No
    UNION ALL
    SELECT c.Adm_No, 'Welfare', COUNT(v.Welfare)
    FROM votes v
    JOIN candidates c ON v.Welfare = c.Adm_No
    GROUP BY c.Adm_No
    UNION ALL
    SELECT c.Adm_No, 'Sports', COUNT(v.Sports)
    FROM votes v
    JOIN candidates c ON v.Sports = c.Adm_No
    GROUP BY c.Adm_No
    UNION ALL
    SELECT c.Adm_No, 'Academics', COUNT(v.Academics)
    FROM votes v
    JOIN candidates c ON v.Academics = c.Adm_No
    GROUP BY c.Adm_No
";
$vote_result = $conn->query($vote_sql);

// Store votes in an associative array (Adm_No => positions & vote count)
$votes = [];
while ($row = $vote_result->fetch_assoc()) {
    $adm_no = $row['Adm_No'];
    $position = $row['Position'];
    $vote_count = $row['Votes'];

    if (!isset($votes[$adm_no])) {
        $votes[$adm_no] = [
            'President' => 0,
            'Welfare' => 0,
            'Sports' => 0,
            'Academics' => 0
        ];
    }

    $votes[$adm_no][$position] = $vote_count; // Store vote count
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results</title>

    <!-- Bootstrap CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Election Results</h2>
    
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Candidate Name</th>
                <th>Course</th>
                <th>Admission Number</th>
                <th>President Votes</th>
                <th>Welfare Votes</th>
                <th>Sports Votes</th>
                <th>Academics Votes</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($candidate_result->num_rows > 0) {
                while ($candidate = $candidate_result->fetch_assoc()) {
                    $adm_no = $candidate['Adm_No'];
                    echo "<tr>
                        <td>{$candidate['First_Name']} {$candidate['Last_Name']}</td>
                        <td>{$candidate['Course']}</td>
                        <td>{$adm_no}</td>
                        <td>" . ($votes[$adm_no]['President'] ?? 0) . "</td>
                        <td>" . ($votes[$adm_no]['Welfare'] ?? 0) . "</td>
                        <td>" . ($votes[$adm_no]['Sports'] ?? 0) . "</td>
                        <td>" . ($votes[$adm_no]['Academics'] ?? 0) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No candidates found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
