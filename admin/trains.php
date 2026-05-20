<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/db.php';

$success = '';
$error = '';

// Handle Add Train
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_train'])) {
    $train_number = trim($_POST['train_number']);
    $train_name = trim($_POST['train_name']);
    $train_type = $_POST['train_type'];
    $total_seats = (int)$_POST['total_seats'];

    $check = $conn->prepare("SELECT train_id FROM trains WHERE train_number = ?");
    $check->bind_param("s", $train_number);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Train number already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO trains (train_number, train_name, train_type, total_seats) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $train_number, $train_name, $train_type, $total_seats);
        if ($stmt->execute()) {
            $success = "Train added successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// Handle Delete Train
if (isset($_GET['delete'])) {
    $train_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM trains WHERE train_id = $train_id");
    $success = "Train deleted successfully.";
}

// Fetch all trains
$trains = $conn->query("SELECT * FROM trains ORDER BY train_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trains | Railway System</title>
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
            <h4 class="mb-4">Manage Trains</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Train Form -->
            <div class="card p-4 mb-4">
                <h6 class="mb-3">Add New Train</h6>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Train Number</label>
                            <input type="text" name="train_number" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Train Name</label>
                            <input type="text" name="train_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Train Type</label>
                            <select name="train_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="express">Express</option>
                                <option value="intercity">Intercity</option>
                                <option value="local">Local</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="add_train" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Trains Table -->
            <div class="card p-4">
                <h6 class="mb-3">All Trains</h6>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Train Number</th>
                            <th>Train Name</th>
                            <th>Type</th>
                            <th>Total Seats</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($trains->num_rows > 0): ?>
                            <?php while ($row = $trains->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['train_id']; ?></td>
                                    <td><?php echo $row['train_number']; ?></td>
                                    <td><?php echo $row['train_name']; ?></td>
                                    <td><?php echo ucfirst($row['train_type']); ?></td>
                                    <td><?php echo $row['total_seats']; ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['train_id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this train?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No trains found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>