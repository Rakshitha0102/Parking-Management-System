<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

date_default_timezone_set("Asia/Kolkata");

$message = "";

$available_slots = mysqli_query($conn, "SELECT * FROM parking_slots WHERE status = 'Available' AND is_active = 1");


if (mysqli_num_rows($available_slots) == 0) {
    $soonest = mysqli_query($conn, "
        SELECT estimated_exit_time 
        FROM vehicles 
        WHERE exit_time IS NULL 
        ORDER BY estimated_exit_time ASC 
        LIMIT 1
    ");

    if (mysqli_num_rows($soonest) > 0) {
        $row = mysqli_fetch_assoc($soonest);
        $next_available = new DateTime($row['estimated_exit_time']);
        $now = new DateTime();
        $interval = $now->diff($next_available);
        $wait_minutes = ($interval->h * 60) + $interval->i;

        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Vehicle Entry</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body {
                    background: url('images/car_login.jpg') no-repeat center center fixed;
                    background-size: cover;
                    color: white;
                    font-family: 'Segoe UI', sans-serif;
                }
                .container {
                    background-color: rgba(0,0,0,0.6);
                    padding: 30px;
                    margin-top: 100px;
                    border-radius: 16px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.5);
                }
                a.btn {
                    margin-top: 15px;
                }
            </style>
        </head>
        <body>
        <div class='container text-center'>
            <h3>🚫 All Parking Slots Full</h3>
            <div class='alert alert-warning mt-4'>
                Next available slot in: <strong>$wait_minutes minute(s)</strong>.<br>
                Please wait or try again later.
            </div>
            <a href='dashboard.php' class='btn btn-outline-light'>⬅️ Back to Dashboard</a>
        </div>
        </body>
        </html>";
        exit(); 
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_number = strtoupper(trim($_POST['vehicle_number']));
    $mobile_number = trim($_POST['mobile_number']);
    $slot_id = $_POST['slot_id'];
    $expected_minutes = intval($_POST['expected_minutes']);
    $entry_time = date('Y-m-d H:i:s');
    $current_time = $entry_time;  
    $estimated_exit_time = date('Y-m-d H:i:s', strtotime("+$expected_minutes minutes"));

    $insert = mysqli_query($conn, 
        "INSERT INTO vehicles (vehicle_number, mobile_number, slot_id, entry_time, expected_minutes, estimated_exit_time)
         VALUES ('$vehicle_number', '$mobile_number', '$slot_id', '$entry_time', $expected_minutes, '$estimated_exit_time')"
    );

    $update = mysqli_query($conn, "UPDATE parking_slots SET status = 'Occupied' WHERE slot_id = $slot_id");

    if ($insert && $update) {
        $message = "<div class='alert alert-success'>
            ✅ Vehicle parked successfully!<br>
            Current time: <strong>$current_time</strong><br>
            Estimated exit at: <strong>$estimated_exit_time</strong>
        </div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Error parking the vehicle. Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Entry - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/car_login.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }

        .entry-box {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            margin-top: 80px;
            border-radius: 16px;
            color: white;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
        }

        .form-control, .form-select {
            background-color: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }

        .form-control::placeholder {
            color: #ccc;
        }

        .btn-primary {
            font-weight: bold;
            background-color: #0d6efd;
            border: none;
        }

        .btn-primary:hover {
            background-color: #084298;
        }

        label {
            font-weight: 500;
        }

        .navbar {
            backdrop-filter: blur(8px);
        }

        .form-select option {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">🚗 Vehicle Entry</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">⬅️ Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="entry-box">
        <h4>📋 Enter Vehicle Details</h4>

        <?= $message ?>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label class="form-label">Vehicle Number</label>
                <input type="text" name="vehicle_number" class="form-control" placeholder="e.g., TN01AB1234" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mobile Number</label>
                <input type="tel" name="mobile_number" class="form-control" placeholder="e.g., 9876543210" pattern="[0-9]{10}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Expected Duration (in minutes)</label>
                <input type="number" name="expected_minutes" class="form-control" min="10" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Parking Slot</label>
                <select name="slot_id" class="form-select" required>
                    <option value="" disabled selected>-- Choose Available Slot --</option>
                    <?php
                    mysqli_data_seek($available_slots, 0);
                    while ($slot = mysqli_fetch_assoc($available_slots)) {
                        echo "<option value='{$slot['slot_id']}'>{$slot['slot_number']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">✅ Park Vehicle</button>
        </form>
    </div>
</div>

<script>
function checkOverdue() {
    fetch('check_overdue.php')
        .then(response => response.json())
        .then(data => {
            if (data.overdue) {
                showPopup("⚠️ Some vehicles have exceeded their expected parking time!");
            }
        });
}
setInterval(checkOverdue, 30000);
function showPopup(message) {
    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-danger position-fixed top-0 end-0 m-3 shadow";
    alertDiv.style.zIndex = "1050";
    alertDiv.innerHTML = `
        <strong>${message}</strong>
        <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(alertDiv);
}
</script>

</body>
</html>
