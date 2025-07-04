<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$total_slots = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM parking_slots WHERE is_active = 1"));

$occupied_slots = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM parking_slots WHERE status='Occupied' AND is_active = 1"));

$available_slots = $total_slots - $occupied_slots;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
    margin: 0;
    padding: 0;
    background: url('images/car_login.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', sans-serif;
}

.container {
    background-color: rgba(255, 255, 255, 0.05); /* Transparent background for effect */
    backdrop-filter: blur(5px);
    border-radius: 20px;
    padding: 30px;
    margin-top: 30px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

.card {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    border-radius: 20px;
    color: #fff;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: scale(1.05);
}

.card h5 {
    font-size: 1.3rem;
    color: #f1f1f1;
}

.dashboard-icons {
    width: 48px;
    margin-bottom: 10px;
}

.navbar {
    backdrop-filter: blur(10px);
    background-color: rgba(0, 0, 0, 0.7);
}

.btn-outline-light:hover {
    background-color: white;
    color: black;
}

.action-buttons {
    background-color: rgba(255, 255, 255, 0.1); 
    display: inline-block;
    padding: 20px;
    border-radius: 16px;
    backdrop-filter: blur(5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.action-buttons .btn {
    margin: 10px;
    font-weight: bold;
    color: #fff;
    border: 2px solid #fff;
    backdrop-filter: blur(3px);
}

.action-buttons .btn:hover {
    background-color: #ffffff;
    color: #000;
    transition: 0.3s ease;
}


    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">🚘 Parking Management - Admin Dashboard</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5 shadow-lg p-4 rounded">

    <div class="text-center mb-4">
    

    <span class="px-4 py-2 bg-dark text-white rounded shadow fw-bold fs-3 d-inline-block">
        📊 Dashboard Overview
    </span>
</div>


    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 p-3 text-center">
                <img src="https://img.icons8.com/fluency/48/parking.png" class="dashboard-icons" />
                <div class="card-body">
                    <h5>Total Parking Slots</h5>
                    <h2><?= $total_slots ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 p-3 text-center">
                <img src="https://img.icons8.com/color/48/available-updates.png" class="dashboard-icons" />
                <div class="card-body">
                    <h5>Available Slots</h5>
                    <h2><?= $available_slots ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3 p-3 text-center">
                <img src="https://img.icons8.com/fluency/48/traffic-jam.png" class="dashboard-icons" />
                <div class="card-body">
                    <h5>Occupied Slots</h5>
                    <h2><?= $occupied_slots ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <div class="action-buttons">
            <a href="entry.php" class="btn btn-outline-primary btn-lg">🚗 Vehicle Entry</a>
            <a href="exit.php" class="btn btn-outline-warning btn-lg">🏁 Vehicle Exit</a>
            <a href="report.php" class="btn btn-outline-success btn-lg">📄 Daily Report</a>
            <a href="manage_slots.php" class="btn btn-outline-info btn-lg">🛠️ Manage Slots</a>
        </div>
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
