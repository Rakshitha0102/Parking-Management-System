<?php
include 'db.php';
date_default_timezone_set("Asia/Kolkata");

$now = new DateTime();
$alert_needed = false;

$query = "SELECT estimated_exit_time FROM vehicles WHERE exit_time IS NULL";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    if (!empty($row['estimated_exit_time'])) {
        $estimated = new DateTime($row['estimated_exit_time']);
        if ($estimated < $now) {
            $alert_needed = true;
            break;
        }
    }
}

echo json_encode(['overdue' => $alert_needed]);
?>
