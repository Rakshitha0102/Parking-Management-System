<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Parking Slots - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background: url('images/car_login.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
    }

    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0;
        height: 100%;
        width: 100%;
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

    h4 {
        font-weight: bold;
    }

    .table {
        background: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .table thead th {
        background-color: #007bff; 
        color: white;
    }

    .table tbody tr {
        background-color:rgb(145, 176, 209); 
    }

    .table tbody tr:nth-child(even) {
        background-color:rgb(158, 183, 209); 
    }

    .table tbody tr:hover {
        background-color: rgba(157, 134, 226, 0.2); 
    }

    .badge-success {
        background-color: #28a745 !important;
    }

    .badge-danger {
        background-color: #dc3545 !important;
    }

    .btn-warning:hover,
    .btn-danger:hover {
        opacity: 0.85;
    }

    .alert {
        margin-top: 15px;
        font-size: 1rem;
    }

    a.btn-primary.btn-sm {
        font-weight: bold;
    }
</style>

    
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">🛠️ Manage Parking Slots</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">⬅️ Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📋 All Parking Slots</h4>
        <a href="add_slot.php" class="btn btn-primary btn-sm"> Add New Slot</a>
    </div>

    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }

    $result = mysqli_query($conn, "SELECT * FROM parking_slots WHERE is_active = 1 ORDER BY slot_number ASC");

    if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>🚗 Slot Number</th>
                        <th>📶 Status</th>
                        <th>⚙️ Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($slot = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($slot['slot_number']) ?></td>
                        <td>
                            <span class="badge bg-<?= $slot['status'] === 'Available' ? 'success' : 'danger' ?>">
                                <?= $slot['status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_slot.php?id=<?= $slot['slot_id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                            <a href="delete_slot.php?id=<?= $slot['slot_id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this slot?');">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">ℹ️ No slots added yet.</div>
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
