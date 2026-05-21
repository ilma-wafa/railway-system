<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/db.php';

$success = '';
$error = '';

// Handle Add Route
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_route'])) {
    $route_name = trim($_POST['route_name']);
    $start_station_id = (int)$_POST['start_station_id'];
    $end_station_id = (int)$_POST['end_station_id'];

    if ($start_station_id == $end_station_id) {
        $error = "Start and end station cannot be the same.";
    } else {
        $stmt = $conn->prepare("INSERT INTO routes (route_name, start_station_id, end_station_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $route_name, $start_station_id, $end_station_id);
        if ($stmt->execute()) {
            $success = "Route added successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// Handle Delete Route
if (isset($_GET['delete'])) {
    $route_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM routes WHERE route_id = $route_id");
    $success = "Route deleted successfully.";
}

// Fetch all stations for dropdown
$stations = $conn->query("SELECT * FROM stations ORDER BY station_name ASC");
$stations_list = [];
while ($row = $stations->fetch_assoc()) {
    $stations_list[] = $row;
}

// Fetch all routes with station names
$routes = $conn->query("
    SELECT r.route_id, r.route_name,
           s1.station_name AS start_station,
           s2.station_name AS end_station
    FROM routes r
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    ORDER BY r.route_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes | Railway System</title>
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
            <h4 class="mb-4">Manage Routes</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Route Form -->
            <div class="card p-4 mb-4">
                <h6 class="mb-3">Add New Route</h6>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Route Name</label>
                            <input type="text" name="route_name" class="form-control"
                                   placeholder="e.g. Colombo Fort to Kandy" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Station</label>
                            <select name="start_station_id" class="form-select" required>
                                <option value="">Select Station</option>
                                <?php foreach ($stations_list as $station): ?>
                                    <option value="<?php echo $station['station_id']; ?>">
                                        <?php echo $station['station_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Station</label>
                            <select name="end_station_id" class="form-select" required>
                                <option value="">Select Station</option>
                                <?php foreach ($stations_list as $station): ?>
                                    <option value="<?php echo $station['station_id']; ?>">
                                        <?php echo $station['station_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="add_route" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Routes Table -->
            <div class="card p-4">
                <h6 class="mb-3">All Routes</h6>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Route Name</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($routes->num_rows > 0): ?>
                            <?php while ($row = $routes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['route_id']; ?></td>
                                    <td><?php echo $row['route_name']; ?></td>
                                    <td><?php echo $row['start_station']; ?></td>
                                    <td><?php echo $row['end_station']; ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['route_id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this route?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">No routes found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>