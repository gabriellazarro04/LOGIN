<?php
// BM.php - Booking Management
include 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_booking'])) {
        $customerName = $_POST['customer_name'];
        $customerPhone = $_POST['customer_phone'];
        $pickupLocation = $_POST['pickup_location'];
        $dropoffLocation = $_POST['dropoff_location'];
        $pickupTime = $_POST['pickup_time'];
        $passengers = $_POST['passengers'];
        $vehicleType = $_POST['vehicle_type'];
        $specialRequests = $_POST['special_requests'];
        
        // Check if customer exists
        $customer = fetchSingle("SELECT * FROM customers WHERE phone = ?", [$customerPhone]);
        
        if (!$customer) {
            // Create new customer
            $nameParts = explode(' ', $customerName, 2);
            $firstName = $nameParts[0];
            $lastName = count($nameParts) > 1 ? $nameParts[1] : '';
            
            $customerId = insertAndGetId(
                "INSERT INTO customers (first_name, last_name, phone) VALUES (?, ?, ?)",
                [$firstName, $lastName, $customerPhone]
            );
        } else {
            $customerId = $customer['customer_id'];
        }
        
        // Create booking
        insertAndGetId(
            "INSERT INTO bookings (customer_id, pickup_location, dropoff_location, pickup_time, passengers, vehicle_type, special_requests) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$customerId, $pickupLocation, $dropoffLocation, $pickupTime, $passengers, $vehicleType, $specialRequests]
        );
        
        header("Location: BM.php?success=1");
        exit();
    }
}

// Handle delete booking
if (isset($_GET['delete_id'])) {
    $bookingId = $_GET['delete_id'];
    executeQuery("DELETE FROM bookings WHERE booking_id = ?", [$bookingId]);
    header("Location: BM.php?deleted=1");
    exit();
}

// Handle status update
if (isset($_GET['update_status'])) {
    $bookingId = $_GET['booking_id'];
    $newStatus = $_GET['new_status'];
    executeQuery("UPDATE bookings SET status = ? WHERE booking_id = ?", [$newStatus, $bookingId]);
    header("Location: BM.php?updated=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Booking Management - VRDS</title>
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
      <li class="active"><a href="BM.php"><i class="fas fa-calendar me-2"></i> Booking Management</a></li>
      <li><a href="DS.php"><i class="fas fa-paper-plane me-2"></i> Dispatch System</a></li>
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
      <div class="page-title">Booking Management</div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="success-message">
      Booking created successfully!
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
    <div class="success-message">
      Booking deleted successfully!
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
    <div class="success-message">
      Booking status updated successfully!
    </div>
    <?php endif; ?>

    <div class="crud-actions">
      <button class="btn btn-primary" onclick="openModal('add-booking')">
        <i class="fas fa-plus"></i> New Booking
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Scheduled Trips</div>
        </div>
        <div class="scheduled-list">
          <table>
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Pickup Location</th>
                <th>Dropoff Location</th>
                <th>Pickup Time</th>
                <th>Passengers</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $bookings = executeQuery("
                  SELECT b.*, c.first_name, c.last_name 
                  FROM bookings b 
                  JOIN customers c ON b.customer_id = c.customer_id 
                  ORDER BY b.pickup_time ASC
              ");
              
              foreach ($bookings as $booking) {
                  $statusClass = str_replace('_', '-', $booking['status']);
                  echo "<tr>
                    <td>#BK-{$booking['booking_id']}</td>
                    <td>{$booking['first_name']} {$booking['last_name']}</td>
                    <td>{$booking['pickup_location']}</td>
                    <td>{$booking['dropoff_location']}</td>
                    <td>" . date('M j, Y g:i A', strtotime($booking['pickup_time'])) . "</td>
                    <td>{$booking['passengers']}</td>
                    <td><span class='status {$statusClass}'>" . ucfirst(str_replace('_', ' ', $booking['status'])) . "</span></td>
                    <td class='action-buttons'>
                      <button class='btn btn-dispatch' onclick=\"dispatchBooking({$booking['booking_id']}, '{$booking['first_name']} {$booking['last_name']}')\">
                        <i class='fas fa-paper-plane'></i> Dispatch
                      </button>
                      <button class='btn btn-edit' onclick=\"editBooking({$booking['booking_id']})\">
                        <i class='fas fa-edit'></i>
                      </button>
                      <button class='btn btn-delete' onclick=\"deleteBooking({$booking['booking_id']}, '{$booking['first_name']} {$booking['last_name']}')\">
                        <i class='fas fa-trash'></i>
                      </button>
                      <select onchange=\"updateStatus({$booking['booking_id']}, this.value)\" class='btn'>
                        <option value=''>Change Status</option>
                        <option value='pending'>Pending</option>
                        <option value='scheduled'>Scheduled</option>
                        <option value='dispatched'>Dispatched</option>
                        <option value='completed'>Completed</option>
                        <option value='cancelled'>Cancelled</option>
                      </select>
                    </td>
                  </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Booking Modal -->
  <div class="modal" id="add-booking-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">New Booking</div>
        <span class="close" onclick="closeModal('add-booking-modal')">&times;</span>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label for="customer_name">Customer Name</label>
              <input type="text" id="customer_name" name="customer_name" required />
            </div>
            <div class="form-group">
              <label for="customer_phone">Customer Phone</label>
              <input type="text" id="customer_phone" name="customer_phone" required />
            </div>
          </div>
          <div class="form-group">
            <label for="pickup_location">Pickup Location</label>
            <input type="text" id="pickup_location" name="pickup_location" required />
          </div>
          <div class="form-group">
            <label for="dropoff_location">Dropoff Location</label>
            <input type="text" id="dropoff_location" name="dropoff_location" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="pickup_time">Pickup Date & Time</label>
              <input type="datetime-local" id="pickup_time" name="pickup_time" required />
            </div>
            <div class="form-group">
              <label for="passengers">Number of Passengers</label>
              <input type="number" id="passengers" name="passengers" min="1" required />
            </div>
          </div>
          <div class="form-group">
            <label for="vehicle_type">Vehicle Type</label>
            <select id="vehicle_type" name="vehicle_type" required>
              <option value="">Select Vehicle Type</option>
              <option value="car">Car</option>
              <option value="motor">Motorcycle</option>
              <option value="suv">SUV</option>
              <option value="van">Van</option>
            </select>
          </div>
          <div class="form-group">
            <label for="special_requests">Special Requests</label>
            <textarea id="special_requests" name="special_requests" rows="3"></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="add_booking" class="btn btn-primary">Create Booking</button>
          <button type="button" class="btn" onclick="closeModal('add-booking-modal')">Cancel</button>
        </div>
        </form>
    </div>
  </div>

  <script>
    function openModal(type) {
      if (type === 'add-booking') {
        document.getElementById('add-booking-modal').style.display = 'flex';
        document.getElementById('pickup_time').value = new Date().toISOString().slice(0, 16);
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function dispatchBooking(bookingId, customerName) {
      window.location.href = 'DS.php?booking_id=' + bookingId;
    }

    function editBooking(bookingId) {
      alert('Edit functionality for booking #' + bookingId + ' would be implemented here.');
    }

    function deleteBooking(bookingId, customerName) {
      if (confirm('Are you sure you want to delete the booking for ' + customerName + '?')) {
        window.location.href = 'BM.php?delete_id=' + bookingId;
      }
    }

    function updateStatus(bookingId, newStatus) {
      if (newStatus) {
        window.location.href = 'BM.php?update_status=1&booking_id=' + bookingId + '&new_status=' + newStatus;
      }
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    };
  </script>
</body>
</html>