<?php
// AA.php - Auto Allocation
include 'config.php';

// Handle auto allocation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate_request'])) {
    $requestId = $_POST['request_id'];
    
    // Get the trip request details - FIXED COLUMN NAMES
    $request = fetchSingle("
        SELECT t.*, c.first_name, c.last_name 
        FROM trips t 
        JOIN customers c ON t.customer = c.customer_id 
        WHERE t.trip_id = ?
    ", [$requestId]);
    
    if (!$request) {
        $error = "Request not found";
    } else {
        // Auto allocation algorithm
        // 1. Find available drivers
        $availableDrivers = executeQuery("
            SELECT * FROM drivers 
            WHERE status = 'available' 
            ORDER BY created_at ASC
        ");
        
        // 2. Find available vehicles that match the requirements
        $requiredCapacity = $request['passengers'];
        $availableVehicles = executeQuery("
            SELECT * FROM vehicles 
            WHERE status = 'available' AND capacity >= ?
            ORDER BY capacity ASC
        ", [$requiredCapacity]);
        
        // 3. If both driver and vehicle are available, create dispatch
        if (!empty($availableDrivers) && !empty($availableVehicles)) {
            $driver = $availableDrivers[0];
            $vehicle = $availableVehicles[0];
            
            // Update request status
            executeQuery("
                UPDATE trips 
                SET status = 'dispatched' 
                WHERE trip_id = ?
            ", [$requestId]);
            
            // Update driver and vehicle status
            executeQuery("
                UPDATE drivers 
                SET status = 'on_trip' 
                WHERE driver_id = ?
            ", [$driver['driver_id']]);
            
            executeQuery("
                UPDATE vehicles 
                SET status = 'in_use' 
                WHERE vehicle_id = ?
            ", [$vehicle['vehicle_id']]);
            
            // Create dispatch record - FIXED COLUMN NAMES
            $dispatchTime = date('Y-m-d H:i:s');
            $estimatedArrival = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            
            $dispatchId = insertAndGetId("
                INSERT INTO dispatches (trip_id, driver_id, vehicle_id, dispatch_time, estimated_arrival, status)
                VALUES (?, ?, ?, ?, ?, 'dispatched')
            ", [$requestId, $driver['driver_id'], $vehicle['vehicle_id'], $dispatchTime, $estimatedArrival]);
            
            if ($dispatchId) {
                $allocationResult = [
                    'success' => true,
                    'message' => 'Allocation successful!',
                    'driver' => $driver['first_name'] . ' ' . $driver['last_name'] . ' (License: ' . $driver['license_number'] . ')',
                    'vehicle' => $vehicle['make'] . ' ' . $vehicle['model'] . ' (Plate: ' . $vehicle['license_plate'] . ')',
                    'dispatch_time' => $dispatchTime,
                    'estimated_arrival' => $estimatedArrival,
                    'dispatch_id' => $dispatchId
                ];
            } else {
                $allocationResult = [
                    'success' => false,
                    'message' => 'Failed to create dispatch record.'
                ];
            }
        } else {
            $allocationResult = [
                'success' => false,
                'message' => 'No available drivers or vehicles matching the requirements.'
            ];
        }
    }
}

// Get allocation statistics - FIXED COLUMN NAMES
$pendingRequestsCount = fetchSingle("SELECT COUNT(*) as count FROM trips WHERE status = 'pending'")['count'];
$availableDriversCount = fetchSingle("SELECT COUNT(*) as count FROM drivers WHERE status = 'available'")['count'];
$availableVehiclesCount = fetchSingle("SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'")['count'];
$successfulAllocations = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE DATE(created_at) = CURDATE()")['count'];

// Get pending requests for the dropdown - FIXED COLUMN NAMES
$pendingRequests = executeQuery("
    SELECT t.*, c.first_name, c.last_name 
    FROM trips t 
    JOIN customers c ON t.customer = c.customer_id 
    WHERE t.status = 'pending' 
    ORDER BY t.created_at ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Automatic Allocation - VRDS</title>
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

    .pending-requests {
      color: var(--warning);
    }

    .available-drivers-stat {
      color: var(--secondary);
    }

    .available-vehicles {
      color: var(--success);
    }

    .successful-allocations {
      color: var(--primary);
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

    .pending {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .scheduled {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .dispatched {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .in-progress-status {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .cancelled {
      background-color: #fdecea;
      color: #e74c3c;
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

    .allocation-results {
      margin-top: 20px;
    }

    .allocation-result {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
      border-left: 4px solid #2ecc71;
    }

    .allocation-result.error {
      border-left-color: #e74c3c;
    }

    .allocation-result h4 {
      margin-bottom: 15px;
      color: #2c3e50;
    }

    .allocation-result p {
      margin-bottom: 10px;
      font-size: 16px;
    }

    .no-data {
      text-align: center;
      padding: 20px;
      color: #7f8c8d;
      font-style: italic;
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
      <li><a href="vrds-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
      <li><a href="TR.php"><i class="fas fa-truck me-2"></i> Trip Requests</a></li>
      <li><a href="BM.php"><i class="fas fa-calendar me-2"></i> Booking Management</a></li>
      <li><a href="DS.php"><i class="fas fa-paper-plane me-2"></i> Dispatch System</a></li>
      <li class="active"><a href="AA.php"><i class="fas fa-robot me-2"></i> Auto Allocation</a></li>
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
      <div class="page-title">Automatic Allocation</div>
    </div>

    <?php if (isset($error)): ?>
    <div class="error-message">
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    
    <div class="crud-actions">
      <button class="btn btn-primary" onclick="runAutoAllocation()">
        <i class="fas fa-play"></i> Run Auto Allocation
      </button>
      <button class="btn" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh Status
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Allocation Configuration</div>
        </div>
        <div class="allocation-results">
          <form method="POST" id="allocation-form">
            <div class="form-group">
              <label for="auto_request_id">Select Request to Allocate</label>
              <select id="auto_request_id" name="request_id" required>
                <option value="">Select Request</option>
                <?php if (count($pendingRequests) > 0): ?>
                <?php foreach ($pendingRequests as $request): ?>
                <?php
                $displayText = "#TRIP-{$request['trip_id']}: {$request['first_name']} {$request['last_name']} ";
                $displayText .= "({$request['pickup_location']} to {$request['dropoff_location']})";
                ?>
                <option value="<?php echo $request['trip_id']; ?>"><?php echo $displayText; ?></option>
                <?php endforeach; ?>
                <?php else: ?>
                <option value="" disabled>No pending requests</option>
                <?php endif; ?>
              </select>
            </div>
            <input type="hidden" name="allocate_request" value="1">
          </form>
          
          <?php if (isset($allocationResult)): ?>
          <div class="allocation-result <?php echo $allocationResult['success'] ? '' : 'error'; ?>">
            <h4>Allocation Result</h4>
            <p><strong>Status:</strong> <?php echo $allocationResult['message']; ?></p>
            
            <?php if ($allocationResult['success']): ?>
            <p><strong>Driver:</strong> <?php echo $allocationResult['driver']; ?></p>
            <p><strong>Vehicle:</strong> <?php echo $allocationResult['vehicle']; ?></p>
            <p><strong>Dispatch Time:</strong> <?php echo date('M j, Y g:i A', strtotime($allocationResult['dispatch_time'])); ?></p>
            <p><strong>Estimated Arrival:</strong> <?php echo date('M j, Y g:i A', strtotime($allocationResult['estimated_arrival'])); ?></p>
            
            <div class="form-actions">
              <a href="DS.php" class="btn btn-dispatch">
                <i class="fas fa-eye"></i> View All Dispatches
              </a>
              <a href="AA.php" class="btn btn-primary">
                <i class="fas fa-sync"></i> Allocate Another
              </a>
            </div>
            <?php else: ?>
            <div class="form-actions">
              <a href="DS.php" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Manual Dispatch
              </a>
              <a href="AA.php" class="btn">
                <i class="fas fa-sync"></i> Try Again
              </a>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">Available Resources</div>
        </div>
        <div class="resource-list">
          <h4>Available Drivers</h4>
          <?php
          $availableDrivers = executeQuery("SELECT * FROM drivers WHERE status = 'available' ORDER BY first_name, last_name");
          if (count($availableDrivers) > 0): ?>
          <ul>
            <?php foreach ($availableDrivers as $driver): ?>
            <li><?php echo $driver['first_name'] . ' ' . $driver['last_name'] . ' (' . $driver['license_number'] . ')'; ?></li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="no-data">No available drivers</p>
          <?php endif; ?>

          <h4>Available Vehicles</h4>
          <?php
          $availableVehicles = executeQuery("SELECT * FROM vehicles WHERE status = 'available' ORDER BY make, model");
          if (count($availableVehicles) > 0): ?>
          <ul>
            <?php foreach ($availableVehicles as $vehicle): ?>
            <li><?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ') - Capacity: ' . $vehicle['capacity']; ?></li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="no-data">No available vehicles</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    function runAutoAllocation() {
      const requestId = document.getElementById('auto_request_id').value;
      if (!requestId) {
        alert('Please select a request to allocate');
        return;
      }
      
      // Submit the form
      document.getElementById('allocation-form').submit();
    }

    document.getElementById('auto_request_id').addEventListener('change', function() {
      // Hide any previous results when selection changes
      const resultDivs = document.querySelectorAll('.allocation-result');
      resultDivs.forEach(div => div.style.display = 'none');
    });
  </script>
</body>
</html>