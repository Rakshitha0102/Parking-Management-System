<?php
$host = "sql12.freesqldatabase.com";       
$user = "sql12788372";              
$password = "aTEa8yPPdA";              
$database = "sql12788372"; 


$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

