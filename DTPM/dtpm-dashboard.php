<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DTPM </title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .page-title {
      font-size: 24px;
      font-weight: 600;
      color: #2c3e50;
    }

    .refresh-btn {
      background-color: #9a66ff;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .refresh-btn:hover {
      background-color: #8253e0ff;
    }

    /* Dashboard Stats */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      display: flex;
      flex-direction: column;
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .stat-title {
      font-size: 16px;
      font-weight: 600;
      color: #7f8c8d;
    }

    .stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .stat-change {
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .positive {
      color: #2ecc71;
    }

    .negative {
      color: #e74c3c;
    }

    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      transition: transform 0.3s ease;
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
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
    }

    .view-all {
      color: #9a66ff;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }

    .view-all:hover {
      text-decoration: underline;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #2c3e50;
    }

    tr:hover {
      background-color: #f8f9fa;
    }

    .status {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .ongoing {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .expired {
      background-color: #fdecea;
      color: #e74c3c;
    }

    .expiring-soon {
      background-color: #fef5e7;
      color: #f39c12;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 30px;
    }

    .action-btn {
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      color: #333;
    }

    .action-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      border-color: #9a66ff;
    }

    .action-icon {
      font-size: 24px;
      margin-bottom: 10px;
      color: #9a66ff;
    }

    .action-title {
      font-weight: 600;
      margin-bottom: 5px;
    }

    .action-desc {
      font-size: 14px;
      color: #7f8c8d;
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

      .dashboard-cards {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <?php
  include 'config.php';
  
  // Fetch data for dashboard
  $totalDrivers = fetchSingle("SELECT COUNT(*) as count FROM drivers")['count'];
  $activeDrivers = fetchSingle("SELECT COUNT(*) as count FROM drivers WHERE status != 'off_duty'")['count'];
  $totalTrips = fetchSingle("SELECT COUNT(*) as count FROM trips")['count'];
  $totalVehicles = fetchSingle("SELECT COUNT(*) as count FROM vehicles WHERE status != 'maintenance'")['count'];
  
  // Fetch recent trips - fixed query to match database structure
  $recentTrips = executeQuery("
    SELECT t.*, c.first_name, c.last_name
    FROM trips t 
    JOIN customers c ON t.customer_id = c.customer_id
    ORDER BY t.pickup_time DESC 
    LIMIT 5
  ");
  
  // Fetch compliance alerts - fixed query to handle empty table
  $complianceAlerts = executeQuery("
    SELECT c.*, d.first_name, d.last_name, d.license_number,
           DATEDIFF(c.expiry_date, CURDATE()) as days_until_expiry
    FROM compliance_records c 
    JOIN drivers d ON c.driver_id = d.driver_id 
    WHERE c.status = 'Valid' AND c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY c.expiry_date ASC 
    LIMIT 5
  ");
  
  // Handle case where compliance_records table is empty
  if ($complianceAlerts === false) {
    $complianceAlerts = [];
  }
  
  // Calculate this month's trips
  $monthTripsResult = fetchSingle("SELECT COUNT(*) as count FROM trips WHERE MONTH(pickup_time) = MONTH(CURDATE()) AND YEAR(pickup_time) = YEAR(CURDATE())");
  $monthTrips = $monthTripsResult ? $monthTripsResult['count'] : 0;
  ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li class="active"><a href="dtpm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li><a href="DP.php"><i class="fas fa-user me-2"></i> Driver Performance</a></li>
      <li><a href="TH.php"><i class="fas fa-road me-2"></i> Trip History</a></li>
      <li><a href="CM.php"><i class="fas fa-check-circle me-2"></i> Compliance Monitoring</a></li>
    </ul>
    <div class="bottom-links">
      <a href="#"><i class="fas fa-bell me-2"></i> Notifications</a>
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="../admin-dashboard.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Dashboard</h1>
      <button class="refresh-btn" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>

    <!-- Stats Overview -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">TOTAL DRIVERS</div>
          <div class="stat-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div class="stat-value"><?php echo $totalDrivers; ?></div>
        <div class="stat-change positive">
          <i class="fas fa-arrow-up"></i> <?php echo $activeDrivers; ?> active
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">TOTAL TRIPS</div>
          <div class="stat-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">
            <i class="fas fa-route"></i>
          </div>
        </div>
        <div class="stat-value"><?php echo $totalTrips; ?></div>
        <div class="stat-change positive">
          <i class="fas fa-arrow-up"></i> This month: <?php echo $monthTrips; ?>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">OPERATIONAL VEHICLES</div>
          <div class="stat-icon" style="background-color: rgba(155, 102, 255, 0.1); color: #9a66ff;">
            <i class="fas fa-truck"></i>
          </div>
        </div>
        <div class="stat-value"><?php echo $totalVehicles; ?></div>
        <div class="stat-change positive">
          <i class="fas fa-check-circle"></i> All operational
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">COMPLIANCE ALERTS</div>
          <div class="stat-icon" style="background-color: rgba(243, 156, 18, 0.1); color: #f39c12;">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
        </div>
        <div class="stat-value"><?php echo count($complianceAlerts); ?></div>
        <div class="stat-change negative">
          <i class="fas fa-exclamation-circle"></i> Needs attention
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <a href="DP.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <div class="action-title">Add Driver</div>
        <div class="action-desc">Register a new driver</div>
      </a>
      
      <a href="TH.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-road"></i>
        </div>
        <div class="action-title">Log Trip</div>
        <div class="action-desc">Record a new journey</div>
      </a>
      
      <a href="CM.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-clipboard-check"></i>
        </div>
        <div class="action-title">Compliance</div>
        <div class="action-desc">Check documents</div>
      </a>
      
      <a href="#" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-chart-bar"></i>
        </div>
        <div class="action-title">Reports</div>
        <div class="action-desc">Generate insights</div>
      </a>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
      <!-- Recent Trips Card -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Recent Trips</div>
          <a href="TH.php" class="view-all">View All</a>
        </div>
        <div class="trip-history">
          <table>
            <thead>
              <tr>
                <th>Customer</th>
                <th>Route</th>
                <th>Pickup Time</th>
                <th>Passengers</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recentTrips)): ?>
                <?php foreach ($recentTrips as $trip): ?>
                <tr>
                  <td><?php echo $trip['first_name'] . ' ' . $trip['last_name']; ?></td>
                  <td><?php echo $trip['pickup_location'] . ' to ' . $trip['dropoff_location']; ?></td>
                  <td><?php echo date('M j, Y', strtotime($trip['pickup_time'])); ?></td>
                  <td><?php echo $trip['passengers']; ?></td>
                  <td><span class="status completed"><?php echo $trip['status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align: center;">No trips found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Compliance Alerts Card -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Compliance Alerts</div>
          <a href="CM.php" class="view-all">View All</a>
        </div>
        <div class="compliance-alerts">
          <table>
            <thead>
              <tr>
                <th>Driver</th>
                <th>Document</th>
                <th>Expiry Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($complianceAlerts)): ?>
                <?php foreach ($complianceAlerts as $record): 
                  $statusClass = 'completed';
                  $statusText = 'Valid';
                  
                  if ($record['days_until_expiry'] < 0) {
                    $statusClass = 'expired';
                    $statusText = 'Expired';
                  } elseif ($record['days_until_expiry'] <= 7) {
                    $statusClass = 'expiring-soon';
                    $statusText = 'Expiring soon';
                  }
                ?>
                <tr>
                  <td><?php echo $record['first_name'] . ' ' . $record['last_name']; ?></td>
                  <td><?php echo $record['record_type']; ?></td>
                  <td><?php echo $record['expiry_date']; ?></td>
                  <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center;">No compliance alerts</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Function to refresh dashboard data
    function refreshData() {
      const refreshBtn = document.querySelector('.refresh-btn');
      refreshBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Refreshing...';
      
      // Reload the page after a short delay to show the refreshing animation
      setTimeout(() => {
        window.location.reload();
      }, 800);
    }
    
    // Auto-refresh every 60 seconds
    setInterval(refreshData, 60000);
  </script>
</body>
</html>