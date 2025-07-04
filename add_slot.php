<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

$message = "";
$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM parking_slots");
$total_slots = mysqli_fetch_assoc($count_result)['total'];

$slot_limit = 20;

if ($total_slots >= $slot_limit) {
    $message = "<div class='alert alert-warning'>🚫 You have reached the maximum limit of $slot_limit parking slots.</div>";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slot_number = strtoupper(trim($_POST['slot_number']));

    $check = mysqli_query($conn, "SELECT * FROM parking_slots WHERE slot_number='$slot_number'");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-warning'>⚠️ Slot number already exists!</div>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO parking_slots (slot_number) VALUES ('$slot_number')");
        if ($insert) {
            $message = "<div class='alert alert-success'>✅ Slot <strong>$slot_number</strong> added successfully.</div>";
            $total_slots++; 
        } else {
            $message = "<div class='alert alert-danger'>❌ Error adding slot. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Parking Slot - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/car_login.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            backdrop-filter: blur(4px);
        }

        .navbar {
            backdrop-filter: blur(6px);
        }

        .container {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            margin-top: 60px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        h4 {
            font-weight: bold;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-label {
            font-weight: 500;
        }

        .btn-primary {
            width: 100%;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background-color: #003366;
        }

        .alert {
            margin-top: 15px;
        }

        input::placeholder {
            font-style: italic;
        }

        @media (max-width: 576px) {
            .container {
                margin-top: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">➕ Add Parking Slot</span>
        <a href="manage_slots.php" class="btn btn-outline-light btn-sm">⬅️ Manage Slots</a>
    </div>
</nav>

<div class="container">
    <h4>🚘 Register New Parking Slot</h4>
    <?= $message ?>
    <?php if ($total_slots < $slot_limit): ?>
    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Slot Number</label>
            <input type="text" name="slot_number" class="form-control" placeholder="e.g., A1, B2" required>
        </div>

        <button type="submit" class="btn btn-primary">➕ Add Slot</button>
    </form>
    <?php else: ?>
        <div class="alert alert-info mt-4">Total slots used: <strong><?= $total_slots ?> / <?= $slot_limit ?></strong></div>
    <?php endif; ?>
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
