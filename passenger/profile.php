<?php
require_once '../includes/auth.php';
require_passenger();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current details
$passenger = $conn->query("
    SELECT p.*, u.email 
    FROM passengers p 
    JOIN users u ON p.user_id = u.user_id 
    WHERE p.user_id = $user_id
")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $nic = trim($_POST['nic']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE passengers SET first_name=?, last_name=?, phone=?, nic=?, address=? WHERE user_id=?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $phone, $nic, $address, $user_id);

    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
        $passenger = $conn->query("
            SELECT p.*, u.email 
            FROM passengers p 
            JOIN users u ON p.user_id = u.user_id 
            WHERE p.user_id = $user_id
        ")->fetch_assoc();
    } else {
        $error = "Something went wrong. Please try again.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = md5(trim($_POST['current_password']));
    $new_password = md5(trim($_POST['new_password']));
    $confirm_password = md5(trim($_POST['confirm_password']));

    $check = $conn->query("SELECT user_id FROM users WHERE user_id = $user_id AND password = '$current_password'");

    if ($check->num_rows == 0) {
        $error = "Current password is incorrect.";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $conn->query("UPDATE users SET password = '$new_password' WHERE user_id = $user_id");
        $success = "Password changed successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Railway System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .sidebar {
            min-height: 100vh;
            background-color: #1a3c5e;
            padding-top: 20px;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
        }
        .sidebar a:hover {
            color: white;
            background-color: #0d2137;
        }
        .sidebar .brand {
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 10px 20px 20px;
            border-bottom: 1px solid #0d2137;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar px-0">
            <div class="brand">🚂 Railway System</div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="search.php">🔍 Search Trains</a>
            <a href="bookings.php">🎫 My Bookings</a>
            <a href="profile.php">👤 My Profile</a>
            <a href="logout.php" style="margin-top:20px; color:#e74c3c;">🚪 Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">My Profile</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Update Profile -->
                <div class="col-md-6">
                    <div class="card p-4">
                        <h6 class="mb-3">Personal Information</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control"
                                       value="<?php echo $passenger['first_name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control"
                                       value="<?php echo $passenger['last_name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                       value="<?php echo $passenger['email']; ?>" disabled>
                                <div class="form-text">Email cannot be changed.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIC Number</label>
                                <input type="text" name="nic" class="form-control"
                                       value="<?php echo $passenger['nic']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?php echo $passenger['phone']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo $passenger['address']; ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-md-6">
                    <div class="card p-4">
                        <h6 class="mb-3">Change Password</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>