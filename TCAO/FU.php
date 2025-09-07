<?php 
include 'config.php';

// Initialize success message variable
$successMessage = '';
$isError = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_fuel'])) {
        // Add new fuel record
        $date = $_POST['date'];
        $vehicle_id = $_POST['vehicle_id'];
        $fuel_type = $_POST['fuel_type'];
        $liters = $_POST['liters'];
        $cost = $_POST['cost'];
        $odometer = $_POST['odometer'];
        
        $sql = "INSERT INTO fuel_usage (date, vehicle_id, fuel_type, liters, cost, odometer_reading) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $result = executeQuery($sql, [$date, $vehicle_id, $fuel_type, $liters, $cost, $odometer]);
        
        if ($result !== false) {
            $successMessage = 'Fuel record added successfully!';
        } else {
            $successMessage = 'Error adding fuel record!';
            $isError = true;
        }
    }
    
    if (isset($_POST['edit_fuel'])) {
        // Edit existing fuel record
        $fuel_id = $_POST['fuel_id'];
        $date = $_POST['date'];
        $vehicle_id = $_POST['vehicle_id'];
        $fuel_type = $_POST['fuel_type'];
        $liters = $_POST['liters'];
        $cost = $_POST['cost'];
        $odometer = $_POST['odometer'];
        
        $sql = "UPDATE fuel_usage SET date=?, vehicle_id=?, fuel_type=?, liters=?, cost=?, odometer_reading=?
                WHERE id=?";
        
        $result = executeQuery($sql, [$date, $vehicle_id, $fuel_type, $liters, $cost, $odometer, $fuel_id]);
        
        if ($result !== false) {
            $successMessage = 'Fuel record updated successfully!';
        } else {
            $successMessage = 'Error updating fuel record!';
            $isError = true;
        }
    }
    
    if (isset($_POST['delete_fuel'])) {
        // Delete fuel record
        $fuel_id = $_POST['fuel_id'];
        
        $sql = "DELETE FROM fuel_usage WHERE id=?";
        $result = executeQuery($sql, [$fuel_id]);
        
        if ($result !== false) {
            $successMessage = 'Fuel record deleted successfully!';
        } else {
            $successMessage = 'Error deleting fuel record!';
            $isError = true;
        }
    }
}

// Fetch vehicles for dropdown
$vehicles = executeQuery("SELECT * FROM vehicles ORDER BY make, model");

// Fetch fuel usage data
$fuel_data = executeQuery("SELECT fu.*, v.make, v.model, v.license_plate 
                          FROM fuel_usage fu 
                          JOIN vehicles v ON fu.vehicle_id = v.vehicle_id 
                          ORDER BY fu.date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trip Cost Analysis - Fuel Usage</title>
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

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .expired {
      background-color: #fdecea;
      color: #e74c3c;
    }

    .expiring-soon {
      background-color: #fef5e7;
      color: #f39c12;
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

    .summary-cards {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .summary-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      flex: 1;
      text-align: center;
    }

    .summary-card h3 {
      margin-top: 0;
      color: #2c3e50;
    }

    .summary-card .value {
      font-size: 24px;
      font-weight: bold;
      color: #9a66ff;
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

      .summary-cards {
        flex-direction: column;
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
      <li class="active"><a href="FU.php"><i class="fas fa-gas-pump me-2"></i> Fuel Usage </a></li>
      <li class=""><a href="TF.php"><i class="fas fa-arrows-alt"></i> Toll Fees </a></li>
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
      <button class="btn btn-primary" onclick="openModal('add-fuel')">
        <i class="fas fa-plus"></i> Add Fuel Record
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Fuel Usage History</div>
        </div>
        <div class="fuel-history">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Vehicle</th>
                <th>Fuel Type</th>
                <th>Liters</th>
                <th>Cost</th>
                <th>Odometer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($fuel_data)): ?>
                <?php foreach ($fuel_data as $record): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                    <td>
                      <?php echo htmlspecialchars($record['make'] . ' ' . $record['model'] . ' (' . $record['license_plate'] . ')'); ?>
                    </td>
                    <td><?php echo htmlspecialchars(ucfirst($record['fuel_type'])); ?></td>
                    <td><?php echo htmlspecialchars($record['liters']); ?> L</td>
                    <td>₱<?php echo number_format($record['cost'], 2); ?></td>
                    <td><?php echo number_format($record['odometer_reading']); ?> km</td>
                    <td>
                      <button class='btn btn-edit' onclick='editFuel(<?php echo $record['id']; ?>)'>
                        <i class='fas fa-edit'></i>
                      </button>
                      <button class='btn btn-delete' onclick='deleteFuel(<?php echo $record['id']; ?>, "<?php echo addslashes($record['make'] . ' ' . $record['model'] . ' (' . $record['license_plate'] . ')'); ?>", "<?php echo $record['date']; ?>")'>
                        <i class='fas fa-trash'></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan='7' style='text-align: center;'>No fuel records found</td></tr>
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
        <div class="modal-title" id="modal-title">Add Fuel Record</div>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      <form method="POST" id="fuel-form">
        <input type="hidden" id="fuel_id" name="fuel_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="form-group">
            <label for="vehicle_id">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" required>
              <option value="">Select Vehicle</option>
              <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?php echo $vehicle['vehicle_id']; ?>">
                  <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="fuel_type">Fuel Type</label>
            <select id="fuel_type" name="fuel_type" required>
              <option value="gasoline">Gasoline</option>
              <option value="diesel">Diesel</option>
              <option value="electric">Electric</option>
              <option value="hybrid">Hybrid</option>
              <option value="cng">CNG</option>
            </select>
          </div>
          <div class="form-group">
            <label for="liters">Liters</label>
            <input type="number" id="liters" name="liters" step="0.1" min="0" required>
          </div>
          <div class="form-group">
            <label for="cost">Total Cost (₱)</label>
            <input type="number" id="cost" name="cost" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label for="odometer">Odometer Reading (km)</label>
            <input type="number" id="odometer" name="odometer" min="0" required>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" id="submit-button" name="add_fuel" class="btn btn-primary">Save</button>
          <button type="button" class="btn" onclick="closeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Delete Fuel Record</div>
        <span class="close" onclick="closeDeleteModal()">&times;</span>
      </div>
      <form method="POST" id="delete-form">
        <input type="hidden" id="delete_fuel_id" name="fuel_id" value="">
        <div class="delete-confirmation">
          <p>Are you sure you want to delete the fuel record for <span id="delete-vehicle-info" style="font-weight: bold;"></span> on <span id="delete-date" style="font-weight: bold;"></span>?</p>
          <p>This action cannot be undone.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_fuel" class="btn btn-delete">Delete</button>
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

  <script>
    function openModal(type) {
      const modal = document.getElementById("modal-form");
      const title = document.getElementById("modal-title");
      const form = document.getElementById("fuel-form");
      const submitButton = document.getElementById("submit-button");

      if (type === "add-fuel") {
        title.textContent = "Add Fuel Record";
        form.reset();
        document.getElementById('fuel_id').value = '';
        document.getElementById('date').value = new Date().toISOString().split('T')[0];
        submitButton.name = 'add_fuel';
        modal.style.display = "flex";
      }
    }

    function editFuel(fuelId) {
      // In a real implementation, you would fetch the data via AJAX
      // For now, we'll redirect to a page with the ID as a parameter
      window.location.href = 'FU.php?edit_id=' + fuelId;
    }

    function deleteFuel(fuelId, vehicleInfo, date) {
      const modal = document.getElementById("delete-modal");
      document.getElementById('delete_fuel_id').value = fuelId;
      document.getElementById('delete-vehicle-info').textContent = vehicleInfo;
      document.getElementById('delete-date').textContent = date;
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
  </script>
</body>
</html>