<?php
// VRM.php - Vehicle Registration Management
include 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_vehicle'])) {
        $make = $_POST['make'];
        $model = $_POST['model'];
        $year = $_POST['year'];
        $license_plate = $_POST['license_plate'];
        $capacity = $_POST['capacity'];
        $vehicle_type = $_POST['vehicle_type'];
        $fuel_type = $_POST['fuel_type']; // Added fuel_type
        $status = $_POST['status'];
        
        $result = insertAndGetId(
            "INSERT INTO vehicles (make, model, year, license_plate, capacity, vehicle_type, fuel_type, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)", // Added fuel_type
            [$make, $model, $year, $license_plate, $capacity, $vehicle_type, $fuel_type, $status] // Added fuel_type
        );
        
        if ($result) {
            $success = "Vehicle added successfully!";
        } else {
            $error = "Error adding vehicle!";
        }
    }
    
    if (isset($_POST['update_vehicle'])) {
        $vehicle_id = $_POST['vehicle_id'];
        $make = $_POST['make'];
        $model = $_POST['model'];
        $year = $_POST['year'];
        $license_plate = $_POST['license_plate'];
        $capacity = $_POST['capacity'];
        $vehicle_type = $_POST['vehicle_type'];
        $fuel_type = $_POST['fuel_type']; // Added fuel_type
        $status = $_POST['status'];
        
        $result = executeQuery(
            "UPDATE vehicles SET make=?, model=?, year=?, license_plate=?, capacity=?, vehicle_type=?, fuel_type=?, status=? 
             WHERE vehicle_id=?", // Added fuel_type
            [$make, $model, $year, $license_plate, $capacity, $vehicle_type, $fuel_type, $status, $vehicle_id] // Added fuel_type
        );
        
        if ($result) {
            $success = "Vehicle updated successfully!";
        } else {
            $error = "Error updating vehicle!";
        }
    }
    
    if (isset($_POST['delete_vehicle'])) {
        $vehicle_id = $_POST['vehicle_id'];
        
        $result = executeQuery(
            "DELETE FROM vehicles WHERE vehicle_id=?",
            [$vehicle_id]
        );
        
        if ($result) {
            $success = "Vehicle deleted successfully!";
        } else {
            $error = "Error deleting vehicle!";
        }
    }
}

// Get all vehicles
$vehicles = executeQuery("SELECT * FROM vehicles ORDER BY vehicle_id DESC");

// Get vehicle data for editing if requested
$edit_vehicle_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_vehicle = executeQuery(
        "SELECT * FROM vehicles WHERE vehicle_id = ?", 
        [$edit_id]
    );
    if (!empty($edit_vehicle)) {
        $edit_vehicle_data = $edit_vehicle[0];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vehicle Registration - VRDS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* Same CSS as DS.php but with FVM-specific adjustments */
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

    .available {
      background-color: #8701bcff;
      color: white;
    }

    .in-use {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .maintenance {
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
      <li><a href="fvm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
      <li class="active"><a href="VR.php"><i class="fas fa-car me-2"></i> Vehicle Registration</a></li>
      <li><a href="SC.php"><i class="fas fa-calendar-alt me-2"></i> Scheduling</a></li>
      <li><a href="MA.php"><i class="fas fa-tools me-2"></i> Maintenance</a></li>
      <li><a href="VA.php"><i class="fas fa-tasks me-2"></i> Vehicle Assignment</a></li>
      <li><a href="VM.php"><i class="fas fa-map-marker-alt me-2"></i> Vehicle Monitoring</a></li>
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
      <div class="page-title">Vehicle Registration Management</div>
    </div>

    <?php if (isset($success)): ?>
    <div class="success-message">
      <?php echo $success; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="error-message">
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="crud-actions">
      <button class="btn btn-primary" onclick="openModal('add-vehicle')">
        <i class="fas fa-plus"></i> Add New Vehicle
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">All Vehicles</div>
        </div>
        <div class="vehicle-list">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Make & Model</th>
                <th>Year</th>
                <th>License Plate</th>
                <th>Capacity</th>
                <th>Type</th>
                <th>Fuel Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vehicles as $vehicle): 
                $statusClass = str_replace('_', '-', $vehicle['status']);
              ?>
              <tr>
                <td>#V-<?php echo $vehicle['vehicle_id']; ?></td>
                <td><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></td>
                <td><?php echo $vehicle['year']; ?></td>
                <td><?php echo $vehicle['license_plate']; ?></td>
                <td><?php echo $vehicle['capacity']; ?> seats</td>
                <td><?php echo strtoupper($vehicle['vehicle_type']); ?></td>
                <td><?php echo !empty($vehicle['fuel_type']) ? ucfirst($vehicle['fuel_type']) : 'N/A'; ?></td>
                <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $vehicle['status'])); ?></span></td>
                <td class="action-buttons">
                  <button class="btn btn-edit" onclick="editVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-delete" onclick="deleteVehicle(<?php echo $vehicle['vehicle_id']; ?>, '<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>')">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Vehicle Modal -->
  <div class="modal" id="add-vehicle-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Add New Vehicle</div>
        <span class="close" onclick="closeModal('add-vehicle-modal')">&times;</span>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label for="make">Make</label>
              <input type="text" id="make" name="make" required />
            </div>
            <div class="form-group">
              <label for="model">Model</label>
              <input type="text" id="model" name="model" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="year">Year</label>
              <input type="number" id="year" name="year" min="2000" max="2030" required />
            </div>
            <div class="form-group">
              <label for="license_plate">License Plate</label>
              <input type="text" id="license_plate" name="license_plate" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="capacity">Capacity</label>
              <input type="number" id="capacity" name="capacity" min="1" max="20" required />
            </div>
            <div class="form-group">
              <label for="vehicle_type">Vehicle Type</label>
              <select id="vehicle_type" name="vehicle_type" required>
                <option value="car">Car</option>
                <option value="suv">SUV</option>
                <option value="van">Van</option>
                <option value="motor">Motorcycle</option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
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
              <label for="status">Status</label>
              <select id="status" name="status" required>
                <option value="available">Available</option>
                <option value="in_use">In Use</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
          <button type="button" class="btn" onclick="closeModal('add-vehicle-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Vehicle Modal -->
  <div class="modal" id="edit-vehicle-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Edit Vehicle</div>
        <span class="close" onclick="closeModal('edit-vehicle-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="edit_vehicle_id" name="vehicle_id" value="">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label for="edit_make">Make</label>
              <input type="text" id="edit_make" name="make" required />
            </div>
            <div class="form-group">
              <label for="edit_model">Model</label>
              <input type="text" id="edit_model" name="model" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="edit_year">Year</label>
              <input type="number" id="edit_year" name="year" min="2000" max="2030" required />
            </div>
            <div class="form-group">
              <label for="edit_license_plate">License Plate</label>
              <input type="text" id="edit_license_plate" name="license_plate" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="edit_capacity">Capacity</label>
              <input type="number" id="edit_capacity" name="capacity" min="1" max="20" required />
            </div>
            <div class="form-group">
              <label for="edit_vehicle_type">Vehicle Type</label>
              <select id="edit_vehicle_type" name="vehicle_type" required>
                <option value="car">Car</option>
                <option value="suv">SUV</option>
                <option value="van">Van</option>
                <option value="motor">Motorcycle</option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="edit_fuel_type">Fuel Type</label>
              <select id="edit_fuel_type" name="fuel_type" required>
                <option value="gasoline">Gasoline</option>
                <option value="diesel">Diesel</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
                <option value="cng">CNG</option>
              </select>
            </div>
            <div class="form-group">
              <label for="edit_status">Status</label>
              <select id="edit_status" name="status" required>
                <option value="available">Available</option>
                <option value="in_use">In Use</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="update_vehicle" class="btn btn-primary">Update Vehicle</button>
          <button type="button" class="btn" onclick="closeModal('edit-vehicle-modal')">Cancel</button>
          <button type="button" class="btn" onclick="window.location.href = 'VR.php'">Cancel Edit</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-vehicle-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Confirm Deletion</div>
        <span class="close" onclick="closeModal('delete-vehicle-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="delete_vehicle_id" name="vehicle_id" value="">
        <div class="delete-confirmation">
          Are you sure you want to delete vehicle: <strong id="delete_vehicle_name"></strong>?
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_vehicle" class="btn btn-delete">Delete</button>
          <button type="button" class="btn" onclick="closeModal('delete-vehicle-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(type) {
      if (type === 'add-vehicle') {
        document.getElementById('add-vehicle-modal').style.display = 'flex';
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
      // Clear edit parameters from URL when closing modal
      if (window.location.href.includes('edit_id')) {
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    }

    function editVehicle(vehicleId) {
      // Redirect to the same page with edit parameter
      window.location.href = 'VR.php?edit_id=' + vehicleId;
    }

    function deleteVehicle(vehicleId, vehicleName) {
      document.getElementById('delete_vehicle_id').value = vehicleId;
      document.getElementById('delete_vehicle_name').textContent = vehicleName;
      document.getElementById('delete-vehicle-modal').style.display = 'flex';
    }

    // Auto-open edit modal if we have edit data
    <?php if ($edit_vehicle_data): ?>
    document.addEventListener('DOMContentLoaded', function() {
        // Populate the edit form with existing data
        document.getElementById('edit_vehicle_id').value = '<?php echo $edit_vehicle_data['vehicle_id']; ?>';
        document.getElementById('edit_make').value = '<?php echo $edit_vehicle_data['make']; ?>';
        document.getElementById('edit_model').value = '<?php echo $edit_vehicle_data['model']; ?>';
        document.getElementById('edit_year').value = '<?php echo $edit_vehicle_data['year']; ?>';
        document.getElementById('edit_license_plate').value = '<?php echo $edit_vehicle_data['license_plate']; ?>';
        document.getElementById('edit_capacity').value = '<?php echo $edit_vehicle_data['capacity']; ?>';
        document.getElementById('edit_vehicle_type').value = '<?php echo $edit_vehicle_data['vehicle_type']; ?>';
        document.getElementById('edit_fuel_type').value = '<?php echo $edit_vehicle_data['fuel_type']; ?>';
        document.getElementById('edit_status').value = '<?php echo $edit_vehicle_data['status']; ?>';
        
        // Show the edit modal
        document.getElementById('edit-vehicle-modal').style.display = 'flex';
    });
    <?php endif; ?>

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        // Clear edit parameters from URL when closing modal
        if (window.location.href.includes('edit_id')) {
          window.history.replaceState({}, document.title, window.location.pathname);
        }
      }
    };
  </script>
</body>
</html>