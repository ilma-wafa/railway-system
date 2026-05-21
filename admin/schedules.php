<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/db.php';

$success = '';
$error = '';

// Handle Add Schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_schedule'])) {
    $train_id = (int)$_POST['train_id'];
    $route_id = (int)$_POST['route_id'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $fare = (float)$_POST['fare'];

    $stmt = $conn->prepare("INSERT INTO schedules (train_id, route_id, departure_time, arrival_time, fare) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissd", $train_id, $route_id, $departure_time, $arrival_time, $fare);
    if ($stmt->execute()) {
        $success = "Schedule added successfully.";
    } else {
        $error = "Something went wrong. Please try again.";
    }
}

// Handle Delete Schedule
if (isset($_GET['delete'])) {
    $schedule_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM schedules WHERE schedule_id = $schedule_id");
    $success = "Schedule deleted successfully.";
}

// Handle Toggle Status
if (isset($_GET['toggle'])) {
    $schedule_id = (int)$_GET['toggle'];
    $current = $conn->query("SELECT status FROM schedules WHERE schedule_id = $schedule_id")->fetch_assoc();
    $new_status = $current['status'] == 'active' ? 'cancelled' : 'active';
    $conn->query("UPDATE schedules SET status = '$new_status' WHERE schedule_id = $schedule_id");
    $success = "Schedule status updated.";
}

// Fetch trains and routes for dropdowns
$trains = $conn->query("SELECT * FROM trains ORDER BY train_name ASC");
$routes = $conn->query("
    SELECT r.route_id, r.route_name,
           s1.station_name AS start_station,
           s2.station_name AS end_station
    FROM routes r
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    ORDER BY r.route_name ASC
");

// Fetch all schedules
$schedules = $conn->query("
    SELECT sc.schedule_id, sc.departure_time, sc.arrival_time, sc.fare, sc.status,
           t.train_name, t.train_number,
           r.route_name,
           s1.station_name AS start_station,
           s2.station_name AS end_station
    FROM schedules sc
    JOIN trains t ON sc.train_id = t.train_id
    JOIN routes r ON sc.route_id = r.route_id
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    ORDER BY sc.schedule_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules | Railway System</title>
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
            <a href="trains.php">🚆 Trains</a>
            <a href="stations.php">🏛️ Stations</a>
            <a href="routes.php">🗺️ Routes</a>
            <a href="schedules.php">🕐 Schedules</a>
            <a href="passengers.php">👥 Passengers</a>
            <a href="employees.php">👤 Employees</a>
            <a href="bookings.php">🎫 Bookings</a>
            <a href="logout.php" style="margin-top:20px; color:#e74c3c;">🚪 Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">Manage Schedules</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Schedule Form -->
            <div class="card p-4 mb-4">
                <h6 class="mb-3">Add New Schedule</h6>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Train</label>
                            <select name="train_id" class="form-select" required>
                                <option value="">Select Train</option>
                                <?php while ($train = $trains->fetch_assoc()): ?>
                                    <option value="<?php echo $train['train_id']; ?>">
                                        <?php echo $train['train_number'] . ' - ' . $train['train_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Route</label>
                            <select name="route_id" class="form-select" required>
                                <option value="">Select Route</option>
                                <?php while ($route = $routes->fetch_assoc()): ?>
                                    <option value="<?php echo $route['route_id']; ?>">
                                        <?php echo $route['start_station'] . ' → ' . $route['end_station']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Departure Time</label>
                            <input type="time" name="departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Arrival Time</label>
                            <input type="time" name="arrival_time" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fare (LKR)</label>
                            <input type="number" name="fare" class="form-control" min="1" step="0.01" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="add_schedule" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Schedules Table -->
            <div class="card p-4">
                <h6 class="mb-3">All Schedules</h6>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Train</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Fare (LKR)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($schedules->num_rows > 0): ?>
                            <?php while ($row = $schedules->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['schedule_id']; ?></td>
                                    <td><?php echo $row['train_number'] . ' - ' . $row['train_name']; ?></td>
                                    <td><?php echo $row['start_station'] . ' → ' . $row['end_station']; ?></td>
                                    <td><?php echo $row['departure_time']; ?></td>
                                    <td><?php echo $row['arrival_time']; ?></td>
                                    <td><?php echo number_format($row['fare'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?toggle=<?php echo $row['schedule_id']; ?>"
                                           class="btn btn-warning btn-sm">
                                           Toggle
                                        </a>
                                        <a href="?delete=<?php echo $row['schedule_id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this schedule?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted">No schedules found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>