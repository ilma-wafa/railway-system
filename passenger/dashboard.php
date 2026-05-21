<?php
require_once '../includes/auth.php';
require_passenger();
require_once '../includes/db.php';

// Get passenger details
$user_id = $_SESSION['user_id'];
$passenger = $conn->query("
    SELECT p.*, u.email 
    FROM passengers p 
    JOIN users u ON p.user_id = u.user_id 
    WHERE p.user_id = $user_id
")->fetch_assoc();

// Get recent bookings
$bookings = $conn->query("
    SELECT b.booking_number, b.travel_date, b.total_fare, b.status,
           s1.station_name AS from_station,
           s2.station_name AS to_station,
           t.train_name
    FROM bookings b
    JOIN schedules sc ON b.schedule_id = sc.schedule_id
    JOIN routes r ON sc.route_id = r.route_id
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    JOIN trains t ON sc.train_id = t.train_id
    WHERE b.passenger_id = $passenger[passenger_id]
    ORDER BY b.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard | Railway System</title>
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
        .welcome-card {
            background: linear-gradient(135deg, #1a3c5e, #2d6a9f);
            color: white;
            border-radius: 12px;
            border: none;
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
            <div class="card welcome-card p-4 mb-4">
                <h4 class="mb-1">Welcome, <?php echo $passenger['first_name']; ?>!</h4>
                <p class="mb-0 opacity-75">Ready to book your next journey?</p>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a href="search.php" class="btn btn-primary">🔍 Search & Book Trains</a>
                <a href="bookings.php" class="btn btn-outline-secondary">🎫 View My Bookings</a>
            </div>

            <!-- Recent Bookings -->
            <div class="card p-4">
                <h6 class="mb-3">Recent Bookings</h6>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking No.</th>
                            <th>Train</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Travel Date</th>
                            <th>Fare (LKR)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($row = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['booking_number']; ?></td>
                                    <td><?php echo $row['train_name']; ?></td>
                                    <td><?php echo $row['from_station']; ?></td>
                                    <td><?php echo $row['to_station']; ?></td>
                                    <td><?php echo $row['travel_date']; ?></td>
                                    <td><?php echo number_format($row['total_fare'], 2); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $row['status'] == 'confirmed' ? 'bg-success' : 
                                                ($row['status'] == 'cancelled' ? 'bg-danger' : 'bg-warning'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No bookings yet. <a href="search.php">Book your first trip!</a>
                                </td>
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