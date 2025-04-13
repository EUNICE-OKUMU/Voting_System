<?php
session_start();
require_once '../Database/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $admin_code = trim($_POST['admin_code']);

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($admin_code)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($admin_code !== 'ADMIN123') {
        $error = "Invalid admin registration code";
    } else {
        // Check if email already exists
        $check_sql = "SELECT SN FROM users WHERE Email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Generate admin code
            $admin_code = 'ADM' . strtoupper(substr(md5(uniqid()), 0, 6));
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $sql = "INSERT INTO users (First_Name, Last_Name, Adm_No, Email, Course, Year_of_Study, Pass, role, admin_code, status, created_at) 
                    VALUES (?, ?, ?, ?, 'Administration', 1, ?, 'admin', ?, 'active', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($sql);
            $adm_no = 'ADMIN' . rand(1000, 9999);
            $stmt->bind_param("ssssss", $first_name, $last_name, $adm_no, $email, $hashed_password, $admin_code);
            
            if ($stmt->execute()) {
                // Log the registration
                $admin_id = $conn->insert_id;
                $log_sql = "INSERT INTO audit_log (admin_id, action, details, ip_address) 
                           VALUES (?, 'registration', 'New admin account created', ?)";
                $log_stmt = $conn->prepare($log_sql);
                $ip = $_SERVER['REMOTE_ADDR'];
                $log_stmt->bind_param("is", $admin_id, $ip);
                $log_stmt->execute();

                $success = "Admin registration successful! Your admin code is: " . $admin_code;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Zetech Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0052d4, #4364f7, #6fb1fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
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
        <div class="register-form">
            <h2 class="form-title">Admin Registration</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="mb-3">
                    <label for="admin_code" class="form-label">Admin Registration Code</label>
                    <input type="text" class="form-control" id="admin_code" name="admin_code" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="../index.php">Back to Main Site</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 