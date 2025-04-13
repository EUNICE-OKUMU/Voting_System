<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Candidates</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Candidates List</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Position</th>
                    <th>Course</th>
                    <th>Year of Study</th>
                    <th>Manifesto</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Database connection
                include "../../Database/db.php";

                // Fetch candidates data
                $sql = "SELECT * FROM candidates";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['candidate_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['First_Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Last_Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Position']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Course']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Year_of_Study']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Manifesto']) . "</td>";
                        echo "<td>";
                        if (!empty($row['Photo'])) {
                            echo "<img src='uploads/" . htmlspecialchars($row['Photo']) . "' alt='Photo' width='50'>";
                        } else {
                            echo "No Photo";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>No candidates found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>