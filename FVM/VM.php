<?php
// VM.php - Vehicle Monitoring
include 'config.php';

// Handle vehicle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vehicle_status'])) {
    $vehicleId = $_POST['vehicle_id'];
    $status = $_POST['status'];
    
    executeQuery("UPDATE vehicles SET status = ? WHERE vehicle_id = ?", [$status, $vehicleId]);
    
    header("Location: VM.php?updated=1");
    exit();
}

// Handle driver status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_driver_status'])) {
    $driverId = $_POST['driver_id'];
    $status = $_POST['status'];
    
    executeQuery("UPDATE drivers SET status = ? WHERE driver_id = ?", [$status, $driverId]);
    
    header("Location: VM.php?updated=1");
    exit();
}

// Get vehicle statistics
$totalVehicles = fetchSingle("SELECT COUNT(*) as count FROM vehicles")['count'];
$availableVehicles = fetchSingle("SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'")['count'];
$inUseVehicles = fetchSingle("SELECT COUNT(*) as count FROM vehicles WHERE status = 'in_use'")['count'];
$maintenanceVehicles = fetchSingle("SELECT COUNT(*) as count FROM vehicles WHERE status = 'maintenance'")['count'];
$activeDispatches = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE status IN ('dispatched', 'in_progress')")['count'];

// Get all vehicles with their current status
$vehicles = executeQuery("
    SELECT v.*, d.first_name, d.last_name 
    FROM vehicles v 
    LEFT JOIN dispatches dp ON v.vehicle_id = dp.vehicle_id AND dp.status IN ('dispatched', 'in_progress')
    LEFT JOIN drivers d ON dp.driver_id = d.driver_id
    ORDER BY v.status, v.make, v.model
");

// Get all drivers with their current status
$drivers = executeQuery("
    SELECT d.*, v.make, v.model, v.license_plate 
    FROM drivers d 
    LEFT JOIN dispatches dp ON d.driver_id = dp.driver_id AND dp.status IN ('dispatched', 'in_progress')
    LEFT JOIN vehicles v ON dp.vehicle_id = v.vehicle_id
    ORDER BY d.status, d.first_name, d.last_name
");

// Get active dispatches
$activeDispatchesList = executeQuery("
    SELECT dp.*, d.first_name, d.last_name, v.make, v.model, v.license_plate 
    FROM dispatches dp 
    JOIN drivers d ON dp.driver_id = d.driver_id 
    JOIN vehicles v ON dp.vehicle_id = v.vehicle_id 
    WHERE dp.status IN ('dispatched', 'in_progress')
    ORDER BY dp.dispatch_time DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vehicle Monitoring - VRDS</title>
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

    .stat-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      display: flex;
      flex-direction: column;
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

    .active-dispatches {
      color: var(--accent);
    }

    .crud-actions {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .btn {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background-color: #9a66ff;
      color: white;
    }

    .btn-primary:hover {
      background-color: #8253e0ff;
    }

    .btn-edit {
      background-color: #f39c12;
      color: white;
    }

    .btn-edit:hover {
      background-color: #e67e22;
    }

    .btn-dispatch {
      background-color: #2ecc71;
      color: white;
    }

    .btn-dispatch:hover {
      background-color: #27ae60;
    }

    .btn-delete {
      background-color: #e74c3c;
      color: white;
    }

    .btn-delete:hover {
      background-color: #c0392b;
    }

    .btn-view {
      background-color: #3498db;
      color: white;
    }

    .btn-view:hover {
      background-color: #2980b9;
    }

    .dashboard-cards {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 30px;
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

    .card-actions {
      display: flex;
      gap: 10px;
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
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .in-use {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .maintenance {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .dispatched {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .in-progress-status {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .action-buttons {
      display: flex;
      gap: 5px;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: white;
      border-radius: 10px;
      width: 600px;
      max-width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .modal-title {
      font-size: 20px;
      font-weight: 600;
      color: #2c3e50;
    }

    .close {
      font-size: 24px;
      cursor: pointer;
      color: #7f8c8d;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #2c3e50;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
    }

    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .delete-confirmation {
      text-align: center;
      margin: 20px 0;
      font-size: 16px;
    }

    .success-message {
      background-color: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .vehicle-map {
      height: 400px;
      background-color: #eee;
      border-radius: 10px;
      margin-bottom: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #777;
      font-size: 18px;
    }

    .driver-status-filter {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .filter-btn {
      padding: 8px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      background: white;
      cursor: pointer;
    }

    .filter-btn.active {
      background: #9a66ff;
      color: white;
      border-color: #9a66ff;
    }

    /* New Layout Styles */
    .dual-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }

    .layout-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      height: 100%;
    }

    .layout-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .layout-card-title {
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
    }

    .status-summary {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }

    .status-item {
      text-align: center;
      padding: 10px;
      border-radius: 8px;
      background-color: #f8f9fa;
    }

    .status-count {
      font-size: 24px;
      font-weight: 700;
    }

    .status-label {
      font-size: 12px;
      color: #7f8c8d;
    }

    .compact-table {
      font-size: 14px;
    }

    .compact-table th,
    .compact-table td {
      padding: 8px 10px;
    }

    .details-content {
      margin-bottom: 20px;
    }

    .details-row {
      display: flex;
      margin-bottom: 10px;
    }

    .details-label {
      font-weight: 600;
      width: 150px;
      color: #2c3e50;
    }

    .details-value {
      flex: 1;
    }

    .history-list {
      max-height: 300px;
      overflow-y: auto;
    }

    .history-item {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .history-item:last-child {
      border-bottom: none;
    }

    .no-data {
      text-align: center;
      padding: 20px;
      color: #7f8c8d;
      font-style: italic;
    }

    @media (max-width: 1200px) {
      .dual-layout {
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

      .form-row {
        flex-direction: column;
        gap: 0;
      }

      .dual-layout {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li><a href="fvm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
      <li><a href="VR.php"><i class="fas fa-car me-2"></i> Vehicle Registration</a></li>
      <li><a href="SC.php"><i class="fas fa-calendar-alt me-2"></i> Scheduling</a></li>
      <li><a href="MA.php"><i class="fas fa-tools me-2"></i> Maintenance</a></li>
      <li><a href="VA.php"><i class="fas fa-tasks me-2"></i> Vehicle Assignment</a></li>
      <li class="active"><a href="VM.php"><i class="fas fa-map-marker-alt me-2"></i> Vehicle Monitoring</a></li>
    </ul>
    
    <div class="bottom-links">
      <a href="#"><i class="fas fa-bell me-2"></i> Notifications</a>
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="../login.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="dashboard-header">
      <div class="page-title">Vehicle Monitoring</div>
      <button class="btn btn-primary" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div class="success-message">
      Status updated successfully!
    </div>
    <?php endif; ?>

    <!-- Statistics Dashboard -->
    <div class="dashboard-stats">
      <div class="stat-card">
        <div class="stat-title">Total Vehicles</div>
        <div class="stat-value"><?php echo $totalVehicles; ?></div>
        <div class="stat-icon total-vehicles">
          <i class="fas fa-car"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Available Vehicles</div>
        <div class="stat-value"><?php echo $availableVehicles; ?></div>
        <div class="stat-icon available-vehicles">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">In Use Vehicles</div>
        <div class="stat-value"><?php echo $inUseVehicles; ?></div>
        <div class="stat-icon in-use-vehicles">
          <i class="fas fa-road"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Maintenance Vehicles</div>
        <div class="stat-value"><?php echo $maintenanceVehicles; ?></div>
        <div class="stat-icon maintenance-vehicles">
          <i class="fas fa-tools"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Active Dispatches</div>
        <div class="stat-value"><?php echo $activeDispatches; ?></div>
        <div class="stat-icon active-dispatches">
          <i class="fas fa-paper-plane"></i>
        </div>
      </div>
    </div>

    <!-- Dual Layout for Vehicle and Driver Status -->
    <div class="dual-layout">
      <!-- Vehicle Status Card -->
      <div class="layout-card">
        <div class="layout-card-header">
          <div class="layout-card-title">Vehicle Status</div>
          <div class="driver-status-filter">
            <button class="filter-btn active" onclick="filterVehicles('all')">All</button>
            <button class="filter-btn" onclick="filterVehicles('available')">Available</button>
            <button class="filter-btn" onclick="filterVehicles('in_use')">In Use</button>
            <button class="filter-btn" onclick="filterVehicles('maintenance')">Maintenance</button>
          </div>
        </div>
        
        <div class="vehicle-status-list">
          <table class="compact-table">
            <thead>
              <tr>
                <th>Vehicle</th>
                <th>Vehicle Type</th>
                <th>Driver</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($vehicles) > 0): ?>
                <?php foreach ($vehicles as $vehicle): 
                  $statusClass = str_replace('_', '-', $vehicle['status']);
                  $driverName = !empty($vehicle['first_name']) ? $vehicle['first_name'] . ' ' . $vehicle['last_name'] : 'N/A';
                ?>
                <tr class="vehicle-row" data-status="<?php echo $vehicle['status']; ?>">
                  <td><?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'; ?></td>
                  <td><?php echo $vehicle['vehicle_type']; ?></td>
                  <td><?php echo $driverName; ?></td>
                  <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $vehicle['status'])); ?></span></td>
                  <td class="action-buttons">
                    <button class="btn btn-edit" onclick="updateVehicleStatus(<?php echo $vehicle['vehicle_id']; ?>, '<?php echo $vehicle['status']; ?>')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-view" onclick="viewVehicleHistory(<?php echo $vehicle['vehicle_id']; ?>)">
                      <i class="fas fa-history"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="no-data">No vehicles found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Driver Status Card -->
      <div class="layout-card">
        <div class="layout-card-header">
          <div class="layout-card-title">Driver Status</div>
          <div class="driver-status-filter">
            <button class="filter-btn active" onclick="filterDrivers('all')">All</button>
            <button class="filter-btn" onclick="filterDrivers('available')">Available</button>
            <button class="filter-btn" onclick="filterDrivers('on_trip')">On Trip</button>
            <button class="filter-btn" onclick="filterDrivers('off_duty')">Off Duty</button>
          </div>
        </div>
        
        <div class="driver-status-list">
          <table class="compact-table">
            <thead>
              <tr>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th>Phone</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($drivers) > 0): ?>
                <?php foreach ($drivers as $driver): 
                  $statusClass = str_replace('_', '-', $driver['status']);
                  $vehicleInfo = !empty($driver['make']) ? $driver['make'] . ' ' . $driver['model'] . ' (' . $driver['license_plate'] . ')' : 'N/A';
                ?>
                <tr class="driver-row" data-status="<?php echo $driver['status']; ?>">
                  <td><?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?></td>
                  <td><?php echo $vehicleInfo; ?></td>
                  <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $driver['status'])); ?></span></td>
                  <td><?php echo $driver['phone']; ?></td>
                  <td class="action-buttons">
                    <button class="btn btn-edit" onclick="updateDriverStatus(<?php echo $driver['driver_id']; ?>, '<?php echo $driver['status']; ?>')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-primary" onclick="contactDriver('<?php echo $driver['phone']; ?>')">
                      <i class="fas fa-phone"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="no-data">No drivers found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Active Dispatches Card -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Active Dispatches</div>
      </div>
      <div class="dispatch-list">
        <table>
          <thead>
            <tr>
              <th>Dispatch ID</th>
              <th>Vehicle</th>
              <th>Driver</th>
              <th>Dispatch Time</th>
              <th>Estimated Arrival</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($activeDispatchesList) > 0): ?>
              <?php foreach ($activeDispatchesList as $dispatch): 
                $statusClass = str_replace('_', '-', $dispatch['status']);
              ?>
              <tr>
                <td>#DSP-<?php echo $dispatch['dispatch_id']; ?></td>
                <td><?php echo $dispatch['make'] . ' ' . $dispatch['model'] . ' (' . $dispatch['license_plate'] . ')'; ?></td>
                <td><?php echo $dispatch['first_name'] . ' ' . $dispatch['last_name']; ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($dispatch['dispatch_time'])); ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($dispatch['estimated_arrival'])); ?></td>
                <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $dispatch['status'])); ?></span></td>
                <td class="action-buttons">
                  <button class="btn btn-view" onclick="viewDispatchDetails(<?php echo $dispatch['dispatch_id']; ?>)">
                    <i class="fas fa-eye"></i> Details
                  </button>
                  <button class="btn btn-primary" onclick="contactDriver('<?php echo $dispatch['phone']; ?>')">
                    <i class="fas fa-phone"></i> Call
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="no-data">No active dispatches</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Update Vehicle Status Modal -->
  <div class="modal" id="update-vehicle-status-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Update Vehicle Status</div>
        <span class="close" onclick="closeModal('update-vehicle-status-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="update_vehicle_id" name="vehicle_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="vehicle_status">Status</label>
            <select id="vehicle_status" name="status" required>
              <option value="available">Available</option>
              <option value="in_use">In Use</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="update_vehicle_status" class="btn btn-primary">Update Status</button>
          <button type="button" class="btn" onclick="closeModal('update-vehicle-status-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Update Driver Status Modal -->
  <div class="modal" id="update-driver-status-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Update Driver Status</div>
        <span class="close" onclick="closeModal('update-driver-status-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="update_driver_id" name="driver_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="driver_status">Status</label>
            <select id="driver_status" name="status" required>
              <option value="available">Available</option>
              <option value="on_trip">On Trip</option>
              <option value="off_duty">Off Duty</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="update_driver_status" class="btn btn-primary">Update Status</button>
          <button type="button" class="btn" onclick="closeModal('update-driver-status-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(type) {
      if (type === 'update-vehicle-status') {
        document.getElementById('update-vehicle-status-modal').style.display = 'flex';
      } else if (type === 'update-driver-status') {
        document.getElementById('update-driver-status-modal').style.display = 'flex';
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function updateVehicleStatus(vehicleId, currentStatus) {
      document.getElementById('update_vehicle_id').value = vehicleId;
      document.getElementById('vehicle_status').value = currentStatus;
      openModal('update-vehicle-status');
    }

    function updateDriverStatus(driverId, currentStatus) {
      document.getElementById('update_driver_id').value = driverId;
      document.getElementById('driver_status').value = currentStatus;
      openModal('update-driver-status');
    }

    function contactDriver(phoneNumber) {
      if (phoneNumber && phoneNumber !== 'N/A') {
        // In a real application, this would initiate a phone call
        alert('Calling driver at: ' + phoneNumber);
        // window.location.href = 'tel:' + phoneNumber;
      } else {
        alert('No phone number available for this driver');
      }
    }

    function viewDispatchDetails(dispatchId) {
      // In a real application, this would fetch and display dispatch details
      alert('View details for dispatch #' + dispatchId);
    }

    function viewVehicleHistory(vehicleId) {
      // In a real application, this would fetch and display vehicle history
      alert('View history for vehicle #' + vehicleId);
    }

    function filterVehicles(status) {
      const rows = document.querySelectorAll('.vehicle-row');
      const filterButtons = document.querySelectorAll('.layout-card:first-child .filter-btn');
      
      // Update active button
      filterButtons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(status)) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
      
      // Show/hide rows based on filter
      rows.forEach(row => {
        if (status === 'all' || row.getAttribute('data-status') === status) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    function filterDrivers(status) {
      const rows = document.querySelectorAll('.driver-row');
      const filterButtons = document.querySelectorAll('.layout-card:nth-child(2) .filter-btn');
      
      // Update active button
      filterButtons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(status)) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
      
      // Show/hide rows based on filter
      rows.forEach(row => {
        if (status === 'all' || row.getAttribute('data-status') === status) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    };
  </script>
</body>
</html>