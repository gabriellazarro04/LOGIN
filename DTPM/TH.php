<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trip History</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #3498db;
      --accent: #e74c3c;
      --light: #ecf0f1;
      --dark: #1d1d1d;
      --success: #2ecc71;
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

    .crud-actions {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
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

    /* Updated dashboard-cards styles for centering */
    .dashboard-cards {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 30px;
      transition: transform 0.3s ease;
      max-width: 1200px;
      width: 100%;
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

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .ongoing {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .pending {
      background-color: #fef5e7;
      color: #f39c12;
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

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .trip-details {
      margin-bottom: 20px;
    }

    .detail-row {
      display: flex;
      margin-bottom: 10px;
    }

    .detail-label {
      font-weight: 600;
      width: 150px;
      color: #2c3e50;
    }

    .detail-value {
      flex: 1;
    }

    .delete-confirmation {
      text-align: center;
      margin: 20px 0;
      font-size: 16px;
    }

    /* Success Modal Styles */
    .success-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1001;
      justify-content: center;
      align-items: center;
    }

    .success-modal-content {
      background-color: white;
      border-radius: 10px;
      width: 400px;
      max-width: 90%;
      padding: 30px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .success-icon {
      font-size: 48px;
      color: #2ecc71;
      margin-bottom: 20px;
    }

    .success-title {
      font-size: 24px;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 10px;
    }

    .success-message {
      color: #7f8c8d;
      margin-bottom: 20px;
    }

    .success-actions {
      display: flex;
      justify-content: center;
      gap: 10px;
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
        flex-direction: column;
        align-items: center;
      }
      
      .action-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <?php
  include 'config.php';
  
  // Initialize success message variable
  $successMessage = '';
  
  // Handle form submissions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_trip'])) {
      $customerId = $_POST['customer_id'];
      $pickupLocation = $_POST['pickup_location'];
      $dropoffLocation = $_POST['dropoff_location'];
      $pickupTime = $_POST['pickup_time'];
      $passengers = $_POST['passengers'] ?? 1;
      $specialRequests = $_POST['special_requests'] ?? '';
      $status = $_POST['status'] ?? 'pending';
      
      $sql = "INSERT INTO trips (customer_id, pickup_location, dropoff_location, pickup_time, passengers, special_requests, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
      $result = executeQuery($sql, [$customerId, $pickupLocation, $dropoffLocation, $pickupTime, $passengers, $specialRequests, $status]);
      
      if ($result) {
        $successMessage = 'Trip added successfully!';
      } else {
        $successMessage = 'Error adding trip.';
      }
    }
    
    if (isset($_POST['delete_trip'])) {
      $tripId = $_POST['trip_id'];
      $sql = "DELETE FROM trips WHERE trip_id = ?";
      $result = executeQuery($sql, [$tripId]);
      
      if ($result) {
        $successMessage = 'Trip deleted successfully!';
      } else {
        $successMessage = 'Error deleting trip.';
      }
    }
  }
  
  // Fetch trips with customer information
  $trips = executeQuery("
    SELECT t.*, c.first_name, c.last_name, c.phone, c.email 
    FROM trips t 
    JOIN customers c ON t.customer = c.customer_id
    ORDER BY t.pickup_time DESC
");
  // Fetch customers for dropdown
  $customers = executeQuery("SELECT * FROM customers");
  ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li class=""><a href="dtpm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li class=""><a href="DP.php"><i class="fas fa-user me-2"></i> Driver Performance</a></li>
      <li class="active"><a href="TH.php"><i class="fas fa-road me-2"></i> Trip History</a></li>
      <li><a href="CM.php"><i class="fas fa-check-circle me-2"></i> Compliance Monitoring</a></li>
      
    </ul>
    <div class="bottom-links">
      <a href="#"><i class="fas fa-bell me-2"></i> Notifications</a>
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="../index.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    

    <div class="dashboard-cards">
      <!-- Trip History Card -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Trip History</div>
        </div>
        <div class="trip-history">
          <table>
            <thead>
              <tr>
                <th>Trip ID</th>
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
              <?php if (!empty($trips)): ?>
                <?php foreach ($trips as $trip): ?>
                <tr>
                  <td>TRIP #<?php echo $trip['trip_id']; ?></td>
                  <td><?php echo $trip['first_name'] . ' ' . $trip['last_name']; ?></td>
                  <td><?php echo $trip['pickup_location']; ?></td>
                  <td><?php echo $trip['dropoff_location']; ?></td>
                  <td><?php echo date('Y-m-d H:i', strtotime($trip['pickup_time'])); ?></td>
                  <td><?php echo $trip['passengers']; ?></td>
                  <td>
                    <span class="status <?php echo strtolower($trip['status']); ?>">
                      <?php echo $trip['status']; ?>
                    </span>
                  </td>
                  <td class="action-buttons">
                    <button class="btn btn-view" onclick="viewTrip(<?php echo $trip['trip_id']; ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-delete" onclick="deleteTrip(<?php echo $trip['trip_id']; ?>, '<?php echo addslashes($trip['first_name'] . ' ' . $trip['last_name']); ?>', '<?php echo addslashes($trip['pickup_location'] . ' to ' . $trip['dropoff_location']); ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" style="text-align: center;">No trips found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Adding Trip -->
  <div class="modal" id="add-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Add Trip</div>
        <span class="close" onclick="closeModal('add-modal')">&times;</span>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label for="customer_id">Customer</label>
            <select id="customer_id" name="customer_id" required>
              <option value="">Select Customer</option>
              <?php foreach ($customers as $customer): ?>
              <option value="<?php echo $customer['customer_id']; ?>">
                <?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="pickup_location">Pickup Location</label>
            <input type="text" id="pickup_location" name="pickup_location" placeholder="Enter pickup location" required />
          </div>
          <div class="form-group">
            <label for="dropoff_location">Dropoff Location</label>
            <input type="text" id="dropoff_location" name="dropoff_location" placeholder="Enter dropoff location" required />
          </div>
          <div class="form-group">
            <label for="pickup_time">Pickup Time</label>
            <input type="datetime-local" id="pickup_time" name="pickup_time" required />
          </div>
          <div class="form-group">
            <label for="passengers">Number of Passengers</label>
            <input type="number" id="passengers" name="passengers" min="1" value="1" required />
          </div>
          <div class="form-group">
            <label for="special_requests">Special Requests</label>
            <textarea id="special_requests" name="special_requests" placeholder="Any special requests"></textarea>
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="pending">Pending</option>
              <option value="scheduled">Scheduled</option>
              <option value="dispatched">Dispatched</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="add_trip" class="btn btn-primary">Save</button>
          <button type="button" class="btn" onclick="closeModal('add-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal for Viewing Trip -->
  <div class="modal" id="view-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Trip Details</div>
        <span class="close" onclick="closeModal('view-modal')">&times;</span>
      </div>
      <div class="modal-body">
        <div class="trip-details" id="trip-details">
          <!-- Trip details will be loaded here via JavaScript -->
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn" onclick="closeModal('view-modal')">Close</button>
      </div>
    </div>
  </div>

  <!-- Modal for Deleting Trip -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Delete Trip</div>
        <span class="close" onclick="closeModal('delete-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="delete_trip_id" name="trip_id">
        <div class="delete-confirmation">
          <p>Are you sure you want to delete the trip for customer: <strong id="delete_customer_name"></strong>?</p>
          <p>Route: <strong id="delete_trip_route"></strong></p>
          <p class="warning-text">This action cannot be undone.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_trip" class="btn btn-delete">Delete</button>
          <button type="button" class="btn" onclick="closeModal('delete-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="success-modal" id="success-modal">
    <div class="success-modal-content">
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="success-title" id="success-title">
        <?php echo strpos($successMessage, 'Error') === false ? 'Success!' : 'Error!'; ?>
      </div>
      <div class="success-message" id="success-message">
        <?php echo $successMessage; ?>
      </div>
      <div class="success-actions">
        <button class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
      </div>
    </div>
  </div>

  <script>
    // Store trips data for viewing
    const trips = <?php echo json_encode($trips); ?>;

    function openModal(modalId) {
      if (modalId === "add-trip") {
        // Set current datetime as default for pickup_time
        document.getElementById('pickup_time').value = new Date().toISOString().slice(0, 16);
        document.getElementById("add-modal").style.display = "flex";
      }
    }

    function viewTrip(tripId) {
      const trip = trips.find(t => t.trip_id == tripId);
      if (!trip) return;
      
      const detailsContainer = document.getElementById("trip-details");
      
      // Format the trip details HTML
      detailsContainer.innerHTML = `
        <div class="detail-row">
          <div class="detail-label">Trip ID:</div>
          <div class="detail-value">TRIP #${trip.trip_id}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Customer:</div>
          <div class="detail-value">${trip.first_name} ${trip.last_name}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Phone:</div>
          <div class="detail-value">${trip.phone || 'N/A'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Email:</div>
          <div class="detail-value">${trip.email || 'N/A'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Route:</div>
          <div class="detail-value">${trip.pickup_location} to ${trip.dropoff_location}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Pickup Time:</div>
          <div class="detail-value">${new Date(trip.pickup_time).toLocaleString()}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Passengers:</div>
          <div class="detail-value">${trip.passengers || '1'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Special Requests:</div>
          <div class="detail-value">${trip.special_requests || 'None'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Status:</div>
          <div class="detail-value"><span class="status ${trip.status.toLowerCase()}">${trip.status}</span></div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Created At:</div>
          <div class="detail-value">${new Date(trip.created_at).toLocaleString()}</div>
        </div>
      `;
      
      document.getElementById("view-modal").style.display = "flex";
    }

    function deleteTrip(tripId, customerName, tripRoute) {
      document.getElementById("delete_trip_id").value = tripId;
      document.getElementById("delete_customer_name").textContent = customerName;
      document.getElementById("delete_trip_route").textContent = tripRoute;
      document.getElementById("delete-modal").style.display = "flex";
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = "none";
    }

    function closeSuccessModal() {
      document.getElementById("success-modal").style.display = "none";
    }

    window.onclick = function (event) {
      const modals = document.getElementsByClassName("modal");
      for (let i = 0; i < modals.length; i++) {
        if (event.target === modals[i]) {
          modals[i].style.display = "none";
        }
      }
      
      const successModal = document.getElementById("success-modal");
      if (event.target === successModal) {
        successModal.style.display = "none";
      }
    };

    // Show success modal if there's a success message
    <?php if (!empty($successMessage)): ?>
    window.onload = function() {
      const successModal = document.getElementById("success-modal");
      successModal.style.display = "flex";
    };
    <?php endif; ?>
  </script>
</body>
</html>