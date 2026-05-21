<?php
require_once '../includes/auth.php';
require_passenger();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$passenger = $conn->query("SELECT * FROM passengers WHERE user_id = $user_id")->fetch_assoc();

$success = '';
$error = '';

// Handle cancellation
if (isset($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    $check = $conn->query("SELECT * FROM bookings WHERE booking_id = $booking_id AND passenger_id = {$passenger['passenger_id']} AND status = 'confirmed'");
    
    if ($check->num_rows > 0) {
        $conn->query("UPDATE bookings SET status = 'cancelled' WHERE booking_id = $booking_id");
        $success = "Booking cancelled successfully.";
    } else {
        $error = "Booking not found or already cancelled.";
    }
}

// Fetch all bookings
$bookings = $conn->query("
    SELECT b.booking_id, b.booking_number, b.travel_date, 
           b.num_passengers, b.total_fare, b.status, b.created_at,
           t.train_name, t.train_number,
           s1.station_name AS from_station,
           s2.station_name AS to_station,
           sc.departure_time, sc.arrival_time,
           GROUP_CONCAT(se.seat_number ORDER BY se.seat_number SEPARATOR ', ') AS seats
    FROM bookings b
    JOIN schedules sc ON b.schedule_id = sc.schedule_id
    JOIN trains t ON sc.train_id = t.train_id
    JOIN routes r ON sc.route_id = r.route_id
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    LEFT JOIN seats se ON b.booking_id = se.booking_id
    WHERE b.passenger_id = {$passenger['passenger_id']}
    GROUP BY b.booking_id
    ORDER BY b.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Railway System</title>
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
            <h4 class="mb-4">My Bookings</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($bookings->num_rows > 0): ?>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <div class="card mb-3 p-4">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <div class="text-muted small">Booking No.</div>
                                <div class="fw-bold"><?php echo $row['booking_number']; ?></div>
                                <div class="text-muted small"><?php echo date('d M Y', strtotime($row['created_at'])); ?></div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-muted small">Train</div>
                                <div class="fw-bold"><?php echo $row['train_name']; ?></div>
                                <div class="text-muted small"><?php echo $row['train_number']; ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small">Route</div>
                                <div class="fw-bold"><?php echo $row['from_station']; ?> → <?php echo $row['to_station']; ?></div>
                                <div class="text-muted small">
                                    <?php echo substr($row['departure_time'], 0, 5); ?> → <?php echo substr($row['arrival_time'], 0, 5); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-muted small">Travel Date</div>
                                <div class="fw-bold"><?php echo date('d M Y', strtotime($row['travel_date'])); ?></div>
                                <div class="text-muted small">Seats: <?php echo $row['seats']; ?></div>
                            </div>
                            <div class="col-md-1">
                                <div class="text-muted small">Fare</div>
                                <div class="fw-bold text-primary">LKR <?php echo number_format($row['total_fare'], 2); ?></div>
                            </div>
                            <div class="col-md-1">
                                <span class="badge <?php echo $row['status'] == 'confirmed' ? 'bg-success' : 'bg-danger'; ?> p-2">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </div>
                            <div class="col-md-1 text-end">
                                <?php if ($row['status'] == 'confirmed'): ?>
                                    <a href="?cancel=<?php echo $row['booking_id']; ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Cancel this booking?')">
                                       Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card p-5 text-center">
                    <p class="text-muted">No bookings yet.</p>
                    <a href="search.php" class="btn btn-primary">Book Your First Trip</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>