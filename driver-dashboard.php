<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'driver') {
    header("Location: login.php");
    exit();
}

include 'Connections.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Fleet Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Driver Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['email']; ?> | <a href="logout.php" style="color: white;">Logout</a></p>
    </div>
    
    <div class="dashboard-container">
        <div class="card">
            <h2>Current Assignments</h2>
            <p>View your current driving assignments and schedules.</p>
        </div>
        <div class="card">
            <h2>Vehicle Information</h2>
            <p>Access details about your assigned vehicle.</p>
        </div>
        <div class="card">
            <h2>Trip History</h2>
            <p>Review your completed trips and performance metrics.</p>
        </div>
    </div>
</body>
</html>