<?php
require_once '../includes/auth.php';
require_passenger();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$passenger = $conn->query("SELECT * FROM passengers WHERE user_id = $user_id")->fetch_assoc();

// Get parameters
$schedule_id = (int)$_GET['schedule_id'];
$travel_date = $_GET['date'];
$num_passengers = (int)$_GET['passengers'];

// Fetch schedule details
$schedule = $conn->query("
    SELECT sc.*, t.train_name, t.train_number, t.total_seats, t.train_type,
           r.route_name,
           s1.station_name AS from_station,
           s2.station_name AS to_station
    FROM schedules sc
    JOIN trains t ON sc.train_id = t.train_id
    JOIN routes r ON sc.route_id = r.route_id
    JOIN stations s1 ON r.start_station_id = s1.station_id
    JOIN stations s2 ON r.end_station_id = s2.station_id
    WHERE sc.schedule_id = $schedule_id
")->fetch_assoc();

if (!$schedule) {
    header("Location: search.php");
    exit();
}

// Get already booked seats for this schedule and date
$booked_seats_result = $conn->query("
    SELECT se.seat_number
    FROM seats se
    JOIN bookings b ON se.booking_id = b.booking_id
    WHERE b.schedule_id = $schedule_id
    AND b.travel_date = '$travel_date'
    AND b.status != 'cancelled'
");
$booked_seats = [];
while ($row = $booked_seats_result->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}

$total_fare = $schedule['fare'] * $num_passengers;
$success = '';
$error = '';

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $selected_seats = $_POST['seats'] ?? [];

    if (count($selected_seats) != $num_passengers) {
        $error = "Please select exactly $num_passengers seat(s).";
    } else {
        // Generate unique booking number
        $booking_number = 'BK' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (booking_number, passenger_id, schedule_id, travel_date, num_passengers, total_fare, status) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
        $stmt->bind_param("siisid", $booking_number, $passenger['passenger_id'], $schedule_id, $travel_date, $num_passengers, $total_fare);

        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;

            // Insert seats
            foreach ($selected_seats as $seat) {
                $seat = $conn->real_escape_string($seat);
                $conn->query("INSERT INTO seats (booking_id, seat_number) VALUES ($booking_id, '$seat')");
            }

            $success = $booking_number;
        } else {
            $error = "Booking failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket | Railway System</title>
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
        .seat {
            width: 45px;
            height: 45px;
            border: 2px solid #1a3c5e;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: bold;
            background-color: #e8f4f8;
            transition: all 0.2s;
        }
        .seat.booked {
            background-color: #e74c3c;
            border-color: #e74c3c;
            color: white;
            cursor: not-allowed;
        }
        .seat.selected {
            background-color: #27ae60;
            border-color: #27ae60;
            color: white;
        }
        .seat:hover:not(.booked) {
            background-color: #1a3c5e;
            color: white;
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
            <h4 class="mb-4">Book Your Ticket</h4>

            <?php if ($success): ?>
                <!-- Booking Confirmation -->
                <div class="card p-4 text-center">
                    <div class="text-success fs-1 mb-3">✓</div>
                    <h4 class="text-success">Booking Confirmed!</h4>
                    <p class="text-muted">Your booking number is:</p>
                    <h3 class="fw-bold"><?php echo $success; ?></h3>
                    <hr>
                    <div class="row text-start">
                        <div class="col-md-6 mx-auto">
                            <table class="table">
                                <tr><td class="text-muted">Train</td><td><strong><?php echo $schedule['train_name']; ?></strong></td></tr>
                                <tr><td class="text-muted">From</td><td><?php echo $schedule['from_station']; ?></td></tr>
                                <tr><td class="text-muted">To</td><td><?php echo $schedule['to_station']; ?></td></tr>
                                <tr><td class="text-muted">Date</td><td><?php echo $travel_date; ?></td></tr>
                                <tr><td class="text-muted">Departure</td><td><?php echo substr($schedule['departure_time'], 0, 5); ?></td></tr>
                                <tr><td class="text-muted">Passengers</td><td><?php echo $num_passengers; ?></td></tr>
                                <tr><td class="text-muted">Total Fare</td><td><strong>LKR <?php echo number_format($total_fare, 2); ?></strong></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="bookings.php" class="btn btn-primary">View My Bookings</a>
                        <a href="search.php" class="btn btn-outline-secondary">Book Another</a>
                    </div>
                </div>

            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Trip Summary -->
                <div class="card p-4 mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-muted small">Train</div>
                            <div class="fw-bold"><?php echo $schedule['train_name']; ?></div>
                            <div class="text-muted small"><?php echo $schedule['train_number']; ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Route</div>
                            <div class="fw-bold"><?php echo $schedule['from_station']; ?> → <?php echo $schedule['to_station']; ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">Date</div>
                            <div class="fw-bold"><?php echo $travel_date; ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">Departure</div>
                            <div class="fw-bold"><?php echo substr($schedule['departure_time'], 0, 5); ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">Total Fare</div>
                            <div class="fw-bold text-primary">LKR <?php echo number_format($total_fare, 2); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Seat Selection -->
                <div class="card p-4 mb-4">
                    <h6 class="mb-3">Select <?php echo $num_passengers; ?> <?php echo $num_passengers == 1 ? 'Seat' : 'Seats'; ?></h6>
                    <div class="d-flex gap-4 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="seat" style="width:25px;height:25px;min-width:25px;"></div>
                            <span>Available</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="seat booked" style="width:25px;height:25px;min-width:25px;"></div>
                            <span>Booked</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="seat selected" style="width:25px;height:25px;min-width:25px;"></div>
                            <span>Selected</span>
                        </div>
                    </div>

                    <form method="POST" id="bookingForm">
                        <div id="seatMap" class="d-flex flex-column align-items-center">
                            <?php
                            $total_seats = $schedule['total_seats'];
                            $rows = ceil($total_seats / 4);
                            for ($i = 1; $i <= $rows; $i++):
                                $letters = ['A', 'B', 'C', 'D'];
                                echo '<div class="d-flex align-items-center mb-1">';
                                echo '<span class="text-muted me-2" style="width:20px;font-size:0.8rem;">' . $i . '</span>';
                                foreach ($letters as $j => $letter):
                                    $seat_num = $i . $letter;
                                    $seat_index = ($i - 1) * 4 + ($j + 1);
                                    if ($seat_index > $total_seats) break;
                                    $is_booked = in_array($seat_num, $booked_seats);
                                    $class = $is_booked ? 'seat booked' : 'seat';
                                    if ($j == 2) echo '<span style="width:20px;"></span>';
                                    echo '<div class="' . $class . '" data-seat="' . $seat_num . '" onclick="' . ($is_booked ? '' : 'selectSeat(this)') . '">' . $seat_num . '</div>';
                                endforeach;
                                echo '</div>';
                            endfor;
                            ?>
                        </div>
                        <div id="selectedSeatsContainer"></div>
                        <div class="mt-3">
                            <span class="text-muted">Selected: </span>
                            <span id="selectedDisplay" class="fw-bold">None</span>
                        </div>
                        <button type="submit" name="confirm_booking" class="btn btn-success mt-3">
                            Confirm Booking
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const maxSeats = <?php echo $num_passengers; ?>;
    let selectedSeats = [];

    function selectSeat(el) {
        const seat = el.dataset.seat;
        if (el.classList.contains('selected')) {
            el.classList.remove('selected');
            selectedSeats = selectedSeats.filter(s => s !== seat);
        } else {
            if (selectedSeats.length >= maxSeats) {
                alert('You can only select ' + maxSeats + ' seat(s).');
                return;
            }
            el.classList.add('selected');
            selectedSeats.push(seat);
        }
        updateDisplay();
    }

    function updateDisplay() {
        document.getElementById('selectedDisplay').textContent =
            selectedSeats.length > 0 ? selectedSeats.join(', ') : 'None';

        const container = document.getElementById('selectedSeatsContainer');
        container.innerHTML = '';
        selectedSeats.forEach(seat => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'seats[]';
            input.value = seat;
            container.appendChild(input);
        });
    }
</script>
</body>
</html>