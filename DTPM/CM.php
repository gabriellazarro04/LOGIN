<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Compliance Monitoring</title>
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
      background-color: #8701bcff;
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

    .valid {
      background-color: #e7f7ef;
      color: #2ecc71;
    }

    .expired {
      background-color: #f9ebea;
      color: #e74c3c;
    }

    .pending-renewal {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .expiring-soon {
      background-color: #fef5e7;
      color: #f39c12;
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
  <?php
  // Include the config file which uses MySQLi
  include 'config.php';
  
  // Initialize success message variable
  $successMessage = '';
  $isError = false;
  
  // Handle form submissions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_compliance'])) {
      $driverId = $_POST['driver_id'];
      $recordType = $_POST['record_type'];
      $expiryDate = $_POST['expiry_date'];
      $status = $_POST['status'];
      $remarks = $_POST['remarks'];
      
      // Validate required fields
      if (empty($driverId) || empty($recordType) || empty($expiryDate) || empty($status)) {
        $successMessage = 'All required fields must be filled!';
        $isError = true;
      } else {
        $sql = "INSERT INTO compliance_records (driver_id, record_type, expiry_date, status, remarks) VALUES (?, ?, ?, ?, ?)";
        $result = executeQuery($sql, [$driverId, $recordType, $expiryDate, $status, $remarks]);
        
        if ($result !== false) {
          $successMessage = 'Compliance record added successfully!';
        } else {
          $successMessage = 'Error adding compliance record.';
          $isError = true;
        }
      }
    }
    
    if (isset($_POST['edit_compliance'])) {
      $complianceId = $_POST['compliance_id'];
      $driverId = $_POST['driver_id'];
      $recordType = $_POST['record_type'];
      $expiryDate = $_POST['expiry_date'];
      $status = $_POST['status'];
      $remarks = $_POST['remarks'];
      
      // Validate required fields
      if (empty($driverId) || empty($recordType) || empty($expiryDate) || empty($status)) {
        $successMessage = 'All required fields must be filled!';
        $isError = true;
      } else {
        $sql = "UPDATE compliance_records SET driver_id = ?, record_type = ?, expiry_date = ?, status = ?, remarks = ? WHERE compliance_id = ?";
        $result = executeQuery($sql, [$driverId, $recordType, $expiryDate, $status, $remarks, $complianceId]);
        
        if ($result !== false) {
          $successMessage = 'Compliance record updated successfully!';
        } else {
          $successMessage = 'Error updating compliance record.';
          $isError = true;
        }
      }
    }
    
    if (isset($_POST['delete_compliance'])) {
      $complianceId = $_POST['compliance_id'];
      $sql = "DELETE FROM compliance_records WHERE compliance_id = ?";
      $result = executeQuery($sql, [$complianceId]);
      
      if ($result !== false) {
        $successMessage = 'Compliance record deleted successfully!';
      } else {
        $successMessage = 'Error deleting compliance record.';
        $isError = true;
      }
    }
  }
  
  // Fetch compliance records with driver information
  $complianceRecords = executeQuery("
    SELECT c.*, d.first_name, d.last_name, d.license_number 
    FROM compliance_records c 
    JOIN drivers d ON c.driver_id = d.driver_id 
    ORDER BY c.expiry_date ASC
  ");
  
  // Ensure $complianceRecords is always an array
  if ($complianceRecords === false) {
    $complianceRecords = [];
    error_log("Failed to fetch compliance records from database");
  }
  
  // Fetch drivers for dropdown
  $drivers = executeQuery("SELECT * FROM drivers");
  
  // Ensure $drivers is always an array
  if ($drivers === false) {
    $drivers = [];
    error_log("Failed to fetch drivers from database");
  }
  ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
     <li class=""><a href="dtpm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li class=""><a href="DP.php"><i class="fas fa-user me-2"></i> Driver Performance</a></li>
      <li><a href="TH.php"><i class="fas fa-road me-2"></i> Trip History</a></li>
      <li class="active"><a href="CM.php"><i class="fas fa-check-circle me-2"></i> Compliance Monitoring</a></li>

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
      <button class="btn btn-primary" onclick="openModal('add-compliance')">
        <i class="fas fa-plus"></i> Add Compliance Record
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Compliance History</div>
        </div>
        <div class="compliance-history">
          <table>
            <thead>
              <tr>
                <th>Driver Name</th>
                <th>License No.</th>
                <th>Record Type</th>
                <th>Expiry Date</th>
                <th>Days Until Expiry</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($complianceRecords)): ?>
                <?php foreach ($complianceRecords as $record): 
                  $expiryDate = new DateTime($record['expiry_date']);
                  $currentDate = new DateTime();
                  $interval = $currentDate->diff($expiryDate);
                  $daysUntilExpiry = $interval->format('%R%a');
                  
                  // Determine status class based on record status and days until expiry
                  if ($record['status'] === 'Valid') {
                    if ($daysUntilExpiry <= 30 && $daysUntilExpiry >= 0) {
                      $statusClass = 'expiring-soon';
                    } else if ($daysUntilExpiry < 0) {
                      $statusClass = 'expired';
                    } else {
                      $statusClass = 'valid';
                    }
                  } else if ($record['status'] === 'Expired') {
                    $statusClass = 'expired';
                  } else {
                    $statusClass = 'pending-renewal';
                  }
                  
                  // Format days display
                  if ($daysUntilExpiry > 0) {
                    $daysDisplay = $daysUntilExpiry . ' days';
                  } else if ($daysUntilExpiry == 0) {
                    $daysDisplay = 'Today';
                  } else {
                    $daysDisplay = 'Expired ' . abs($daysUntilExpiry) . ' days ago';
                  }
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                  <td><?php echo htmlspecialchars($record['license_number']); ?></td>
                  <td><?php echo htmlspecialchars($record['record_type']); ?></td>
                  <td><?php echo htmlspecialchars($record['expiry_date']); ?></td>
                  <td><?php echo $daysDisplay; ?></td>
                  <td><span class="status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($record['status']); ?></span></td>
                  <td><?php echo htmlspecialchars($record['remarks'] ?? 'N/A'); ?></td>
                  <td>
                    <button class="btn btn-edit" onclick="editCompliance(<?php echo $record['compliance_id']; ?>)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="deleteCompliance(<?php echo $record['compliance_id']; ?>, '<?php echo addslashes($record['first_name'] . ' ' . $record['last_name']); ?>', '<?php echo addslashes($record['record_type']); ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" style="text-align: center;">No compliance records found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div class="modal" id="modal-form">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title" id="modal-title">Add Compliance Record</div>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      <form method="POST" id="compliance-form">
        <input type="hidden" id="compliance_id" name="compliance_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="driver_id">Driver *</label>
            <select id="driver_id" name="driver_id" required>
              <option value="">Select Driver</option>
              <?php if (!empty($drivers)): ?>
                <?php foreach ($drivers as $driver): ?>
                <option value="<?php echo $driver['driver_id']; ?>">
                  <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name'] . ' (' . $driver['license_number'] . ')'); ?>
                </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="record_type">Record Type *</label>
           <select id="record_type" name="record_type" required>
              <option value="">Select Type</option>
              <option value="License">License</option>
              <option value="Medical">Medical</option>
              <option value="Training">Training</option>
              <option value="Vehicle Inspection">Vehicle Inspection</option>
            </select>
          </div>
          <div class="form-group">
            <label for="expiry_date">Expiry Date *</label>
            <input type="date" id="expiry_date" name="expiry_date" required />
          </div>
          <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
              <option value="">Select Status</option>
              <option value="Valid">Valid</option>
              <option value="Expired">Expired</option>
              <option value="Pending Renewal">Pending Renewal</option>
            </select>
          </div>
          <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" placeholder="Enter remarks here..."></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" id="submit-button" name="add_compliance" class="btn btn-primary">Save</button>
          <button type="button" class="btn" onclick="closeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Delete Compliance Record</div>
        <span class="close" onclick="closeDeleteModal()">&times;</span>
      </div>
      <form method="POST" id="delete-form">
        <input type="hidden" id="delete_compliance_id" name="compliance_id" value="">
        <div class="delete-confirmation">
          <p>Are you sure you want to delete the compliance record for <span id="delete-driver-name" style="font-weight: bold;"></span> (<span id="delete-record-type" style="font-weight: bold;"></span>)?</p>
          <p>This action cannot be undone.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_compliance" class="btn btn-delete">Delete</button>
          <button type="button" class="btn" onclick="closeDeleteModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="success-modal" id="success-modal">
    <div class="success-modal-content">
      <div class="<?php echo $isError ? 'error-icon' : 'success-icon'; ?>">
        <i class="fas <?php echo $isError ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
      </div>
      <div class="success-title" id="success-title">
        <?php echo $isError ? 'Error!' : 'Success!'; ?>
      </div>
      <div class="success-message" id="success-message">
        <?php echo htmlspecialchars($successMessage); ?>
      </div>
      <div class="success-actions">
        <button class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Store compliance records data for editing
    const complianceRecords = <?php echo json_encode($complianceRecords); ?>;

    function openModal(type) {
      const modal = document.getElementById("modal-form");
      const title = document.getElementById("modal-title");
      const form = document.getElementById("compliance-form");
      const submitButton = document.getElementById("submit-button");

      if (type === "add-compliance") {
        title.textContent = "Add Compliance Record";
        form.reset();
        document.getElementById('compliance_id').value = '';
        submitButton.name = 'add_compliance';
        
        // Show modal with animation
        modal.style.display = "flex";
      }
    }

    function editCompliance(complianceId) {
      const record = complianceRecords.find(r => r.compliance_id == complianceId);
      if (!record) return;
      
      const modal = document.getElementById("modal-form");
      const title = document.getElementById("modal-title");
      const form = document.getElementById("compliance-form");
      const submitButton = document.getElementById("submit-button");
      
      title.textContent = "Edit Compliance Record";
      document.getElementById('compliance_id').value = record.compliance_id;
      document.getElementById('driver_id').value = record.driver_id;
      document.getElementById('record_type').value = record.record_type;
      document.getElementById('expiry_date').value = record.expiry_date;
      document.getElementById('status').value = record.status;
      document.getElementById('remarks').value = record.remarks || '';
      submitButton.name = 'edit_compliance';
      
      // Show modal with animation
      modal.style.display = "flex";
    }

    function deleteCompliance(complianceId, driverName, recordType) {
      const modal = document.getElementById("delete-modal");
      document.getElementById('delete_compliance_id').value = complianceId;
      document.getElementById('delete-driver-name').textContent = driverName;
      document.getElementById('delete-record-type').textContent = recordType;
      
      // Show modal with animation
      modal.style.display = "flex";
    }

    function closeModal() {
      const modal = document.getElementById("modal-form");
      modal.style.display = "none";
    }

    function closeDeleteModal() {
      const modal = document.getElementById("delete-modal");
      modal.style.display = "none";
    }

    function closeSuccessModal() {
      const successModal = document.getElementById("success-modal");
      successModal.style.display = "none";
    }

    window.onclick = function (event) {
      const modal = document.getElementById("modal-form");
      const deleteModal = document.getElementById("delete-modal");
      const successModal = document.getElementById("success-modal");
      
      if (event.target === modal) {
        closeModal();
      }
      if (event.target === deleteModal) {
        closeDeleteModal();
      }
      if (event.target === successModal) {
        closeSuccessModal();
      }
    };

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal();
        closeDeleteModal();
        closeSuccessModal();
      }
    });

    // Show success modal if there's a success message
    <?php if (!empty($successMessage)): ?>
    window.onload = function() {
      const successModal = document.getElementById("success-modal");
      successModal.style.display = "flex";
    };
    <?php endif; ?>

    // Form validation
    document.getElementById('compliance-form').addEventListener('submit', function(e) {
      const driverId = document.getElementById('driver_id').value;
      const recordType = document.getElementById('record_type').value;
      const expiryDate = document.getElementById('expiry_date').value;
      const status = document.getElementById('status').value;
      
      if (!driverId || !recordType || !expiryDate || !status) {
        e.preventDefault();
        alert('Please fill in all required fields (marked with *)');
        return false;
      }
    });
  </script>
</body>
</html>