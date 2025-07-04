<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
date_default_timezone_set("Asia/Kolkata");

$query = "SELECT v.*, p.slot_number 
          FROM vehicles v 
          JOIN parking_slots p ON v.slot_id = p.slot_id 
          ORDER BY v.entry_time DESC";

$result = mysqli_query($conn, $query);

$overdue_exists = false;
$now = new DateTime();
$vehicle_rows = [];

while ($row = mysqli_fetch_assoc($result)) {
    $isOverdue = false;
    if (is_null($row['exit_time'])) {
        $estimated = new DateTime($row['estimated_exit_time']);
        if ($estimated < $now) {
            $isOverdue = true;
            $overdue_exists = true;
        }
    }
    $row['isOverdue'] = $isOverdue;
    $vehicle_rows[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parking Report - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/car_login.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 100vw;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            padding: 30px;
            margin-top: 50px;
            border-radius: 16px;
            color: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .navbar {
            backdrop-filter: blur(10px);
        }

        .table {
            color: white;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .blink {
            animation: blink-animation 1s steps(2, start) infinite;
        }

        @keyframes blink-animation {
            to {
                visibility: hidden;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>


<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">📄 Parking Report</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">⬅️ Dashboard</a>
    </div>
</nav>

<?php if ($overdue_exists): ?>
<script>
const normalTitle = document.title;
const alertTitle = "⚠️ Overdue Vehicles!";
let blink = true;

setInterval(() => {
    document.title = blink ? alertTitle : normalTitle;
    blink = !blink;
}, 1000);
</script>
<?php endif; ?>

<div class="container">
    <h4 class="mb-4 text-center">🕓 Vehicle Entry & Exit Report</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-primary text-white text-center">

                <tr>
                    <th>🚘 Vehicle</th>
                    <th>📱 Mobile</th>
                    <th>🔢 Slot</th>
                    <th>🕒 Entry Time</th>
                    <th>🏁 Exit Time</th>
                    <th>💰 Charge</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($vehicle_rows) > 0): ?>
                    <?php foreach ($vehicle_rows as $row): ?>
                        <tr class="text-center <?= $row['isOverdue'] ? 'table-danger' : '' ?>">
                            <td><?= htmlspecialchars($row['vehicle_number']) ?></td>
                            <td>
                                <?= $row['isOverdue'] 
                                    ? "<span class='text-warning fw-bold'>{$row['mobile_number']}</span>" 
                                    : htmlspecialchars($row['mobile_number']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['slot_number']) ?></td>
                            <td><?= $row['entry_time'] ?></td>
                            <td>
                                <?php
                                if ($row['exit_time']) {
                                    echo "<span class='badge bg-success'>{$row['exit_time']}</span>";
                                } elseif ($row['isOverdue']) {
                                    echo "<span class='badge bg-danger blink'>⏰ Overdue</span>";
                                } else {
                                    echo "<span class='text-warning'>Still Parked</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <?= $row['charge'] 
                                    ? "<span class='badge bg-light text-dark'>₹{$row['charge']}</span>" 
                                    : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-light">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
