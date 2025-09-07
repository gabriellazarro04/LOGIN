<?php
// DS.php - Dispatch System
include 'config.php';

// Handle dispatch form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispatch_trip'])) {
    $requestId = $_POST['request_id'] ?? null;
    $bookingId = $_POST['booking_id'] ?? null;
    $driverId = $_POST['driver_id'];
    $vehicleId = $_POST['vehicle_id'];
    $dispatchTime = $_POST['dispatch_time'];
    $estimatedArrival = $_POST['estimated_arrival'];
    $dispatchNotes = $_POST['dispatch_notes'];
    
    // Validate driver and vehicle are available
    $driver = fetchSingle("SELECT status FROM drivers WHERE driver_id = ?", [$driverId]);
    $vehicle = fetchSingle("SELECT status FROM vehicles WHERE vehicle_id = ?", [$vehicleId]);
    
    if (!$driver || $driver['status'] !== 'available') {
        $error = "Selected driver is not available";
    } elseif (!$vehicle || $vehicle['status'] !== 'available') {
        $error = "Selected vehicle is not available";
    } else {
        // Update request or booking status
        if ($requestId) {
            // Check if the trip exists before updating - FIXED COLUMN NAME
            $tripExists = fetchSingle("SELECT trip_id FROM trips WHERE trip_id = ?", [$requestId]);
            if ($tripExists) {
                executeQuery("UPDATE trips SET status = 'dispatched' WHERE trip_id = ?", [$requestId]);
            } else {
                $error = "The selected trip no longer exists";
            }
        } elseif ($bookingId) {
            // Check if the booking exists before updating
            $bookingExists = fetchSingle("SELECT booking_id FROM bookings WHERE booking_id = ?", [$bookingId]);
            if ($bookingExists) {
                executeQuery("UPDATE bookings SET status = 'dispatched' WHERE booking_id = ?", [$bookingId]);
            } else {
                $error = "The selected booking no longer exists";
            }
        }
        
        if (!isset($error)) {
            // Update driver and vehicle status
            executeQuery("UPDATE drivers SET status = 'on_trip' WHERE driver_id = ?", [$driverId]);
            executeQuery("UPDATE vehicles SET status = 'in_use' WHERE vehicle_id = ?", [$vehicleId]);
            
                    // Create dispatch record - Handle NULL values properly
            $dispatchId = insertAndGetId("
                INSERT INTO dispatches (trip_id, booking_id, driver_id, vehicle_id, dispatch_time, estimated_arrival, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'dispatched')
            ", [
                $requestId ? $requestId : NULL, 
                $bookingId ? $bookingId : NULL, 
                $driverId, 
                $vehicleId, 
                $dispatchTime, 
                $estimatedArrival, 
                $dispatchNotes
            ]);
            
            if ($dispatchId) {
                header("Location: DS.php?success=1");
                exit();
            } else {
                $error = "Failed to create dispatch record";
            }
        }
    }
}

// Handle status update
if (isset($_GET['update_status'])) {
    $dispatchId = $_GET['dispatch_id'];
    $newStatus = $_GET['new_status'];
    
    executeQuery("UPDATE dispatches SET status = ? WHERE dispatch_id = ?", [$newStatus, $dispatchId]);
    
    // If completed, free up the driver and vehicle AND update the trip status
    if ($newStatus === 'completed') {
        $dispatch = fetchSingle("SELECT driver_id, vehicle_id, trip_id, booking_id FROM dispatches WHERE dispatch_id = ?", [$dispatchId]);
        
        if ($dispatch) {
            executeQuery("UPDATE drivers SET status = 'available' WHERE driver_id = ?", [$dispatch['driver_id']]);
            executeQuery("UPDATE vehicles SET status = 'available' WHERE vehicle_id = ?", [$dispatch['vehicle_id']]);
            
            // Update the trip status to completed if this dispatch is for a trip
            if ($dispatch['trip_id']) {
                executeQuery("UPDATE trips SET status = 'completed' WHERE trip_id = ?", [$dispatch['trip_id']]);
            }
            
            // Update the booking status to completed if this dispatch is for a booking
            if ($dispatch['booking_id']) {
                executeQuery("UPDATE bookings SET status = 'completed' WHERE booking_id = ?", [$dispatch['booking_id']]);
            }
        }
    }
    
    header("Location: DS.php?updated=1");
    exit();
}

// Get available drivers and vehicles for the form
$availableDrivers = executeQuery("SELECT * FROM drivers WHERE status = 'available'");
$availableVehicles = executeQuery("SELECT * FROM vehicles WHERE status = 'available'");

// Get pending requests and bookings for dispatch - FIXED COLUMN NAME
$pendingRequests = executeQuery("
    SELECT t.*, c.first_name, c.last_name 
    FROM trips t 
    JOIN customers c ON t.customer = c.customer_id 
    WHERE t.status = 'pending'
");

$scheduledBookings = executeQuery("
    SELECT b.*, c.first_name, c.last_name 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.customer_id 
    WHERE b.status = 'scheduled'
");

// Get dispatch statistics for dashboard
$dispatchedCount = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE status = 'dispatched'")['count'];
$inProgressCount = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE status = 'in_progress'")['count'];
$completedCount = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE status = 'completed'")['count'];
$availableDriversCount = fetchSingle("SELECT COUNT(*) as count FROM drivers WHERE status = 'available'")['count'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dispatch System - VRDS</title>
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

    .dispatched-trips {
      color: var(--primary);
    }

    .in-progress {
      color: var(--secondary);
    }

    .completed-trips {
      color: var(--success);
    }

    .available-drivers {
      color: var(--warning);
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
      <li class="active"><a href="DS.php"><i class="fas fa-paper-plane me-2"></i> Dispatch System</a></li>
      <li><a href="AA.php"><i class="fas fa-robot me-2"></i> Auto Allocation</a></li>
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
      <div class="page-title">Dispatch System</div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="success-message">
      Dispatch created successfully!
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
    <div class="success-message">
      Dispatch status updated successfully!
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="error-message">
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="crud-actions">
      <button class="btn btn-primary" onclick="openModal('dispatch')">
        <i class="fas fa-paper-plane"></i> New Dispatch
      </button>
      <button class="btn" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh Status
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Pending Dispatch</div>
        </div>
        <div class="pending-dispatch">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Customer</th>
                <th>Pickup Location</th>
                <th>Pickup Time</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($pendingRequests) > 0): ?>
              <?php foreach ($pendingRequests as $request): ?>
              <tr>
                <td>TRIP #<?php echo $request['trip_id']; ?></td>
                <td>Trip Request</td>
                <td><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></td>
                <td><?php echo $request['pickup_location']; ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($request['pickup_time'])); ?></td>
                <td class="action-buttons">
                  <button class="btn btn-dispatch" onclick="dispatchRequest(<?php echo $request['trip_id']; ?>, 'request')">
                    <i class="fas fa-paper-plane"></i> Dispatch
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td colspan="6" class="no-data">No pending trip requests</td>
              </tr>
              <?php endif; ?>
              
              <?php if (count($scheduledBookings) > 0): ?>
              <?php foreach ($scheduledBookings as $booking): ?>
              <tr>
                <td>BK #<?php echo $booking['booking_id']; ?></td>
                <td>Booking</td>
                <td><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                <td><?php echo $booking['pickup_location']; ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($booking['pickup_time'])); ?></td>
                <td class="action-buttons">
                  <button class="btn btn-dispatch" onclick="dispatchRequest(<?php echo $booking['booking_id']; ?>, 'booking')">
                    <i class="fas fa-paper-plane"></i> Dispatch
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td colspan="6" class="no-data">No scheduled bookings</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <div class="card-title">Active Dispatches</div>
        </div>
        <div class="dispatch-list">
          <table>
            <thead>
              <tr>
                <th>Dispatch ID</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Dispatch Time</th>
                <th>Estimated Arrival</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $dispatches = executeQuery("
                SELECT d.*, dr.first_name, dr.last_name, v.make, v.model, v.license_plate 
                FROM dispatches d 
                JOIN drivers dr ON d.driver_id = dr.driver_id 
                JOIN vehicles v ON d.vehicle_id = v.vehicle_id 
                WHERE d.status IN ('dispatched', 'in_progress')
                ORDER BY d.dispatch_time DESC
              ");
              
              if (count($dispatches) > 0) {
                foreach ($dispatches as $dispatch) {
                  $statusClass = str_replace('_', '-', $dispatch['status']);
                  echo "<tr>
                    <td>DSP #{$dispatch['dispatch_id']}</td>
                    <td>{$dispatch['first_name']} {$dispatch['last_name']}</td>
                    <td>{$dispatch['make']} {$dispatch['model']} ({$dispatch['license_plate']})</td>
                    <td>" . date('M j, Y g:i A', strtotime($dispatch['dispatch_time'])) . "</td>
                     <td>" . date('M j, Y g:i A', strtotime($dispatch['estimated_arrival'])) ."</td>
                    <td><span class='status {$statusClass}'>" . ucfirst(str_replace('_', ' ', $dispatch['status'])) . "</span></td>
                    <td class='action-buttons'>
                      <select onchange='updateDispatchStatus({$dispatch['dispatch_id']}, this.value)'>
                        <option value=''>Update Status</option>
                        <option value='dispatched'>Dispatched</option>
                        <option value='in_progress'>In Progress</option>
                        <option value='completed'>Completed</option>
                        <option value='cancelled'>Cancelled</option>
                      </select>
                      <button class='btn btn-view' onclick='viewDispatch({$dispatch['dispatch_id']})'>
                        <i class='fas fa-eye'></i> Details
                      </button>
                    </td>
                  </tr>";
                }
              } else {
                echo "<tr><td colspan='6' class='no-data'>No active dispatches</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Dispatch Modal -->
  <div class="modal" id="dispatch-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Dispatch Trip</div>
        <span class="close" onclick="closeModal('dispatch-modal')">&times;</span>
      </div>
      <form method="POST" id="dispatch-form">
        <input type="hidden" id="dispatch_request_id" name="request_id" value="">
        <input type="hidden" id="dispatch_booking_id" name="booking_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="driver_id">Select Driver</label>
            <select id="driver_id" name="driver_id" required>
              <option value="">Select Driver</option>
              <?php foreach ($availableDrivers as $driver): ?>
              <option value="<?php echo $driver['driver_id']; ?>">
                <?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?> 
                (License: <?php echo $driver['license_number']; ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="vehicle_id">Select Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" required>
              <option value="">Select Vehicle</option>
              <?php foreach ($availableVehicles as $vehicle): ?>
              <option value="<?php echo $vehicle['vehicle_id']; ?>">
                <?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?> 
                (<?php echo $vehicle['license_plate']; ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="dispatch_time">Dispatch Time</label>
              <input type="datetime-local" id="dispatch_time" name="dispatch_time" required />
            </div>
            <div class="form-group">
              <label for="estimated_arrival">Estimated Arrival</label>
              <input type="datetime-local" id="estimated_arrival" name="estimated_arrival" required />
            </div>
          </div>
          <div class="form-group">
            <label for="dispatch_notes">Dispatch Notes</label>
            <textarea id="dispatch_notes" name="dispatch_notes" rows="3" placeholder="Add any special instructions for the driver..."></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="dispatch_trip" class="btn btn-dispatch">Dispatch Trip</button>
          <button type="button" class="btn" onclick="closeModal('dispatch-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let currentRequestId = null;
    let currentRequestType = null;
    
    function openModal(type) {
      if (type === 'dispatch') {
        document.getElementById('dispatch-modal').style.display = 'flex';
        document.getElementById('dispatch_time').value = new Date().toISOString().slice(0, 16);
        
        // Set estimated arrival to 30 minutes from now
        const now = new Date();
        now.setMinutes(now.getMinutes() + 30);
        document.getElementById('estimated_arrival').value = now.toISOString().slice(0, 16);
        
        // Clear any previous request/booking IDs
        document.getElementById('dispatch_request_id').value = '';
        document.getElementById('dispatch_booking_id').value = '';
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function dispatchRequest(id, type) {
      if (type === 'request') {
        document.getElementById('dispatch_request_id').value = id;
        document.getElementById('dispatch_booking_id').value = '';
      } else if (type === 'booking') {
        document.getElementById('dispatch_request_id').value = '';
        document.getElementById('dispatch_booking_id').value = id;
      }
      
      openModal('dispatch');
    }

    function updateDispatchStatus(dispatchId, newStatus) {
      if (newStatus) {
        if (confirm('Are you sure you want to update the status of this dispatch?')) {
          window.location.href = `DS.php?update_status=1&dispatch_id=${dispatchId}&new_status=${newStatus}`;
        }
      }
    }

    function viewDispatch(dispatchId) {
      alert('View details for dispatch #' + dispatchId);
      // In a real application, this would open a modal with detailed information
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    };
  </script>
</body>
</html>