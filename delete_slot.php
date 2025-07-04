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
$check = mysqli_query($conn, "SELECT status FROM parking_slots WHERE slot_id = $slot_id");
$row = mysqli_fetch_assoc($check);

if (!$row) {
    $_SESSION['message'] = "<div class='alert alert-danger'>Slot not found.</div>";
} elseif ($row['status'] === 'Occupied') {
    $_SESSION['message'] = "<div class='alert alert-warning'>❌ Cannot delete an occupied slot!</div>";
} else {
    $disable = mysqli_query($conn, "UPDATE parking_slots SET is_active = 0 WHERE slot_id = $slot_id");
    if ($disable) {
        $_SESSION['message'] = "<div class='alert alert-success'>✅ Slot removed from active list (soft-deleted).</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>❌ Error removing slot.</div>";
    }
}

header("Location: manage_slots.php");
exit();
?>
