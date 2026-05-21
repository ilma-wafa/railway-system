<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/db.php';

$success = '';
$error = '';

// Handle Add Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_employee'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "An account with this email already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, 'employee')");
        $stmt->bind_param("ss", $email, $password);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $stmt2 = $conn->prepare("INSERT INTO employees (user_id, first_name, last_name, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("issss", $user_id, $first_name, $last_name, $phone, $address);

            if ($stmt2->execute()) {
                $success = "Employee added successfully.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

// Handle Delete Employee
if (isset($_GET['delete'])) {
    $employee_id = (int)$_GET['delete'];
    $user_id_to_delete = $conn->query("SELECT user_id FROM employees WHERE employee_id = $employee_id")->fetch_assoc()['user_id'];
    $conn->query("DELETE FROM employees WHERE employee_id = $employee_id");
    $conn->query("DELETE FROM users WHERE user_id = $user_id_to_delete");
    $success = "Employee deleted successfully.";
}

// Fetch all employees
$employees = $conn->query("
    SELECT e.*, u.email, u.created_at
    FROM employees e
    JOIN users u ON e.user_id = u.user_id
    ORDER BY e.employee_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees | Railway System</title>
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

        <div class="col-md-10 p-4">
            <h4 class="mb-4">Manage Employees</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Employee Form -->
            <div class="card p-4 mb-4">
                <h6 class="mb-3">Add New Employee</h6>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="add_employee" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Employees Table -->
            <div class="card p-4">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Added</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($employees->num_rows > 0): ?>
                            <?php while ($row = $employees->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['employee_id']; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone'] ?: '—'; ?></td>
                                    <td><?php echo $row['address'] ?: '—'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['employee_id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this employee?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No employees yet.</td>
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