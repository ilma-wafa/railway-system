<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/db.php';

$success = '';
$error = '';

// Handle Add Station
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_station'])) {
    $station_name = trim($_POST['station_name']);
    $station_code = strtoupper(trim($_POST['station_code']));
    $district = trim($_POST['district']);
    $contact_number = trim($_POST['contact_number']);

    $check = $conn->prepare("SELECT station_id FROM stations WHERE station_code = ?");
    $check->bind_param("s", $station_code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Station code already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO stations (station_name, station_code, district, contact_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $station_name, $station_code, $district, $contact_number);
        if ($stmt->execute()) {
            $success = "Station added successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// Handle Delete Station
if (isset($_GET['delete'])) {
    $station_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM stations WHERE station_id = $station_id");
    $success = "Station deleted successfully.";
}

// Fetch all stations
$stations = $conn->query("SELECT * FROM stations ORDER BY station_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stations | Railway System</title>
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
            <a href="schedules.php">🕐 Schedules</a>
            <a href="passengers.php">👥 Passengers</a>
            <a href="employees.php">👤 Employees</a>
            <a href="bookings.php">🎫 Bookings</a>
            <a href="logout.php" style="margin-top:20px; color:#e74c3c;">🚪 Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">Manage Stations</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Station Form -->
            <div class="card p-4 mb-4">
                <h6 class="mb-3">Add New Station</h6>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Station Name</label>
                            <input type="text" name="station_name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Station Code</label>
                            <input type="text" name="station_code" class="form-control" maxlength="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">District</label>
                            <input type="text" name="district" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="add_station" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stations Table -->
            <div class="card p-4">
                <h6 class="mb-3">All Stations</h6>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Station Name</th>
                            <th>Code</th>
                            <th>District</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stations->num_rows > 0): ?>
                            <?php while ($row = $stations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['station_id']; ?></td>
                                    <td><?php echo $row['station_name']; ?></td>
                                    <td><?php echo $row['station_code']; ?></td>
                                    <td><?php echo $row['district']; ?></td>
                                    <td><?php echo $row['contact_number']; ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['station_id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this station?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No stations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>