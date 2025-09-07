
<?php
// dashboard.php
include 'config.php';

// Handle delete request
if (isset($_GET['delete_dispatch'])) {
    $dispatchId = $_GET['dispatch_id'];
    
    // Get dispatch details before deleting
    $dispatch = fetchSingle("SELECT driver_id, vehicle_id FROM dispatches WHERE dispatch_id = ?", [$dispatchId]);
    
    if ($dispatch) {
        // Update driver and vehicle status to available
        executeQuery("UPDATE drivers SET status = 'available' WHERE driver_id = ?", [$dispatch['driver_id']]);
        executeQuery("UPDATE vehicles SET status = 'available' WHERE vehicle_id = ?", [$dispatch['vehicle_id']]);
        
        // Delete the dispatch record
        executeQuery("DELETE FROM dispatches WHERE dispatch_id = ?", [$dispatchId]);
        
        header("Location: vrds-dashboard.php?deleted=1");
        exit();
    } else {
        $error = "Dispatch record not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VRDS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* CSS from the original files, consolidated */
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

    .total-bookings {
      color: var(--primary);
    }

    .upcoming-trips {
      color: var(--secondary);
    }

    .completed-trips {
      color: var(--success);
    }

    .cancelled-trips {
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
      <li class="active"><a href="vrds-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
      <li><a href="TR.php"><i class="fas fa-truck me-2"></i> Trip Requests</a></li>
      <li><a href="BM.php"><i class="fas fa-calendar me-2"></i> Booking Management</a></li>
      <li><a href="DS.php"><i class="fas fa-paper-plane me-2"></i> Dispatch System</a></li>
      <li><a href="AA.php"><i class="fas fa-robot me-2"></i> Auto Allocation</a></li>
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
      <div class="page-title">Vehicle Reservation and Dispatch System</div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
      Dispatch record deleted successfully!
    </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="dashboard-stats">
      <?php
      // Get stats from database
      $totalBookings = fetchSingle("SELECT COUNT(*) as count FROM bookings")['count'];
      $pendingRequests = fetchSingle("SELECT COUNT(*) as count FROM trips WHERE status = 'pending'")['count'];
      $completedTrips = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE status = 'completed'")['count'];
      $activeDispatches = fetchSingle("SELECT COUNT(*) as count FROM dispatches WHERE status IN ('dispatched', 'in_progress')")['count'];
      ?>
      <div class="stat-card">
        <div class="stat-title">Total Bookings</div>
        <div class="stat-value"><?php echo $totalBookings; ?></div>
        <div class="stat-icon total-bookings">
          <i class="fas fa-calendar"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Pending Trips Request</div>
        <div class="stat-value"><?php echo $pendingRequests; ?></div>
        <div class="stat-icon upcoming-trips">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Completed Trips</div>
        <div class="stat-value"><?php echo $completedTrips; ?></div>
        <div class="stat-icon completed-trips">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-title">Active Dispatches</div>
        <div class="stat-value"><?php echo $activeDispatches; ?></div>
        <div class="stat-icon cancelled-trips">
          <i class="fas fa-paper-plane"></i>
        </div>
      </div>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Recent Trip Requests</div>
          <a href="TR.php" class="btn btn-view">View All</a>
        </div>
        <div class="request-list">
          <table>
            <thead>
              <tr>
                <th>Trip ID</th>
                <th>Customer</th>
                <th>Pickup Location</th>
                <th>Pickup Time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $recentRequests = executeQuery("SELECT r.*, c.first_name, c.last_name 
                             FROM trips r 
                             JOIN customers c ON r.customer = c.customer_id 
                             ORDER BY r.created_at DESC LIMIT 5");
              
              foreach ($recentRequests as $request) {
                echo "<tr>
                  <td>#REQ-{$request['trip_id']}</td>
                  <td>{$request['first_name']} {$request['last_name']}</td>
                  <td>{$request['pickup_location']}</td>
                  <td>" . date('M j, Y g:i A', strtotime($request['pickup_time'])) . "</td>
                  <td><span class='status {$request['status']}'>" . ucfirst(str_replace('_', ' ', $request['status'])) . "</span></td>
                </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <div class="card-title">Active Dispatches</div>
          <a href="DS.php" class="btn btn-view">View All</a>
        </div>
        <div class="dispatch-list">
          <table>
            <thead>
              <tr>
                <th>Dispatch ID</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $activeDispatches = executeQuery("SELECT d.*, dr.first_name, dr.last_name, v.make, v.model, v.license_plate 
                                               FROM dispatches d 
                                               JOIN drivers dr ON d.driver_id = dr.driver_id 
                                               JOIN vehicles v ON d.vehicle_id = v.vehicle_id 
                                               WHERE d.status IN ('dispatched', 'in_progress') 
                                               ORDER BY d.dispatch_time DESC LIMIT 5");
              
              foreach ($activeDispatches as $dispatch) {
                echo "<tr>
                  <td>#DSP-{$dispatch['dispatch_id']}</td>
                  <td>{$dispatch['first_name']} {$dispatch['last_name']}</td>
                  <td>{$dispatch['make']} {$dispatch['model']} ({$dispatch['license_plate']})</td>
                  <td><span class='status {$dispatch['status']}'>" . ucfirst(str_replace('_', ' ', $dispatch['status'])) . "</span></td>
                </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Completed Trips Table -->
       <div class="card">
        <div class="card-header">
          <div class="card-title">Completed Trips</div>
        </div>
        <div class="completed-trips-list">
          <table>
            <thead>
              <tr>
                <th>Trip ID</th>
                <th>Customer</th>
                <th>Pickup Location</th>
                <th>Dropoff Location</th>
                <th>Pickup Time</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Fixed query to get completed trips with all necessary data
             $completedTrips = executeQuery("
    SELECT 
      d.dispatch_id,
      COALESCE(t.trip_id, b.booking_id) as trip_id,
      COALESCE(t.pickup_location, b.pickup_location) as pickup_location,
      COALESCE(t.dropoff_location, b.dropoff_location) as dropoff_location,
      COALESCE(t.pickup_time, b.pickup_time) as pickup_time,
      c.first_name, 
      c.last_name,
      dr.first_name as driver_first, 
      dr.last_name as driver_last,
      v.make, 
      v.model, 
      v.license_plate,
      CASE 
        WHEN t.trip_id IS NOT NULL THEN 'Trip'
        WHEN b.booking_id IS NOT NULL THEN 'Booking'
        ELSE 'Unknown'
      END as trip_type
    FROM dispatches d
    LEFT JOIN trips t ON d.trip_id = t.trip_id
    LEFT JOIN bookings b ON d.booking_id = b.booking_id
    LEFT JOIN customers c ON 
      (t.trip_id IS NOT NULL AND t.customer = c.customer_id) OR 
      (b.booking_id IS NOT NULL AND b.customer_id = c.customer_id)
    JOIN drivers dr ON d.driver_id = dr.driver_id
    JOIN vehicles v ON d.vehicle_id = v.vehicle_id
    WHERE d.status = 'completed'
    ORDER BY d.dispatch_time DESC
");
              if (count($completedTrips) > 0) {
                foreach ($completedTrips as $trip) {
                  $tripId = !empty($trip['trip_id']) ? $trip['trip_id'] : 'N/A';
                  $tripType = $trip['trip_type'];
                  echo "<tr>
                    <td>#{$tripType}-{$tripId}</td>
                    <td>{$trip['first_name']} {$trip['last_name']}</td>
                    <td>{$trip['pickup_location']}</td>
                    <td>{$trip['dropoff_location']}</td>
                    <td>" . date('M j, Y g:i A', strtotime($trip['pickup_time'])) . "</td>
                    <td>{$trip['driver_first']} {$trip['driver_last']}</td>
                    <td>{$trip['make']} {$trip['model']} ({$trip['license_plate']})</td>
                    <td class='action-buttons'>
                      <button class='btn btn-view' onclick='viewTrip({$trip['dispatch_id']})'>
                        <i class='fas fa-eye'></i> View
                      </button>
                      <button class='btn btn-delete' onclick='confirmDelete({$trip['dispatch_id']})'>
                        <i class='fas fa-trash'></i> Delete
                      </button>
                    </td>
                  </tr>";
                }
              } else {
                echo "<tr><td colspan='8' style='text-align: center;'>No completed trips found</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- View Trip Modal -->
  <div id="viewTripModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Trip Details</h2>
        <span class="close" onclick="closeModal('viewTripModal')">&times;</span>
      </div>
      <div id="tripDetails">
        <!-- Trip details will be loaded here -->
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Confirm Deletion</h2>
        <span class="close" onclick="closeModal('deleteModal')">&times;</span>
      </div>
      <div class="delete-confirmation">
        <p>Are you sure you want to delete this trip record?</p>
        <p>This action cannot be undone.</p>
      </div>
      <div class="form-actions">
        <button class="btn" onclick="closeModal('deleteModal')">Cancel</button>
        <button class="btn btn-delete" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>

  <script>
    let currentTripId = null;

    // Function to view trip details
    function viewTrip(tripId) {
      // In a real application, you would fetch data from the server via AJAX
      // For this example, we'll simulate the data
      const tripDetails = `
        <div class="form-group">
          <label>Trip ID:</label>
          <p>#TRP-${tripId}</p>
        </div>
        <div class="form-group">
          <label>Customer:</label>
          <p>John Doe</p>
        </div>
        <div class="form-group">
          <label>Pickup Location:</label>
          <p>123 Main St</p>
        </div>
        <div class="form-group">
          <label>Dropoff Location:</label>
          <p>456 Oak Ave</p>
        </div>
        <div class="form-group">
          <label>Pickup Time:</label>
          <p>Sep 7, 2023 10:30 AM</p>
        </div>
        <div class="form-group">
          <label>Driver:</label>
          <p>Jane Smith</p>
        </div>
        <div class="form-group">
          <label>Vehicle:</label>
          <p>Toyota Camry (ABC123)</p>
        </div>
        <div class="form-group">
          <label>Status:</label>
          <p><span class="status completed">Completed</span></p>
        </div>
      `;
      
      document.getElementById('tripDetails').innerHTML = tripDetails;
      document.getElementById('viewTripModal').style.display = 'flex';
    }

    // Function to confirm deletion
    function confirmDelete(tripId) {
      currentTripId = tripId;
      document.getElementById('deleteModal').style.display = 'flex';
    }

    // Function to actually delete the trip
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
      if (currentTripId) {
        // Redirect to delete the record
        window.location.href = `vrds-dashboard.php?delete_dispatch=1&dispatch_id=${currentTripId}`;
      }
    });

    // Function to close modals
    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      const modals = document.querySelectorAll('.modal');
      modals.forEach(modal => {
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>
