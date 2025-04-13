<?php
session_start();
require_once '../Database/db.php';

// Check if already logged in as admin
if(isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $sql = "SELECT * FROM users WHERE Email = ? AND role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['Pass'])) {
                // Update last login
                $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE SN = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $admin['SN']);
                $update_stmt->execute();

                // Set session variables
                $_SESSION['user_id'] = $admin['SN'];
                $_SESSION['user_name'] = $admin['First_Name'] . ' ' . $admin['Last_Name'];
                $_SESSION['user_role'] = 'admin';

                // Log the login action
                $log_sql = "INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'login', 'Admin logged in successfully', ?)";
                $log_stmt = $conn->prepare($log_sql);
                $ip = $_SERVER['REMOTE_ADDR'];
                $log_stmt->bind_param("is", $admin['SN'], $ip);
                $log_stmt->execute();

                header("Location: ../Dashboard/Admin/index.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Admin not found or account is inactive";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Zetech Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0052d4, #4364f7, #6fb1fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-title {
            color: #0052d4;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2 class="form-title">Admin Login</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p>Don't have an admin account? <a href="register.php">Register here</a></p>
                <p><a href="../index.php">Back to Main Site</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 