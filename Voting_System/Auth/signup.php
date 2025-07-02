<?php

include '../Database/db.php';

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $adm_no = trim($_POST['adm_no']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year = (int)$_POST['year'];
    $pass = $_POST['pass'];
    $confirm = $_POST['confirm'];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($adm_no) || empty($email) || empty($course) || empty($pass)) {
        $error = 'Please fill in all required fields';
    } elseif ($pass !== $confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($year < 1 || $year > 4) {
        $error = 'Invalid year of study';
    } else {
        // Check if email or admission number already exists
        $check_sql = "SELECT SN FROM users WHERE Email = ? OR Adm_No = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $email, $adm_no);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email or Admission Number already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (First_Name, Last_Name, Adm_No, Email, Course, Year_of_Study, Pass, role, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'voter', 'active', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssis", $first_name, $last_name, $adm_no, $email, $course, $year, $hashed_password);
            
            if ($stmt->execute()) {
                // Log the registration
                $user_id = $conn->insert_id;
                $log_sql = "INSERT INTO audit_log (user_id, action, details, ip_address) 
                           VALUES (?, 'registration', 'New voter account created', ?)";
                $log_stmt = $conn->prepare($log_sql);
                $ip = $_SERVER['REMOTE_ADDR'];
                $log_stmt->bind_param("is", $user_id, $ip);
                $log_stmt->execute();

                $success = 'Registration successful. You can now login';
            } else {
                $error = 'An error occurred. Please try again';
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
    <title>Zetech University - Voter Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0052d4, #4364f7, #6fb1fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
            <h2 class="form-title">Voter Registration</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first-name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first-name" name="first_name" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="last-name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last-name" name="last_name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="adm-no" class="form-label">Student ID Number</label>
                    <input type="text" class="form-control" id="adm-no" name="adm_no" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">University Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="course" class="form-label">Course</label>
                    <input type="text" class="form-control" id="course" name="course" required>
                </div>

                <div class="mb-3">
                    <label for="year" class="form-label">Year of Study</label>
                    <input type="number" class="form-control" id="year" name="year" min="1" max="4" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Create Password</label>
                    <input type="password" class="form-control" id="password" name="pass" required>
                </div>

                <div class="mb-3">
                    <label for="confirm-password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm-password" name="confirm" required>
                </div>

                <div class="d-grid">
                    <button type="submit" name="submit" class="btn btn-primary">Register as Voter</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p>Already registered? <a href="index.php">Login here</a></p>
                <p><a href="../index.php">Back to Main Site</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
