<?php
session_start();

// If already logged in, redirect to correct dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['user_type'] == 'employee') {
        header("Location: employee/dashboard.php");
    } else {
        header("Location: passenger/dashboard.php");
    }
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'includes/db.php';
    
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    
    $stmt = $conn->prepare("SELECT user_id, user_type FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
        
        if ($user['user_type'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($user['user_type'] == 'employee') {
            header("Location: employee/dashboard.php");
        } else {
            header("Location: passenger/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway System | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .login-card {
            max-width: 400px;
            margin: 100px auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .login-header {
            background-color: #1a3c5e;
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 24px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-header">
            <h4 class="mb-0">🚂 Railway System</h4>
            <small>Sri Lanka Railway Reservation</small>
        </div>
        <div class="card-body p-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="text-center mt-3">
                    <small>New passenger? <a href="passenger/register.php">Register here</a></small>
                </div>
            </form>
        </div>
    </div>
</body>
</html>