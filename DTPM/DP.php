<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Driver and Trip Performance Monitoring</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #3498db;
      --accent: #e74c3c;
      --light: #ecf0f1;
      --dark: #1d1d1d;
      --success: #2ecc71;
      --available: #2ecc71;
      --on_trip: #f39c12;
      --off_duty: #e74c3c;
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
      max-width: 1000px;
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
      text-transform: capitalize;
    }

    .available {
      background-color: #e7f7ef;
      color: var(--available);
    }

    .on_trip {
      background-color: #fef5e7;
      color: var(--on_trip);
    }

    .off_duty {
      background-color: #f9ebea;
      color: var(--off_duty);
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
      width: 500px;
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

    .form-group input:invalid,
    .form-group select:invalid {
      border-color: #e74c3c;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .action-buttons {
      display: flex;
      gap: 5px;
    }

    .action-buttons form {
      display: inline;
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

    .error-icon {
      font-size: 48px;
      color: #e74c3c;
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

    .warning-text {
      color: #e74c3c;
      font-weight: 600;
      margin-top: 10px;
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
  $isError = false;
  
  // Handle form submissions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_driver'])) {
      $firstName = trim($_POST['first_name']);
      $lastName = trim($_POST['last_name']);
      $licenseNumber = trim($_POST['license_number']);
      $email = trim($_POST['email']);
      $phone = trim($_POST['phone']);
      
      // Validate required fields
      if (empty($firstName) || empty($lastName) || empty($licenseNumber)) {
        $successMessage = 'First name, last name, and license number are required!';
        $isError = true;
      } else {
        $sql = "INSERT INTO drivers (first_name, last_name, license_number, email, phone) VALUES (?, ?, ?, ?, ?)";
        $result = executeQuery($sql, [$firstName, $lastName, $licenseNumber, $email, $phone]);
        
        if ($result !== false) {
          $successMessage = 'Driver added successfully!';
        } else {
          $successMessage = 'Error adding driver. License number might already exist.';
          $isError = true;
        }
      }
    }
  };
    if (isset($_POST['edit_driver'])) {
      $driverId = $_POST['driver_id'];
      $firstName = trim($_POST['first_name']);
      $lastName = trim($_POST['last_name']);
      $licenseNumber = trim($_POST['license_number']);
      $email = trim($_POST['email']);
      $phone = trim($_POST['phone']);
      $status = $_POST['status'];
      
      // Validate required fields
      if (empty($firstName) || empty($lastName) || empty($licenseNumber)) {
        $successMessage = 'First name, last name, and license number are required!';
        $isError = true;
      } else {
        $sql = "UPDATE drivers SET first_name = ?, last_name = ?, license_number = ?, email = ?, phone = ?, status = ? WHERE driver_id = ?";
        $result = executeQuery($sql, [$firstName, $lastName, $licenseNumber, $email, $phone, $status, $driverId]);
        
        if ($result !== false) {
          $successMessage = 'Driver updated successfully!';
        } else {
          $successMessage = 'Error updating driver. License number might already exist.';
          $isError = true;
        }
      }
    }
    
   if (isset($_POST['delete_driver'])) {
  $driverId = $_POST['driver_id'];

  // Check if driver exists
  $driverExists = fetchSingle("SELECT driver_id FROM drivers WHERE driver_id = ?", [$driverId]);
  if (!$driverExists) {
    $successMessage = 'Driver not found or already deleted.';
    $isError = true;
  } else {
    // Check if driver has any assigned records before deleting
    $checkSql = "SELECT 
        (SELECT COUNT(*) FROM dispatches WHERE driver_id = ?) as dispatch_count,
        (SELECT COUNT(*) FROM schedules WHERE driver_id = ?) as schedule_count,
        (SELECT COUNT(*) FROM vehicle_assignments WHERE driver_id = ?) as assignment_count";
    $checkResult = executeQuery($checkSql, [$driverId, $driverId, $driverId]);

    if ($checkResult && ($checkResult[0]['dispatch_count'] > 0 || $checkResult[0]['schedule_count'] > 0 || $checkResult[0]['assignment_count'] > 0)) {
      $successMessage = 'Cannot delete driver. Driver has assigned dispatches, schedules, or vehicle assignments.';
      $isError = true;
    } else {
      // Now delete
      $sql = "DELETE FROM drivers WHERE driver_id = ?";
      $result = executeQuery($sql, [$driverId]);
      if ($result) {
        $successMessage = 'Driver deleted successfully!';
        $isError = false;
      } else {
        $successMessage = 'Error deleting driver. Please try again.';
        $isError = true;
      }
    }
  }
}
  
  // Fetch drivers from database
  $drivers = executeQuery("SELECT * FROM drivers ORDER BY driver_id DESC");
  ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li class=""><a href="dtpm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li class="active"><a href="DP.php"><i class="fas fa-user me-2"></i> Driver Performance</a></li>
      <li><a href="TH.php"><i class="fas fa-road me-2"></i> Trip History</a></li>
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
    <div class="crud-actions">
      <button class="btn btn-primary" onclick="openModal('add')">
        <i class="fas fa-plus"></i> Add New Driver
      </button>
    </div>

    <div class="dashboard-cards">
      <!-- Driver List Card -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Driver List</div>
        </div>
        <div class="trip-history">
          <table>
            <thead>
              <tr>
                <th>Driver ID</th>
                <th>Name</th>
                <th>License No.</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($drivers)): ?>
                <?php foreach ($drivers as $driver): 
                  // Convert status to display format
                  $statusDisplay = str_replace('_', ' ', $driver['status']);
                  $statusDisplay = ucwords($statusDisplay);
                ?>
                <tr>
                  <td>#DRV-<?php echo $driver['driver_id']; ?></td>
                  <td><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></td>
                  <td><?php echo htmlspecialchars($driver['license_number']); ?></td>
                  <td><?php echo htmlspecialchars($driver['email']); ?></td>
                  <td><?php echo htmlspecialchars($driver['phone']); ?></td>
                  <td><span class="status <?php echo $driver['status']; ?>"><?php echo $statusDisplay; ?></span></td>
                  <td class="action-buttons">
                    <button class="btn btn-edit" onclick="openEditModal(<?php echo $driver['driver_id']; ?>, '<?php echo addslashes($driver['first_name']); ?>', '<?php echo addslashes($driver['last_name']); ?>', '<?php echo addslashes($driver['license_number']); ?>', '<?php echo addslashes($driver['email']); ?>', '<?php echo addslashes($driver['phone']); ?>', '<?php echo addslashes($driver['status']); ?>')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="openDeleteModal(<?php echo $driver['driver_id']; ?>, '<?php echo addslashes($driver['first_name'] . ' ' . $driver['last_name']); ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align: center;">No drivers found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Adding Driver -->
  <div class="modal" id="add-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Add Driver</div>
        <span class="close" onclick="closeModal('add-modal')">&times;</span>
      </div>
      <form method="POST" onsubmit="return validateAddForm()">
        <div class="modal-body">
          <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required />
          </div>
          <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required />
          </div>
          <div class="form-group">
            <label for="license_number">License Number *</label>
            <input type="text" id="license_number" name="license_number" placeholder="Enter license number" required />
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email" />
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" placeholder="Enter phone number" />
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="add_driver" class="btn btn-primary">Save</button>
          <button type="button" class="btn" onclick="closeModal('add-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal for Editing Driver -->
  <div class="modal" id="edit-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Edit Driver</div>
        <span class="close" onclick="closeModal('edit-modal')">&times;</span>
      </div>
      <form method="POST" onsubmit="return validateEditForm()">
        <input type="hidden" id="edit_driver_id" name="driver_id">
        <div class="modal-body">
          <div class="form-group">
            <label for="edit_first_name">First Name *</label>
            <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name" required />
          </div>
          <div class="form-group">
            <label for="edit_last_name">Last Name *</label>
            <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name" required />
          </div>
          <div class="form-group">
            <label for="edit_license_number">License Number *</label>
            <input type="text" id="edit_license_number" name="license_number" placeholder="Enter license number" required />
          </div>
          <div class="form-group">
            <label for="edit_email">Email</label>
            <input type="email" id="edit_email" name="email" placeholder="Enter email" />
          </div>
          <div class="form-group">
            <label for="edit_phone">Phone</label>
            <input type="text" id="edit_phone" name="phone" placeholder="Enter phone number" />
          </div>
          <div class="form-group">
            <label for="edit_status">Status *</label>
            <select id="edit_status" name="status" required>
              <option value="available">Available</option>
              <option value="on_trip">On Trip</option>
              <option value="off_duty">Off Duty</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="edit_driver" class="btn btn-primary">Update</button>
          <button type="button" class="btn" onclick="closeModal('edit-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal for Deleting Driver -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Confirm Deletion</div>
        <span class="close" onclick="closeModal('delete-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="delete_driver_id" name="driver_id">
        <div class="modal-body">
          <p>Are you sure you want to delete driver: <strong id="delete_driver_name"></strong>?</p>
          <p class="warning-text">This action cannot be undone. If the driver has assigned trips, deletion will fail.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_driver" class="btn btn-delete">Delete</button>
          <button type="button" class="btn" onclick="closeModal('delete-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success/Error Modal -->
  <div class="success-modal" id="success-modal">
    <div class="success-modal-content">
      <div class="<?php echo $isError ? 'error-icon' : 'success-icon'; ?>">
        <i class="fas <?php echo $isError ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
      </div>
      <div class="success-title" id="success-title">
        <?php echo $isError ? 'Error!' : 'Success!'; ?>
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
    function openModal(type) {
      if (type === "add") {
        document.getElementById("add-modal").style.display = "flex";
      }
    }

    function openEditModal(driverId, firstName, lastName, licenseNumber, email, phone, status) {
      document.getElementById("edit_driver_id").value = driverId;
      document.getElementById("edit_first_name").value = firstName;
      document.getElementById("edit_last_name").value = lastName;
      document.getElementById("edit_license_number").value = licenseNumber;
      document.getElementById("edit_email").value = email;
      document.getElementById("edit_phone").value = phone;
      document.getElementById("edit_status").value = status;
      document.getElementById("edit-modal").style.display = "flex";
    }

    function openDeleteModal(driverId, driverName) {
      document.getElementById("delete_driver_id").value = driverId;
      document.getElementById("delete_driver_name").textContent = driverName;
      document.getElementById("delete-modal").style.display = "flex";
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = "none";
    }

    function closeSuccessModal() {
      document.getElementById("success-modal").style.display = "none";
    }

    function validateAddForm() {
      const firstName = document.getElementById('first_name').value.trim();
      const lastName = document.getElementById('last_name').value.trim();
      const licenseNumber = document.getElementById('license_number').value.trim();
      
      if (!firstName || !lastName || !licenseNumber) {
        alert('Please fill in all required fields (marked with *)');
        return false;
      }
      
      return true;
    }
    
    function validateEditForm() {
      const firstName = document.getElementById('edit_first_name').value.trim();
      const lastName = document.getElementById('edit_last_name').value.trim();
      const licenseNumber = document.getElementById('edit_license_number').value.trim();
      
      if (!firstName || !lastName || !licenseNumber) {
        alert('Please fill in all required fields (marked with *)');
        return false;
      }
      
      return true;
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

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal('add-modal');
        closeModal('edit-modal');
        closeModal('delete-modal');
        closeSuccessModal();
      }
    });

    // Show success/error modal if there's a message
    <?php if (!empty($successMessage)): ?>
    window.onload = function() {
      const successModal = document.getElementById("success-modal");
      successModal.style.display = "flex";
    };
    <?php endif; ?>
  </script>
</body>
</html>