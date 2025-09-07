<?php
// Include database connection
require_once 'Connections.php';

// Fetch dashboard statistics
$stats = [
    'total_vehicles' => 0,
    'online_vehicles' => 0,
    'active_messages' => 0,
    'recent_actions' => 0
];

// Total vehicles
$sql = "SELECT COUNT(*) as count FROM vehicles";
$result = mysqli_query($Connections, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_vehicles'] = $row['count'];
}

// Online vehicles
$sql = "SELECT COUNT(*) as count FROM vehicles WHERE status = 'online'";
$result = mysqli_query($Connections, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['online_vehicles'] = $row['count'];
}

// Recent messages (last 24 hours)
$sql = "SELECT COUNT(*) as count FROM messages WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
$result = mysqli_query($Connections, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['active_messages'] = $row['count'];
}

// Recent communication actions
$sql = "SELECT COUNT(*) as count FROM communication_actions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
$result = mysqli_query($Connections, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['recent_actions'] = $row['count'];
}

// Fetch recent messages
$recent_messages = [];
$sql = "SELECT m.*, v.vehicle_name, v.identifier, u.first_name, u.last_name 
        FROM messages m 
        LEFT JOIN vehicles v ON m.receiver_id = v.id AND m.receiver_type = 'vehicle'
        JOIN users u ON m.sender_id = u.id 
        ORDER BY m.sent_at DESC 
        LIMIT 5";
$result = mysqli_query($Connections, $sql);
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $recent_messages[] = $row;
    }
}

// Fetch vehicle status
$vehicles = [];
$sql = "SELECT * FROM vehicles ORDER BY status DESC, vehicle_name ASC";
$result = mysqli_query($Connections, $sql);
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $vehicles[] = $row;
    }
}

// Fetch recent communication actions
$recent_actions = [];
$sql = "SELECT ca.*, u.username 
        FROM communication_actions ca 
        JOIN users u ON ca.created_by = u.id 
        ORDER BY ca.created_at DESC 
        LIMIT 5";
$result = mysqli_query($Connections, $sql);
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $recent_actions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleetCom - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #1d1d1d;
            --success: #2ecc71;
            --warning: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            margin: 0;
        }
        
        /* --- Sidebar --- */
        .sidebar {
            width: 220px;
            background: var(--dark);
            color: white;
            height: 100vh;
            padding: 25px 0;
            position: fixed;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo img {
            width: 160px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar ul li:hover {
            background: #333;
            transform: translateX(5px);
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            transition: color 0.3s ease;
        }

        .sidebar ul li:hover a {
            color: #a875ff;
        }

        .sidebar ul li.active {
            background: #7a3ff2;
        }

        .bottom-links {
            display: flex;
            flex-direction: column;
            margin-top: 100px;
        }

        .bottom-links a {
            color: white;
            text-decoration: none;
            margin: 5px 0;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .bottom-links a:hover {
            color: #a875ff;
            background: #333;
            transform: translateX(5px);
        }
        
        /* --- Main Content --- */
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
            min-height: 100vh;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }
        
        header {
            background: #9a66ff;
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            background-color: #4cd964;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: #2c3e50;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            color: #2c3e50;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(155, 102, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 20px;
            color: #9a66ff;
        }
        
        .action-label {
            font-weight: 600;
            text-align: center;
        }
        
        /* Dashboard Sections */
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        @media (min-width: 992px) {
            .dashboard-sections {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .view-all {
            color: #9a66ff;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        /* Vehicle List */
        .vehicle-list {
            list-style: none;
        }
        
        .vehicle-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: background-color 0.2s;
        }
        
        .vehicle-item:hover {
            background-color: #f8f9fa;
        }
        
        .vehicle-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 12px;
        }
        
        .status-online {
            background-color: #4cd964;
        }
        
        .status-offline {
            background-color: #e74c3c;
        }
        
        .vehicle-info {
            flex: 1;
        }
        
        .vehicle-name {
            font-weight: 600;
            color: #2d3436;
        }
        
        .vehicle-details {
            font-size: 0.8rem;
            color: #636e72;
        }
        
        /* Message List */
        .message-list {
            list-style: none;
        }
        
        .message-item {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #9a66ff;
        }
        
        .message-content {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .message-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #636e72;
        }
        
        /* Action List */
        .action-list {
            list-style: none;
        }
        
        .action-item {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        
        .action-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .action-name {
            font-weight: 600;
            color: #2d3436;
        }
        
        .action-type {
            font-size: 0.8rem;
            background-color: #e3f2fd;
            color: #1a73e8;
            padding: 2px 8px;
            border-radius: 12px;
        }
        
        .action-content {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .action-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #636e72;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="viahale1.png" alt="Viahale Logo">
        </div>
        <ul>
            <li class="active"><a href="dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
            <li><a href="RT.php"><i class="fas fa-user me-2"></i> RT Communication</a></li>
            <li><a href="CC.php"><i class="fas fa-check-circle me-2"></i> Com. Center Access</a></li>
        </ul>
        <div class="bottom-links">
            <a href="#"><i class="fas fa-user me-2"></i> Account</a>
            <a href="../admin-dashboard.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-tachometer-alt"></i> FleetCom Dashboard</h1>
                    <div class="status-indicator">
                        <div class="status-dot"></div>
                        <span>System Operational</span>
                    </div>
                </div>
            </header>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(155, 102, 255, 0.1); color: #9a66ff;">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_vehicles']; ?></div>
                        <div class="stat-label">Total Vehicles</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">
                        <i class="fas fa-signal"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['online_vehicles']; ?></div>
                        <div class="stat-label">Online Vehicles</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['active_messages']; ?></div>
                        <div class="stat-label">Messages Today</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(243, 156, 18, 0.1); color: #f39c12;">
                        <i class="fas fa-broadcast-tower"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['recent_actions']; ?></div>
                        <div class="stat-label">Recent Actions</div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions - Moved to upper section -->
            <div class="quick-actions">
                <a href="RT.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="action-label">Real-Time Communication</div>
                </a>
                
                <a href="CC.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-broadcast-tower"></i>
                    </div>
                    <div class="action-label">Communication Center</div>
                </a>
                
                <a href="#" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="action-label">Route Planning</div>
                </a>
                
                <a href="#" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-label">Reports & Analytics</div>
                </a>
            </div>
            
            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Vehicles Status -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2 class="card-title">Fleet Status</h2>
                        <a href="RT.php" class="view-all">View All</a>
                    </div>
                    <ul class="vehicle-list">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <li class="vehicle-item">
                            <div class="vehicle-status <?php echo $vehicle['status'] == 'online' ? 'status-online' : 'status-offline'; ?>"></div>
                            <div class="vehicle-info">
                                <div class="vehicle-name"><?php echo $vehicle['vehicle_name'] . ' ' . $vehicle['identifier']; ?></div>
                                <div class="vehicle-details">
                                    <?php echo ucfirst($vehicle['status']) . ' - ' . ucfirst(str_replace('_', ' ', $vehicle['operational_status'])); ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Recent Messages -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Messages</h2>
                        <a href="RT.php" class="view-all">View All</a>
                    </div>
                    <ul class="message-list">
                        <?php if (empty($recent_messages)): ?>
                            <li style="text-align: center; padding: 20px; color: #777;">
                                No recent messages.
                            </li>
                        <?php else: ?>
                            <?php foreach ($recent_messages as $message): ?>
                            <li class="message-item">
                                <div class="message-content">
                                    <?php echo $message['content']; ?>
                                </div>
                                <div class="message-meta">
                                    <span>
                                        <?php 
                                        if ($message['receiver_type'] == 'vehicle' && !empty($message['vehicle_name'])) {
                                            echo 'To: ' . $message['vehicle_name'];
                                        } else {
                                            echo 'From: ' . $message['first_name'] . ' ' . $message['last_name'];
                                        }
                                        ?>
                                    </span>
                                    <span><?php echo date('M j, h:i A', strtotime($message['sent_at'])); ?></span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

    <script>
        // Auto refresh dashboard every 60 seconds
        setInterval(function() {
            // You could implement a more efficient partial refresh with AJAX
            window.location.reload();
        }, 60000);
    </script>
</body>
</html>