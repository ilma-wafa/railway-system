<?php
require_once '../includes/auth.php';
require_passenger();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$passenger = $conn->query("SELECT * FROM passengers WHERE user_id = $user_id")->fetch_assoc();

// Fetch stations for dropdowns
$stations = $conn->query("SELECT * FROM stations ORDER BY station_name ASC");
$stations_list = [];
while ($row = $stations->fetch_assoc()) {
    $stations_list[] = $row;
}

// Search results
$results = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $from_station = (int)$_POST['from_station'];
    $to_station = (int)$_POST['to_station'];
    $travel_date = $_POST['travel_date'];
    $num_passengers = (int)$_POST['num_passengers'];

    $searched = true;

    $query = $conn->prepare("
        SELECT sc.schedule_id, sc.departure_time, sc.arrival_time, sc.fare,
               t.train_name, t.train_number, t.train_type, t.total_seats,
               r.route_name,
               s1.station_name AS from_station,
               s2.station_name AS to_station,
               (SELECT COUNT(*) FROM seats se 
                JOIN bookings b ON se.booking_id = b.booking_id 
                WHERE b.schedule_id = sc.schedule_id 
                AND b.travel_date = ?
                AND b.status != 'cancelled') AS booked_seats
        FROM schedules sc
        JOIN trains t ON sc.train_id = t.train_id
        JOIN routes r ON sc.route_id = r.route_id
        JOIN stations s1 ON r.start_station_id = s1.station_id
        JOIN stations s2 ON r.end_station_id = s2.station_id
        WHERE r.start_station_id = ?
        AND r.end_station_id = ?
        AND sc.status = 'active'
    ");
    $query->bind_param("sii", $travel_date, $from_station, $to_station);
    $query->execute();
    $results = $query->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Trains | Railway System</title>
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
        .train-card {
            border-radius: 12px;
            border: 1px solid #dee2e6;
            transition: box-shadow 0.2s;
        }
        .train-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
            <h4 class="mb-4">Search Trains</h4>

            <!-- Search Form -->
            <div class="card p-4 mb-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From</label>
                            <select name="from_station" class="form-select" required>
                                <option value="">Select Station</option>
                                <?php foreach ($stations_list as $station): ?>
                                    <option value="<?php echo $station['station_id']; ?>"
                                        <?php echo (isset($_POST['from_station']) && $_POST['from_station'] == $station['station_id']) ? 'selected' : ''; ?>>
                                        <?php echo $station['station_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To</label>
                            <select name="to_station" class="form-select" required>
                                <option value="">Select Station</option>
                                <?php foreach ($stations_list as $station): ?>
                                    <option value="<?php echo $station['station_id']; ?>"
                                        <?php echo (isset($_POST['to_station']) && $_POST['to_station'] == $station['station_id']) ? 'selected' : ''; ?>>
                                        <?php echo $station['station_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Travel Date</label>
                            <input type="date" name="travel_date" class="form-control"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo isset($_POST['travel_date']) ? $_POST['travel_date'] : ''; ?>"
                                   required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Passengers</label>
                            <input type="number" name="num_passengers" class="form-control"
                                   min="1" max="10"
                                   value="<?php echo isset($_POST['num_passengers']) ? $_POST['num_passengers'] : 1; ?>"
                                   required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="search" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <?php if ($searched): ?>
                <?php if ($results && $results->num_rows > 0): ?>
                    <h6 class="mb-3">Available Trains</h6>
                    <?php while ($train = $results->fetch_assoc()): ?>
                        <?php $available_seats = $train['total_seats'] - $train['booked_seats']; ?>
                        <div class="card train-card p-4 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="fw-bold"><?php echo $train['train_name']; ?></div>
                                    <div class="text-muted small"><?php echo $train['train_number'] . ' · ' . ucfirst($train['train_type']); ?></div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="fw-bold"><?php echo substr($train['departure_time'], 0, 5); ?></div>
                                    <div class="text-muted small"><?php echo $train['from_station']; ?></div>
                                </div>
                                <div class="col-md-1 text-center text-muted">→</div>
                                <div class="col-md-3 text-center">
                                    <div class="fw-bold"><?php echo substr($train['arrival_time'], 0, 5); ?></div>
                                    <div class="text-muted small"><?php echo $train['to_station']; ?></div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="fw-bold text-primary">LKR <?php echo number_format($train['fare'], 2); ?></div>
                                    <div class="text-muted small"><?php echo $available_seats; ?> seats left</div>
                                    <?php if ($available_seats >= (int)$_POST['num_passengers']): ?>
                                        <a href="book.php?schedule_id=<?php echo $train['schedule_id']; ?>&date=<?php echo $_POST['travel_date']; ?>&passengers=<?php echo $_POST['num_passengers']; ?>"
                                           class="btn btn-success btn-sm mt-1">Book Now</a>
                                    <?php else: ?>
                                        <span class="badge bg-danger mt-1">Full</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">No trains found for this route and date.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>