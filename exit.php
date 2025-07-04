<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
date_default_timezone_set("Asia/Kolkata");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_number = strtoupper(trim($_POST['vehicle_number']));
    $result = mysqli_query($conn, "SELECT * FROM vehicles WHERE vehicle_number='$vehicle_number' AND exit_time IS NULL");
    $vehicle = mysqli_fetch_assoc($result);

    if ($vehicle) {
    $entry_time = new DateTime($vehicle['entry_time']);
    $exit_time = new DateTime();
    $interval = $entry_time->diff($exit_time);

    $actual_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    $hours_decimal = $actual_minutes / 60;
    $expected_minutes = intval($vehicle['expected_minutes']);
    $extra_minutes = $actual_minutes - $expected_minutes;

    $rate = 20;
    $charge = round($rate * $hours_decimal, 2); 
    $penalty = 0;

    if ($extra_minutes > 0) {
        $penalty = ceil($extra_minutes / 15) * 10; 
        $charge += $penalty;
    }

    $slot_id = $vehicle['slot_id'];
    mysqli_query($conn, "UPDATE vehicles SET exit_time = NOW(), charge = $charge WHERE id = {$vehicle['id']}");

    mysqli_query($conn, "UPDATE parking_slots SET status = 'Available' WHERE slot_id = $slot_id");

    $duration_readable = $interval->format('%h hour(s) %i minute(s)');

    $message = "<div class='alert alert-success text-center'>
        ✅ Vehicle exited successfully.<br>
        Duration: <strong>$duration_readable</strong><br>
        Base Charge: ₹" . round($charge - $penalty) . "<br>";

    if ($extra_minutes > 0) {
        $message .= "<span class='text-warning'>⏰ Exceeded expected time by $extra_minutes minute(s).<br>Penalty: ₹$penalty</span><br>";
    }

    $message .= "<strong>Total Charge: ₹$charge</strong></div>";

    } else {
        $message = "<div class='alert alert-danger text-center'>🚫 No active parking record found for this vehicle.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Exit - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/car_login.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            height: 100vh; width: 100vw;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .exit-box {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            padding: 30px;
            margin-top: 80px;
            border-radius: 16px;
            color: white;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
            position: relative;
            z-index: 1;
        }

        .form-control {
            background-color: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }

        .form-control::placeholder {
            color: #ccc;
        }

        .btn-warning {
            font-weight: bold;
            width: 100%;
        }

        label {
            font-weight: 500;
        }

        .navbar {
            backdrop-filter: blur(8px);
        }

        .alert {
            margin-top: 15px;
            font-size: 1rem;
        }

        @media (max-width: 576px) {
            .exit-box {
                padding: 20px;
                margin-top: 60px;
            }
        }
    </style>
</head>
<body>


<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">🏁 Vehicle Exit</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">⬅️ Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="exit-box mt-5 mx-auto" style="max-width: 500px;">
        <h4 class="text-center mb-4">🔍 Process Vehicle Exit</h4>

        <?= $message ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Enter Vehicle Number</label>
                <input type="text" name="vehicle_number" class="form-control" placeholder="e.g., TN01AB1234" required>
            </div>

            <button type="submit" class="btn btn-warning">✅ Exit Vehicle</button>
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
