<?php
// MA.php - Maintenance
include 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_maintenance'])) {
        $vehicle_id = $_POST['vehicle_id'];
        $maintenance_type = $_POST['maintenance_type'];
        $scheduled_date = $_POST['scheduled_date'];
        $completed_date = $_POST['completed_date'];
        $cost = $_POST['cost'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        
        $result = insertAndGetId(
            "INSERT INTO maintenance (vehicle_id, maintenance_type, scheduled_date, completed_date, cost, description, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$vehicle_id, $maintenance_type, $scheduled_date, $completed_date, $cost, $description, $status]
        );
        
        if ($result) {
            // Update vehicle status if maintenance is in progress or completed
            if ($status === 'in_progress' || $status === 'completed') {
                executeQuery(
                    "UPDATE vehicles SET status = 'maintenance' WHERE vehicle_id = ?",
                    [$vehicle_id]
                );
            }
            
            $success = "Maintenance record added successfully!";
        } else {
            $error = "Error adding maintenance record!";
        }
    }
    
    if (isset($_POST['update_maintenance'])) {
        $maintenance_id = $_POST['maintenance_id'];
        $vehicle_id = $_POST['vehicle_id'];
        $maintenance_type = $_POST['maintenance_type'];
        $scheduled_date = $_POST['scheduled_date'];
        $completed_date = $_POST['completed_date'];
        $cost = $_POST['cost'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        
        $result = executeQuery(
            "UPDATE maintenance SET vehicle_id=?, maintenance_type=?, scheduled_date=?, completed_date=?, cost=?, description=?, status=? 
             WHERE maintenance_id=?",
            [$vehicle_id, $maintenance_type, $scheduled_date, $completed_date, $cost, $description, $status, $maintenance_id]
        );
        
        if ($result) {
            $success = "Maintenance record updated successfully!";
        } else {
            $error = "Error updating maintenance record!";
        }
    }
}

// Get all maintenance records
$maintenanceRecords = executeQuery("
    SELECT m.*, v.make, v.model, v.license_plate 
    FROM maintenance m
    LEFT JOIN vehicles v ON m.vehicle_id = v.vehicle_id
    ORDER BY m.scheduled_date DESC
");

// Get all vehicles for the form
$allVehicles = executeQuery("SELECT * FROM vehicles ORDER BY make, model");

// Get statistics for dashboard
$totalMaintenance = count($maintenanceRecords);
$scheduled = array_filter($maintenanceRecords, function($m) { return $m['status'] === 'scheduled'; });
$inProgress = array_filter($maintenanceRecords, function($m) { return $m['status'] === 'in_progress'; });
$completed = array_filter($maintenanceRecords, function($m) { 
    return $m['status'] === 'completed' && 
           date('Y-m', strtotime($m['completed_date'])) === date('Y-m'); 
});
$cancelled = array_filter($maintenanceRecords, function($m) { return $m['status'] === 'cancelled'; });

// Get maintenance record for editing if ID is provided
$editRecord = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $editRecord = executeQuery(
        "SELECT m.*, v.make, v.model, v.license_plate 
         FROM maintenance m
         LEFT JOIN vehicles v ON m.vehicle_id = v.vehicle_id
         WHERE m.maintenance_id = ?",
        [$edit_id]
    );
    
    if (!empty($editRecord)) {
        $editRecord = $editRecord[0];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Maintenance - VRDS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* Same CSS as before */
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

    .total-maintenance {
      color: var(--primary);
    }

    .scheduled {
      color: var(--secondary);
    }

    .in-progress {
      color: var(--warning);
    }

    .completed {
      color: var(--success);
    }

    .cancelled {
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

    .scheduled {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .in-progress {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .cancelled {
      background-color: #faecea;
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
  <!-- Sidebar (same as VR.php) -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li><a href="fvm-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard</a></li>
      <li><a href="VR.php"><i class="fas fa-car me-2"></i> Vehicle Registration</a></li>
      <li><a href="SC.php"><i class="fas fa-calendar-alt me-2"></i> Scheduling</a></li>
      <li class="active"><a href="MA.php"><i class="fas fa-tools me-2"></i> Maintenance</a></li>
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
      <div class="page-title">Maintenance Management</div>
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
      <button class="btn btn-primary" onclick="openModal('add-maintenance')">
        <i class="fas fa-plus"></i> Add Maintenance Record
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Maintenance History</div>
        </div>
        <div class="maintenance-list">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Maintenance Type</th>
                <th>Scheduled Date</th>
                <th>Completed Date</th>
                <th>Cost</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($maintenanceRecords as $record): 
                $statusClass = str_replace('_', '-', $record['status']);
              ?>
              <tr>
                <td>#M-<?php echo $record['maintenance_id']; ?></td>
                <td><?php echo $record['make'] . ' ' . $record['model'] . ' (' . $record['license_plate'] . ')'; ?></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $record['maintenance_type'])); ?></td>
                <td><?php echo date('M j, Y', strtotime($record['scheduled_date'])); ?></td>
                <td><?php echo $record['completed_date'] ? date('M j, Y', strtotime($record['completed_date'])) : 'N/A'; ?></td>
                <td>$<?php echo number_format($record['cost'], 2); ?></td>
                <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?></span></td>
                <td class="action-buttons">
                  <button class="btn btn-edit" onclick="editMaintenance(<?php echo $record['maintenance_id']; ?>)">
                    <i class="fas fa-edit"></i> Edit
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

  <!-- Add Maintenance Modal -->
  <div class="modal" id="add-maintenance-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Add Maintenance Record</div>
        <span class="close" onclick="closeModal('add-maintenance-modal')">&times;</span>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label for="vehicle_id">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" required>
              <option value="">Select Vehicle</option>
              <?php foreach ($allVehicles as $vehicle): ?>
              <option value="<?php echo $vehicle['vehicle_id']; ?>">
                <?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="maintenance_type">Maintenance Type</label>
            <select id="maintenance_type" name="maintenance_type" required>
              <option value="routine">Routine Maintenance</option>
              <option value="repair">Repair</option>
              <option value="inspection">Inspection</option>
              <option value="tire_replacement">Tire Replacement</option>
              <option value="oil_change">Oil Change</option>
              <option value="brake_service">Brake Service</option>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="scheduled_date">Scheduled Date</label>
              <input type="date" id="scheduled_date" name="scheduled_date" required />
            </div>
            <div class="form-group">
              <label for="completed_date">Completed Date</label>
              <input type="date" id="completed_date" name="completed_date" />
            </div>
          </div>
          
          <div class="form-group">
            <label for="cost">Cost ($)</label>
            <input type="number" id="cost" name="cost" step="0.01" min="0" />
          </div>
          
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"></textarea>
          </div>
          
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="scheduled">Scheduled</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="add_maintenance" class="btn btn-primary">Add Record</button>
          <button type="button" class="btn" onclick="closeModal('add-maintenance-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Maintenance Modal -->
  <div class="modal" id="edit-maintenance-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Edit Maintenance Record</div>
        <span class="close" onclick="closeModal('edit-maintenance-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" name="maintenance_id" id="edit_maintenance_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="edit_vehicle_id">Vehicle</label>
            <select id="edit_vehicle_id" name="vehicle_id" required>
              <option value="">Select Vehicle</option>
              <?php foreach ($allVehicles as $vehicle): ?>
              <option value="<?php echo $vehicle['vehicle_id']; ?>">
                <?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="edit_maintenance_type">Maintenance Type</label>
            <select id="edit_maintenance_type" name="maintenance_type" required>
              <option value="routine">Routine Maintenance</option>
              <option value="repair">Repair</option>
              <option value="inspection">Inspection</option>
              <option value="tire_replacement">Tire Replacement</option>
              <option value="oil_change">Oil Change</option>
              <option value="brake_service">Brake Service</option>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="edit_scheduled_date">Scheduled Date</label>
              <input type="date" id="edit_scheduled_date" name="scheduled_date" required />
            </div>
            <div class="form-group">
              <label for="edit_completed_date">Completed Date</label>
              <input type="date" id="edit_completed_date" name="completed_date" />
            </div>
          </div>
          
          <div class="form-group">
            <label for="edit_cost">Cost ($)</label>
            <input type="number" id="edit_cost" name="cost" step="0.01" min="0" />
          </div>
          
          <div class="form-group">
            <label for="edit_description">Description</label>
            <textarea id="edit_description" name="description" rows="3"></textarea>
          </div>
          
          <div class="form-group">
            <label for="edit_status">Status</label>
            <select id="edit_status" name="status" required>
              <option value="scheduled">Scheduled</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="update_maintenance" class="btn btn-primary">Update Record</button>
          <button type="button" class="btn" onclick="closeModal('edit-maintenance-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(type) {
      if (type === 'add-maintenance') {
        document.getElementById('add-maintenance-modal').style.display = 'flex';
        
        // Set default scheduled date to today
        document.getElementById('scheduled_date').value = new Date().toISOString().slice(0, 10);
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function editMaintenance(maintenanceId) {
      // Redirect to the same page with the edit_id parameter
      window.location.href = 'MA.php?edit_id=' + maintenanceId;
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    };

    // Auto-open edit modal if there's an edit_id in the URL
    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      const editId = urlParams.get('edit_id');
      
      if (editId) {
        // Populate the edit form with data (this would typically come from server-side)
        // For now, we'll just open the modal
        document.getElementById('edit-maintenance-modal').style.display = 'flex';
        
        // In a real application, you would fetch the data via AJAX or have it pre-populated by PHP
        <?php if ($editRecord): ?>
        document.getElementById('edit_maintenance_id').value = '<?php echo $editRecord['maintenance_id']; ?>';
        document.getElementById('edit_vehicle_id').value = '<?php echo $editRecord['vehicle_id']; ?>';
        document.getElementById('edit_maintenance_type').value = '<?php echo $editRecord['maintenance_type']; ?>';
        document.getElementById('edit_scheduled_date').value = '<?php echo $editRecord['scheduled_date']; ?>';
        document.getElementById('edit_completed_date').value = '<?php echo $editRecord['completed_date']; ?>';
        document.getElementById('edit_cost').value = '<?php echo $editRecord['cost']; ?>';
        document.getElementById('edit_description').value = '<?php echo addslashes($editRecord['description']); ?>';
        document.getElementById('edit_status').value = '<?php echo $editRecord['status']; ?>';
        <?php endif; ?>
      }
    };
  </script>
</body>
</html>