<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: manage_slots.php");
    exit();
}

$slot_id = $_GET['id'];
$message = "";

$result = mysqli_query($conn, "SELECT * FROM parking_slots WHERE slot_id = $slot_id");
$slot = mysqli_fetch_assoc($result);

if (!$slot) {
    $message = "<div class='alert alert-danger'>Slot not found.</div>";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slot_number = strtoupper(trim($_POST['slot_number']));

    $check = mysqli_query($conn, "SELECT * FROM parking_slots WHERE slot_number = '$slot_number' AND slot_id != $slot_id");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-warning'>This slot number already exists!</div>";
    } else {
        $update = mysqli_query($conn, "UPDATE parking_slots SET slot_number = '$slot_number' WHERE slot_id = $slot_id");
        if ($update) {
            $message = "<div class='alert alert-success'>Slot updated successfully.</div>";
            $slot['slot_number'] = $slot_number;
        } else {
            $message = "<div class='alert alert-danger'>Error updating slot.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Parking Slot - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Edit Parking Slot</span>
        <a href="manage_slots.php" class="btn btn-outline-light btn-sm">Back</a>
    </div>
</nav>
<div class="container mt-4">
    <h4>Edit Slot Number</h4>

    <?= $message ?>
    <?php if ($slot): ?>
    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Slot Number</label>
            <input type="text" name="slot_number" class="form-control" value="<?= $slot['slot_number'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Slot</button>
    </form>
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
