<?php
require '../Database/db.php';

$error = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_adm_no = trim($_POST['email_or_adm_no']);
    $password = trim($_POST['password']);

    // Validate user input
    if (empty($email_or_adm_no) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        // Check if user exists using either email or Adm_No
        $sql = "SELECT SN, First_Name, Last_Name, Pass, role, status FROM users WHERE Email = ? OR Adm_No = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email_or_adm_no, $email_or_adm_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Check if account is active
            if ($user['status'] !== 'active') {
                $error = "Your account is not active. Please contact the administrator.";
            } else {
                // Verify password
                if (password_verify($password, $user['Pass'])) {
                    // Start session and set session variables
                    session_start();
                    $_SESSION['user_id'] = $user['SN'];
                    $_SESSION['user_name'] = $user['First_Name'] . ' ' . $user['Last_Name'];
                    $_SESSION['user_role'] = $user['role'];

                    // Update last login
                    $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE SN = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $user['SN']);
                    $update_stmt->execute();

                    // Log the login action
                    $log_sql = "INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'login', 'User logged in successfully', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $log_stmt->bind_param("is", $user['SN'], $ip);
                    $log_stmt->execute();

                    // Redirect based on role
                    if ($user['role'] === 'voter') {
                        header("Location: ../Dashboard/Voter/index.php");
                    } elseif ($user['role'] === 'admin') {
                        header("Location: ../Dashboard/Admin/index.php");
                    }
                    exit();
                } else {
                    $error = "Invalid email/Adm_No or password.";
                }
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
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
            <h2 class="form-title">Student Online Voting Login</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email_or_adm_no" class="form-label">Email or Admission Number</label>
                    <input type="text" class="form-control" id="email_or_adm_no" name="email_or_adm_no" required>
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
                <p>Don't have an account? <a href="signup.php">Register</a></p>
                <p><a href="../index.php">Back to Main Site</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
