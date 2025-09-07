<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics 2 - Dashboard</title>
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
        
        /* Additional Stats Section */
        .additional-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .stats-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .stats-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .stats-list {
            list-style: none;
        }
        
        .stats-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .stats-item:last-child {
            border-bottom: none;
        }
        
        .stats-item-name {
            color: #636e72;
        }
        
        .stats-item-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .trend-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .trend-up {
            color: #2ecc71;
        }
        
        .trend-down {
            color: #e74c3c;
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
            
            .stats-grid, .additional-stats {
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
            <img src="viahale1.png">
        </div>
        <ul>
            <li class="active"><a href="admin-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
            <li><a href="FVM/fvm-dashboard.php"><i class="fas fa-user me-2"></i> FVM </a></li>
            <li><a href="vrds/vrds-dashboard.php"><i class="fas fa-solid fa-car"></i> VRDS </a></li>
            <li><a href="dtpm/dtpm-dashboard.php"><i class="fas fa-solid fa-gear"></i> DTPM </a></li>
            <li><a href="tcao/tcao-dashboard.php"><i class="fas fa-solid fa-money-bill"></i> TCAO </a></li>
            <li><a href=""><i class="fas fa-solid fa-mobile"></i> MFA </a></li>
        </ul>
        <div class="bottom-links">
            <a href="#"><i class="fas fa-user me-2"></i> Account</a>
            <a href="login.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
        </div>  
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-tachometer-alt"></i> Fleet Dashboard</h1>
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
                        <div class="stat-value">24</div>
                        <div class="stat-label">Total Vehicles</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">
                        <i class="fas fa-signal"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">18</div>
                        <div class="stat-label">Online Vehicles</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">42</div>
                        <div class="stat-label">Messages Today</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(243, 156, 18, 0.1); color: #f39c12;">
                        <i class="fas fa-broadcast-tower"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">15</div>
                        <div class="stat-label">Recent Actions</div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Stats Section -->
            <div class="additional-stats">
                <div class="stats-card">
                    <div class="stats-header">
                        <h2 class="stats-title">Vehicle Statistics</h2>
                    </div>
                    <ul class="stats-list">
                        <li class="stats-item">
                            <span class="stats-item-name">Active Vehicles</span>
                            <span class="stats-item-value">18 <span class="trend-indicator trend-up">+2%</span></span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">In Maintenance</span>
                            <span class="stats-item-value">4 <span class="trend-indicator trend-down">-1%</span></span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">Idle Vehicles</span>
                            <span class="stats-item-value">2</span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">Average Utilization</span>
                            <span class="stats-item-value">78% <span class="trend-indicator trend-up">+5%</span></span>
                        </li>
                    </ul>
                </div>
                
                <div class="stats-card">
                    <div class="stats-header">
                        <h2 class="stats-title">Communication Stats</h2>
                    </div>
                    <ul class="stats-list">
                        <li class="stats-item">
                            <span class="stats-item-name">Messages Sent</span>
                            <span class="stats-item-value">42 <span class="trend-indicator trend-up">+12%</span></span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">Messages Received</span>
                            <span class="stats-item-value">38</span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">Response Rate</span>
                            <span class="stats-item-value">92% <span class="trend-indicator trend-up">+3%</span></span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">Avg. Response Time</span>
                            <span class="stats-item-value">4.2min <span class="trend-indicator trend-down">-0.8min</span></span>
                        </li>
                    </ul>
                </div>
                
                <div class="stats-card">
                    <div class="stats-header">
                        <h2 class="stats-title">Performance Metrics</h2>
                    </div>
                    <ul class="stats-list">
                        <li class="stats-item">
                            <span class="stats-item-name">Fuel Efficiency</span>
                            <span class="stats-item-value">8.2 mpg <span class="trend-indicator trend-up">+0.3%</span></span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">On-time Delivery</span>
                            <span class="stats-item-value">94% <span class="trend-indicator trend-up">+2%</span></span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">Route Compliance</span>
                            <span class="stats-item-value">89%</span>
                        </li>
                        <li class="stats-item">
                            <span class="stats-item-name">System Uptime</span>
                            <span class="stats-item-value">99.8%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto refresh dashboard every 60 seconds
        setInterval(function() {
            // You could implement a more efficient partial refresh with AJAX
            // window.location.reload();
            console.log("Auto-refresh triggered");
        }, 60000);
        
        // Add active class to clicked sidebar items
        document.querySelectorAll('.sidebar ul li').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.sidebar ul li').forEach(li => {
                    li.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>