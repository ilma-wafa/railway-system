<?php
require_once '../includes/auth.php';
require_employee();
require_once '../includes/db.php';

// Fetch all bookings
$bookings = $conn->query("
    SELECT b.booking_id, b.booking_number, b.travel_date,
           b.num_passengers, b.total_fare, b.status,
           p.first_name, p.last_name,
           t.train_name, t.train_number,
           s1.station_name AS from_station,
           s2.station_name AS to_station,
           GROUP_CONCAT(se.seat_number ORDER BY se.seat_number SEPARATOR ', ') AS seats
    FROM bookings b
    JOIN passengers p ON b.passenger_id = p.passenger_id
    JOIN schedules sc ON b.schedule_id = sc.schedule_id
    JOIN trains t ON sc.train_id = t.train_id
    JOIN routes r ON sc.route_id = r.route_id
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    LEFT JOIN seats se ON b.booking_id = se.booking_id
    GROUP BY b.booking_id
    ORDER BY b.travel_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings | Railway System</title>
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
            <a href="bookings.php">🎫 Bookings</a>
            <a href="logout.php" style="margin-top:20px; color:#e74c3c;">🚪 Logout</a>
        </div>

        <div class="col-md-10 p-4">
            <h4 class="mb-4">All Bookings</h4>

            <div class="card p-4">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking No.</th>
                            <th>Passenger</th>
                            <th>Train</th>
                            <th>Route</th>
                            <th>Travel Date</th>
                            <th>Seats</th>
                            <th>Fare (LKR)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($row = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['booking_number']; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['train_name']; ?></td>
                                    <td><?php echo $row['from_station'] . ' → ' . $row['to_station']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['travel_date'])); ?></td>
                                    <td><?php echo $row['seats']; ?></td>
                                    <td><?php echo number_format($row['total_fare'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] == 'confirmed' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No bookings found.</td>
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