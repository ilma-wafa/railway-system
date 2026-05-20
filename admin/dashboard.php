<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/db.php';

// Fetch stats for dashboard
$total_trains = $conn->query("SELECT COUNT(*) as count FROM trains")->fetch_assoc()['count'];
$total_passengers = $conn->query("SELECT COUNT(*) as count FROM passengers")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Railway System</title>
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
        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
            <a href="trains.php">🚆 Trains</a>
            <a href="stations.php">🏛️ Stations</a>
            <a href="schedules.php">🕐 Schedules</a>
            <a href="passengers.php">👥 Passengers</a>
            <a href="employees.php">👤 Employees</a>
            <a href="bookings.php">🎫 Bookings</a>
            <a href="logout.php" style="margin-top:20px; color:#e74c3c;">🚪 Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">Admin Dashboard</h4>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Total Trains</div>
                        <div class="fs-2 fw-bold text-primary"><?php echo $total_trains; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Total Passengers</div>
                        <div class="fs-2 fw-bold text-success"><?php echo $total_passengers; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Total Bookings</div>
                        <div class="fs-2 fw-bold text-warning"><?php echo $total_bookings; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Total Employees</div>
                        <div class="fs-2 fw-bold text-danger"><?php echo $total_employees; ?></div>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-flex gap-2">
                    <a href="trains.php" class="btn btn-primary btn-sm">Add Train</a>
                    <a href="stations.php" class="btn btn-secondary btn-sm">Add Station</a>
                    <a href="schedules.php" class="btn btn-success btn-sm">Add Schedule</a>
                    <a href="employees.php" class="btn btn-warning btn-sm">Add Employee</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>