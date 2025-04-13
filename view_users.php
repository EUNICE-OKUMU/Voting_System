<?php
// Database connection
include "../../Database/db.php";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users from the database
$sql = "SELECT SN, First_Name, Last_Name, Adm_No, Email, Course, Year_of_Study, user_role, status FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">User List</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>SN</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Admission No</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Year of Study</th>
                    <th>User Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['SN']; ?></td>
                            <td><?php echo $row['First_Name']; ?></td>
                            <td><?php echo $row['Last_Name']; ?></td>
                            <td><?php echo $row['Adm_No']; ?></td>
                            <td><?php echo $row['Email']; ?></td>
                            <td><?php echo $row['Course']; ?></td>
                            <td><?php echo $row['Year_of_Study']; ?></td>
                            <td><?php echo ucfirst($row['user_role']); ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>