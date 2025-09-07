<?php
// Connections.php
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "MFdb"; // Changed from TCA to MFdb

// Create connection
$Connections = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$Connections) {
    die("Connection failed: " . mysqli_connect_error());
}
?>