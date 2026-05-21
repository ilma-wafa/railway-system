<?php
session_start();
require_once '../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $nic = trim($_POST['nic']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "An account with this email already exists.";
    } else {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, 'passenger')");
        $stmt->bind_param("ss", $email, $password);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            // Insert into passengers table
            $stmt2 = $conn->prepare("INSERT INTO passengers (user_id, first_name, last_name, nic, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssss", $user_id, $first_name, $last_name, $nic, $phone, $address);

            if ($stmt2->execute()) {
                $success = "Account created successfully. You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Railway System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .register-card {
            max-width: 500px;
            margin: 60px auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .register-header {
            background-color: #1a3c5e;
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 24px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card register-card">
        <div class="register-header">
            <h4 class="mb-0">🚂 Railway System</h4>
            <small>Create a Passenger Account</small>
        </div>
        <div class="card-body p-4">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <a href="../index.php">Login here</a>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIC Number</label>
                        <input type="text" name="nic" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Create Account</button>
                    </div>
                    <div class="col-12 text-center">
                        <small>Already have an account? <a href="../index.php">Login here</a></small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>