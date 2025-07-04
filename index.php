<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username='$user'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['admin'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parking Management - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
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

        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 1; 
        }

        .card {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            color: white;
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
        }

        .form-control {
            background-color: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }

        .form-control::placeholder {
            color: #ccc;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #084298;
        }

        .card-title {
            font-weight: bold;
            text-align: center;
        }

        .login-icon {
            display: block;
            margin: 0 auto 15px;
            width: 60px;
        }

        .alert {
            background-color: rgba(220, 53, 69, 0.85);
            color: white;
            border: none;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card shadow-lg">
        <img src="https://img.icons8.com/fluency/96/admin-settings-male.png" class="login-icon" />
        <h4 class="card-title mb-4">Admin Login</h4>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Enter username" required>
            </div>

            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">🔐 Login</button>
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
