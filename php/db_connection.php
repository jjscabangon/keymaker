<?php
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "newkeymaker_db";  


$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


mysqli_set_charset($conn, "utf8");

return $conn;
?>
