<?php
include 'config.php';

// Get vehicle statistics
$totalVehicles = count(executeQuery("SELECT * FROM vehicles"));
$availableVehicles = count(executeQuery("SELECT * FROM vehicles WHERE status = 'available'"));
$inUseVehicles = count(executeQuery("SELECT * FROM vehicles WHERE status = 'in_use'"));
$maintenanceVehicles = count(executeQuery("SELECT * FROM vehicles WHERE status = 'maintenance'"));

// Get driver statistics
$totalDrivers = count(executeQuery("SELECT * FROM drivers"));

// Get active assignments
$activeAssignments = count(executeQuery("SELECT * FROM vehicle_assignments WHERE status = 'active'"));

// Get scheduled maintenance
$scheduledMaintenance = count(executeQuery("SELECT * FROM maintenance WHERE status = 'scheduled'"));

// Get recent vehicle activities
$recentActivities = executeQuery("
    SELECT v.make, v.model, v.license_plate, v.vehicle_type, v.status as vehicle_status,
           d.first_name, d.last_name, va.purpose
    FROM vehicles v
    LEFT JOIN vehicle_assignments va ON v.vehicle_id = va.vehicle_id AND va.status = 'active'
    LEFT JOIN drivers d ON va.driver_id = d.driver_id
    ORDER BY v.vehicle_id DESC
    LIMIT 5
");

// Get upcoming schedules - FIXED QUERY
$upcomingSchedules = executeQuery("
    SELECT s.purpose, v.make, v.model, s.start_time
    FROM schedules s
    JOIN vehicles v ON s.vehicle_id = v.vehicle_id
    WHERE s.status = 'scheduled' AND s.start_time >= NOW()
    ORDER BY s.start_time ASC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FVM </title>
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
    }

    .dashboard-header {
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

    .dashboard-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    a.viewall {
      text-decoration: none;
      color: #9a66ff;
      background-color:blue;
    }
    .stat-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-title {
      font-size: 14px;
      color: #7f8c8d;
      margin-bottom: 10px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #2c3e50;
    }

    .stat-icon {
      align-self: flex-end;
      font-size: 32px;
      margin-top: -30px;
      opacity: 0.2;
    }

    .total-vehicles {
      color: var(--primary);
    }

    .available-vehicles {
      color: var(--success);
    }

    .in-use-vehicles {
      color: var(--secondary);
    }

    .maintenance-vehicles {
      color: var(--warning);
    }

    .total-drivers {
      color: var(--primary);
    }

    .active-assignments {
      color: var(--secondary);
    }

    .scheduled-maintenance {
      color: var(--warning);
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 30px;
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-3px);
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
      font-weight: 600;
      font-size: 14px;
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


    .available {
      background-color: #bc2ae9ff;
      color: #ffffffff;
    }

    .in-use {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .maintenance {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .scheduled {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .in-progress {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .upcoming-item {
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .upcoming-item:last-child {
      border-bottom: none;
    }

    .upcoming-title {
      font-weight: 600;
      margin-bottom: 5px;
    }

    .upcoming-details {
      display: flex;
      justify-content: space-between;
      color: #7f8c8d;
      font-size: 14px;
    }

    .upcoming-date {
      color: #9a66ff;
      font-weight: 600;
    }

    .chart-container {
      height: 250px;
      margin-top: 20px;
      position: relative;
    }

    @media (max-width: 1024px) {
      .dashboard-cards {
        grid-template-columns: 1fr;
      }
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

      .dashboard-stats {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li class="active"><a href="dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
      <li><a href="VR.php"><i class="fas fa-car me-2"></i> Vehicle Registration</a></li>
      <li><a href="SC.php"><i class="fas fa-calendar-alt me-2"></i> Scheduling</a></li>
      <li><a href="MA.php"><i class="fas fa-tools me-2"></i> Maintenance</a></li>
      <li><a href="VA.php"><i class="fas fa-tasks me-2"></i> Vehicle Assignment</a></li>
      <li><a href="VM.php"><i class="fas fa-map-marker-alt me-2"></i> Vehicle Monitoring</a></li>
    </ul>
    
    <div class="bottom-links">
      <a href="#"><i class="fas fa-bell me-2"></i> Notifications</a>
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="../admin-dashboard.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="dashboard-header">
      <div class="page-title"> Fleet and Vehicle Management Dashboard</div>
      <div class="date-display"><?php echo date('l, F j, Y'); ?></div>
    </div>

    <div class="dashboard-stats">
      <div class="stat-card">
        <div class="stat-title">Total Vehicles</div>
        <div class="stat-value"><?php echo $totalVehicles; ?></div>
        <div class="stat-icon total-vehicles"><i class="fas fa-car"></i></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Available Vehicles</div>
        <div class="stat-value"><?php echo $availableVehicles; ?></div>
        <div class="stat-icon available-vehicles"><i class="fas fa-check-circle"></i></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">In Use Vehicles</div>
        <div class="stat-value"><?php echo $inUseVehicles; ?></div>
        <div class="stat-icon in-use-vehicles"><i class="fas fa-road"></i></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Maintenance Vehicles</div>
        <div class="stat-value"><?php echo $maintenanceVehicles; ?></div>
        <div class="stat-icon maintenance-vehicles"><i class="fas fa-tools"></i></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Total Drivers</div>
        <div class="stat-value"><?php echo $totalDrivers; ?></div>
        <div class="stat-icon total-drivers"><i class="fas fa-users"></i></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Active Assignments</div>
        <div class="stat-value"><?php echo $activeAssignments; ?></div>
        <div class="stat-icon active-assignments"><i class="fas fa-tasks"></i></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Scheduled Maintenance</div>
        <div class="stat-value"><?php echo $scheduledMaintenance; ?></div>
        <div class="stat-icon scheduled-maintenance"><i class="fas fa-calendar-check"></i></div>
      </div>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Recent Vehicle Activities</div>
          <a href="VR.php" class="view-all">View All</a>
        </div>
        <table>
          <thead>
            <tr>
              <th>Vehicle</th>
              <th>Type</th>
              <th>Driver</th>
              <th>Assignment</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentActivities as $activity): 
              $driverName = !empty($activity['first_name']) ? $activity['first_name'] . ' ' . $activity['last_name'] : 'Not Assigned';
              $assignment = !empty($activity['purpose']) ? $activity['purpose'] : 'N/A';
              $statusClass = str_replace('_', '-', $activity['vehicle_status']);
            ?>
            <tr>
              <td><?php echo $activity['make'] . ' ' . $activity['model'] . ' (' . $activity['license_plate'] . ')'; ?></td>
              <td><?php echo ucfirst($activity['vehicle_type']); ?></td>
              <td><?php echo $driverName; ?></td>
              <td><?php echo $assignment; ?></td>
              <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $activity['vehicle_status'])); ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      
    
    <div class="card" style="margin-top: 20px;">
      <div class="card-header">
        <div class="card-title">Vehicle Status Distribution</div>
      </div>
      <div class="chart-container">
        <canvas id="vehicleStatusChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Vehicle Status Chart
      const ctx = document.getElementById('vehicleStatusChart').getContext('2d');
      const vehicleStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Available', 'In Use', 'Maintenance'],
          datasets: [{
            data: [<?php echo $availableVehicles; ?>, <?php echo $inUseVehicles; ?>, <?php echo $maintenanceVehicles; ?>],
            backgroundColor: [
              '#2ecc71',
              '#3498db',
              '#f39c12'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
      
      // Add today's date
      const today = new Date();
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      document.querySelector('.date-display').textContent = today.toLocaleDateString('en-US', options);
    });
  </script>
</body>
</html>