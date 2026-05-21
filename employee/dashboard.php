<?php
require_once '../includes/auth.php';
require_employee();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$employee = $conn->query("
    SELECT e.*, u.email 
    FROM employees e 
    JOIN users u ON e.user_id = u.user_id 
    WHERE e.user_id = $user_id
")->fetch_assoc();

// Stats
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['count'];
$total_cancelled = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'")->fetch_assoc()['count'];
$total_trains = $conn->query("SELECT COUNT(*) as count FROM trains")->fetch_assoc()['count'];
$total_passengers = $conn->query("SELECT COUNT(*) as count FROM passengers")->fetch_assoc()['count'];

// Today's schedules
$today_schedules = $conn->query("
    SELECT sc.departure_time, sc.arrival_time, sc.status,
           t.train_name, t.train_number,
           s1.station_name AS from_station,
           s2.station_name AS to_station
    FROM schedules sc
    JOIN trains t ON sc.train_id = t.train_id
    JOIN routes r ON sc.route_id = r.route_id
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    WHERE sc.status = 'active'
    ORDER BY sc.departure_time ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | Railway System</title>
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
            <a href="bookings.php">🎫 Bookings</a>
            <a href="logout.php" style="margin-top:20px; color:#e74c3c;">🚪 Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">Welcome, <?php echo $employee['first_name']; ?>!</h4>

            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Active Bookings</div>
                        <div class="fs-2 fw-bold text-success"><?php echo $total_bookings; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Cancelled Bookings</div>
                        <div class="fs-2 fw-bold text-danger"><?php echo $total_cancelled; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Total Trains</div>
                        <div class="fs-2 fw-bold text-primary"><?php echo $total_trains; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3">
                        <div class="text-muted small">Total Passengers</div>
                        <div class="fs-2 fw-bold text-warning"><?php echo $total_passengers; ?></div>
                    </div>
                </div>
            </div>

            <!-- Active Schedules -->
            <div class="card p-4">
                <h6 class="mb-3">Active Train Schedules</h6>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Train</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($today_schedules->num_rows > 0): ?>
                            <?php while ($row = $today_schedules->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['train_number'] . ' - ' . $row['train_name']; ?></td>
                                    <td><?php echo $row['from_station']; ?></td>
                                    <td><?php echo $row['to_station']; ?></td>
                                    <td><?php echo substr($row['departure_time'], 0, 5); ?></td>
                                    <td><?php echo substr($row['arrival_time'], 0, 5); ?></td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No active schedules.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>