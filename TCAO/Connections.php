<?php
$Connections = mysqli_connect("localhost:3306", "root", "", "log2");

// Check connection
if (!$Connections) {
    die("Connection failed: " . mysqli_connect_error());
}
?>