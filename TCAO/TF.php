<?php 
include 'config.php';

// Initialize success message variable
$successMessage = '';
$isError = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_toll'])) {
        // Add new toll fee using prepared statements
        $toll_name = $_POST['toll_name'];
        $vehicle_type = $_POST['vehicle_type'];
        $fee_amount = $_POST['fee_amount'];
        $effective_date = $_POST['effective_date'];
        $status = $_POST['status'];
        
        $sql = "INSERT INTO toll_fees (toll_name, vehicle_type, fee_amount, effective_date, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $result = executeQuery($sql, [$toll_name, $vehicle_type, $fee_amount, $effective_date, $status]);
        
        if ($result !== false) {
            $successMessage = 'Toll fee added successfully!';
        } else {
            $successMessage = 'Error adding toll fee!';
            $isError = true;
        }
    }
    
    if (isset($_POST['edit_toll'])) {
        // Edit existing toll fee using prepared statements
        $toll_id = $_POST['toll_id'];
        $toll_name = $_POST['toll_name'];
        $vehicle_type = $_POST['vehicle_type'];
        $fee_amount = $_POST['fee_amount'];
        $effective_date = $_POST['effective_date'];
        $status = $_POST['status'];
        
        $sql = "UPDATE toll_fees SET toll_name=?, vehicle_type=?, fee_amount=?, effective_date=?, status=?
                WHERE id=?";
        
        $result = executeQuery($sql, [$toll_name, $vehicle_type, $fee_amount, $effective_date, $status, $toll_id]);
        
        if ($result !== false) {
            $successMessage = 'Toll fee updated successfully!';
        } else {
            $successMessage = 'Error updating toll fee!';
            $isError = true;
        }
    }
    
    if (isset($_POST['delete_toll'])) {
        // Delete toll fee using prepared statements
        $toll_id = $_POST['toll_id'];
        
        $sql = "DELETE FROM toll_fees WHERE id=?";
        $result = executeQuery($sql, [$toll_id]);
        
        if ($result !== false) {
            $successMessage = 'Toll fee deleted successfully!';
        } else {
            $successMessage = 'Error deleting toll fee!';
            $isError = true;
        }
    }
}

// Fetch toll fees data
$toll_query = "SELECT * FROM toll_fees ORDER BY effective_date DESC";
$toll_result = executeQuery($toll_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Toll Fees Management</title>
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

    body {
      background-color: #f5f7fa;
      color: #333;
      display: flex;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
      background-color: #9a66ff;
      color: white;
    }

    .btn-edit:hover {
      background-color: #723edaff;
    }

    .btn-delete {
      background-color: #e74c3c;
      color: white;
    }

    .btn-delete:hover {
      background-color: #c0392b;
    }

    .dashboard-cards {
      margin-top: 60px;
      display: flex;
      margin-left:40px;
      margin-right: 90px;
      flex-direction: column;
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 30px;
      transition: transform 0.3s ease;
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

    .active {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .inactive {
      background-color: #fdecea;
      color: #e74c3c;
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

      .dashboard-cards {
        flex-direction: column;
        align-items: center;
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
     <li><a href="tcao-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li class=""><a href="FU.php"><i class="fas fa-gas-pump me-2"></i> Fuel Usage </a></li>
      <li class="active"><a href="TF.php"><i class="fas fa-arrows-alt"></i> Toll Fees </a></li>
      <li class=""><a href="TE.php"><i class="fas fa-bolt"></i> Trip Expenses </a></li>
      <li class=""><a href="CO.php"><i class="fas fa-money-bill"></i> Cost Optimization </a></li>
      
    </ul>
    <div class="bottom-links">
      <a href="#"><i class="fas fa-bell me-2"></i> Notifications</a>
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="../admin-dashboard.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="crud-actions">
      <button class="btn btn-primary" onclick="openModal('add-toll')">
        <i class="fas fa-plus"></i> Add Toll Fee
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Toll Fees Management</div>
        </div>
        <div class="toll-fees-table">
          <table>
            <thead>
              <tr>
                <th>Toll Name</th>
                <th>Vehicle Type</th>
                <th>Fee Amount (₱)</th>
                <th>Effective Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($toll_result && mysqli_num_rows($toll_result) > 0) {
                  while($row = mysqli_fetch_assoc($toll_result)) {
                      echo "<tr>
                          <td>{$row['toll_name']}</td>
                          <td>{$row['vehicle_type']}</td>
                          <td>₱" . number_format($row['fee_amount'], 2) . "</td>
                          <td>{$row['effective_date']}</td>
                          <td><span class='status " . strtolower($row['status']) . "'>{$row['status']}</span></td>
                          <td>
                            <button class='btn btn-edit' onclick='editToll({$row['id']})'>
                              <i class='fas fa-edit'></i>
                            </button>
                            <button class='btn btn-delete' onclick='deleteToll({$row['id']}, \"{$row['toll_name']}\", \"{$row['vehicle_type']}\")'>
                              <i class='fas fa-trash'></i>
                            </button>
                          </td>
                      </tr>";
                  }
              } else {
                  echo "<tr><td colspan='6' style='text-align: center;'>No toll fees found</td></tr>";
              }
              ?>
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
        <div class="modal-title" id="modal-title">Add Toll Fee</div>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      <form method="POST" id="toll-form">
        <input type="hidden" id="toll_id" name="toll_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="toll_name">Toll Name</label>
            <input type="text" id="toll_name" name="toll_name" required placeholder="Enter toll name">
          </div>
          <div class="form-group">
  <label for="vehicle_type">Vehicle Type</label>
  <select id="vehicle_type" name="vehicle_type" required>
    <option value="">Select Vehicle Type</option>
    <option value="car">Car</option>
    <option value="suv">SUV</option>
    <option value="van">Van</option>
    <option value="motor">Motorcycle</option>
  </select>
</div>
          <div class="form-group">
            <label for="fee_amount">Fee Amount (₱)</label>
            <input type="number" id="fee_amount" name="fee_amount" step="0.01" min="0" required placeholder="0.00">
          </div>
          <div class="form-group">
            <label for="effective_date">Effective Date</label>
            <input type="date" id="effective_date" name="effective_date" required>
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" id="submit-button" name="add_toll" class="btn btn-primary">Save</button>
          <button type="button" class="btn" onclick="closeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Delete Toll Fee</div>
        <span class="close" onclick="closeDeleteModal()">&times;</span>
      </div>
      <form method="POST" id="delete-form">
        <input type="hidden" id="delete_toll_id" name="toll_id" value="">
        <div class="delete-confirmation">
          <p>Are you sure you want to delete the toll fee for <span id="delete-toll-name" style="font-weight: bold;"></span> (<span id="delete-vehicle-type" style="font-weight: bold;"></span>)?</p>
          <p>This action cannot be undone.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_toll" class="btn btn-delete">Delete</button>
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
        <?php echo $successMessage; ?>
      </div>
      <div class="success-actions">
        <button class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    function openModal(type) {
      const modal = document.getElementById("modal-form");
      const title = document.getElementById("modal-title");
      const form = document.getElementById("toll-form");
      const submitButton = document.getElementById("submit-button");

      if (type === "add-toll") {
        title.textContent = "Add Toll Fee";
        form.reset();
        document.getElementById('toll_id').value = '';
        document.getElementById('effective_date').value = new Date().toISOString().split('T')[0];
        submitButton.name = 'add_toll';
        modal.style.display = "flex";
      }
    }

    function editToll(tollId) {
      // Fetch toll fee data via AJAX using the config.php endpoint
      fetch('config.php?action=get_toll_record&id=' + tollId)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            alert(data.error);
            return;
          }
          
          const modal = document.getElementById("modal-form");
          const title = document.getElementById("modal-title");
          const form = document.getElementById("toll-form");
          const submitButton = document.getElementById("submit-button");
          
          title.textContent = "Edit Toll Fee";
          document.getElementById('toll_id').value = data.id;
          document.getElementById('toll_name').value = data.toll_name;
          document.getElementById('vehicle_type').value = data.vehicle_type;
          document.getElementById('fee_amount').value = data.fee_amount;
          document.getElementById('effective_date').value = data.effective_date;
          document.getElementById('status').value = data.status;
          submitButton.name = 'edit_toll';
          
          modal.style.display = "flex";
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error fetching toll fee data');
        });
    }

    function deleteToll(tollId, tollName, vehicleType) {
      const modal = document.getElementById("delete-modal");
      document.getElementById('delete_toll_id').value = tollId;
      document.getElementById('delete-toll-name').textContent = tollName;
      document.getElementById('delete-vehicle-type').textContent = vehicleType;
      modal.style.display = "flex";
    }

    function closeModal() {
      document.getElementById("modal-form").style.display = "none";
    }

    function closeDeleteModal() {
      document.getElementById("delete-modal").style.display = "none";
    }

    function closeSuccessModal() {
      document.getElementById("success-modal").style.display = "none";
    }

    window.onclick = function (event) {
      const modal = document.getElementById("modal-form");
      const deleteModal = document.getElementById("delete-modal");
      const successModal = document.getElementById("success-modal");
      
      if (event.target === modal) {
        modal.style.display = "none";
      }
      if (event.target === deleteModal) {
        deleteModal.style.display = "none";
      }
      if (event.target === successModal) {
        successModal.style.display = "none";
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

    // Add this script to each page to enable real-time updates
    function enableRealTimeUpdates() {
      // Update page every 30 seconds
      setInterval(() => {
        location.reload();
      }, 30000);
    }

    // Call this function when the page loads
    document.addEventListener('DOMContentLoaded', function() {
      enableRealTimeUpdates();
    });
  </script>
</body>
</html>