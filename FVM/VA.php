<?php
// VA.php - Vehicle Assignment
include 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_vehicle'])) {
        $vehicle_id = $_POST['vehicle_id'];
        $driver_id = $_POST['driver_id'];
        $assignment_date = $_POST['assignment_date'];
        $expected_return_date = $_POST['expected_return_date'];
        $purpose = $_POST['purpose'];
        $status = $_POST['status'];
        
        $result = insertAndGetId(
            "INSERT INTO vehicle_assignments (vehicle_id, driver_id, assignment_date, expected_return_date, purpose, status) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$vehicle_id, $driver_id, $assignment_date, $expected_return_date, $purpose, $status]
        );
        
        if ($result) {
            // Update vehicle status to in_use
            executeQuery(
                "UPDATE vehicles SET status = 'in_use' WHERE vehicle_id = ?",
                [$vehicle_id]
            );
            
            // Update driver status to on_trip
            executeQuery(
                "UPDATE drivers SET status = 'on_trip' WHERE driver_id = ?",
                [$driver_id]
            );
            
            $success = "Vehicle assigned successfully!";
        } else {
            $error = "Error assigning vehicle!";
        }
    }
    
    if (isset($_POST['update_assignment'])) {
        $assignment_id = $_POST['assignment_id'];
        $actual_return_date = $_POST['actual_return_date'];
        $status = $_POST['status'];
        
        $result = executeQuery(
            "UPDATE vehicle_assignments SET actual_return_date=?, status=? 
             WHERE assignment_id=?",
            [$actual_return_date, $status, $assignment_id]
        );
        
        if ($result && $status === 'completed') {
            // Get assignment details to update vehicle and driver status
            $assignment = fetchSingle(
                "SELECT vehicle_id, driver_id FROM vehicle_assignments WHERE assignment_id = ?",
                [$assignment_id]
            );
            
            if ($assignment) {
                // Update vehicle status to available
                executeQuery(
                    "UPDATE vehicles SET status = 'available' WHERE vehicle_id = ?",
                    [$assignment['vehicle_id']]
                );
                
                // Update driver status to available
                executeQuery(
                    "UPDATE drivers SET status = 'available' WHERE driver_id = ?",
                    [$assignment['driver_id']]
                );
            }
            
            $success = "Assignment updated successfully!";
        } else {
            $error = "Error updating assignment!";
        }
    }
}

// Get all vehicle assignments
$assignments = executeQuery("
    SELECT a.*, v.make, v.model, v.license_plate, d.first_name, d.last_name 
    FROM vehicle_assignments a
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN drivers d ON a.driver_id = d.driver_id
    ORDER BY a.assignment_date DESC
");

// Get available vehicles and drivers for the form
$availableVehicles = executeQuery("SELECT * FROM vehicles WHERE status = 'available'");
$availableDrivers = executeQuery("SELECT * FROM drivers WHERE status = 'available'");

// Get statistics for dashboard
$totalAssignments = count($assignments);
$active = array_filter($assignments, function($a) { return $a['status'] === 'active'; });
$dueToday = array_filter($assignments, function($a) { 
    return $a['status'] === 'active' && 
           date('Y-m-d', strtotime($a['expected_return_date'])) === date('Y-m-d'); 
});
$overdue = array_filter($assignments, function($a) { 
    return $a['status'] === 'active' && 
           strtotime($a['expected_return_date']) < strtotime(date('Y-m-d')); 
});
$completed = array_filter($assignments, function($a) { return $a['status'] === 'completed'; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vehicle Assignment - VRDS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* Same CSS as VR.php */
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

    .total-assignments {
      color: var(--primary);
    }

    .active-assignments {
      color: var(--secondary);
    }

    .due-today {
      color: var(--warning);
    }

    .overdue {
      color: var(--accent);
    }

    .completed {
      color: var(--success);
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

    .active {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .overdue-row {
      background-color: #faecea;
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
      <li><a href="MA.php"><i class="fas fa-tools me-2"></i> Maintenance</a></li>
      <li class="active"><a href="VA.php"><i class="fas fa-tasks me-2"></i> Vehicle Assignment</a></li>
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
      <div class="page-title">Vehicle Assignment Management</div>
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
      <button class="btn btn-primary" onclick="openModal('assign-vehicle')">
        <i class="fas fa-plus"></i> Assign Vehicle
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Current Assignments</div>
        </div>
        <div class="assignment-list">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Assignment Date</th>
                <th>Expected Return</th>
                <th>Actual Return</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($assignments as $assignment): 
                $statusClass = str_replace('_', '-', $assignment['status']);
                
                // Check if assignment is overdue
                $isOverdue = $assignment['status'] === 'active' && 
                             strtotime($assignment['expected_return_date']) < strtotime(date('Y-m-d'));
              ?>
              <tr class="<?php echo $isOverdue ? 'overdue-row' : ''; ?>">
                <td>#A-<?php echo $assignment['assignment_id']; ?></td>
                <td><?php echo $assignment['make'] . ' ' . $assignment['model'] . ' (' . $assignment['license_plate'] . ')'; ?></td>
                <td><?php echo $assignment['first_name'] . ' ' . $assignment['last_name']; ?></td>
                <td><?php echo date('M j, Y', strtotime($assignment['assignment_date'])); ?></td>
                <td><?php echo date('M j, Y', strtotime($assignment['expected_return_date'])); ?></td>
                <td><?php echo $assignment['actual_return_date'] ? date('M j, Y', strtotime($assignment['actual_return_date'])) : 'N/A'; ?></td>
                <td><?php echo $assignment['purpose']; ?></td>
                <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst($assignment['status']); ?></span></td>
                <td class="action-buttons">
                  <button class="btn btn-edit" onclick="completeAssignment(<?php echo $assignment['assignment_id']; ?>)">
                    <i class="fas fa-check"></i> Complete
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

  <!-- Assign Vehicle Modal -->
  <div class="modal" id="assign-vehicle-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Assign Vehicle</div>
        <span class="close" onclick="closeModal('assign-vehicle-modal')">&times;</span>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label for="vehicle_id">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" required>
              <option value="">Select Vehicle</option>
              <?php foreach ($availableVehicles as $vehicle): ?>
              <option value="<?php echo $vehicle['vehicle_id']; ?>">
                <?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="driver_id">Driver</label>
            <select id="driver_id" name="driver_id" required>
              <option value="">Select Driver</option>
              <?php foreach ($availableDrivers as $driver): ?>
              <option value="<?php echo $driver['driver_id']; ?>">
                <?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="assignment_date">Assignment Date</label>
              <input type="date" id="assignment_date" name="assignment_date" required />
            </div>
            <div class="form-group">
              <label for="expected_return_date">Expected Return Date</label>
              <input type="date" id="expected_return_date" name="expected_return_date" required />
            </div>
          </div>
          
          <div class="form-group">
            <label for="purpose">Purpose</label>
            <textarea id="purpose" name="purpose" rows="3" required></textarea>
          </div>
          
          <input type="hidden" name="status" value="active">
        </div>
        <div class="form-actions">
          <button type="submit" name="assign_vehicle" class="btn btn-primary">Assign Vehicle</button>
          <button type="button" class="btn" onclick="closeModal('assign-vehicle-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Complete Assignment Modal -->
  <div class="modal" id="complete-assignment-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Complete Assignment</div>
        <span class="close" onclick="closeModal('complete-assignment-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="complete_assignment_id" name="assignment_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="actual_return_date">Actual Return Date</label>
            <input type="date" id="actual_return_date" name="actual_return_date" required />
          </div>
          
          <input type="hidden" name="status" value="completed">
        </div>
        <div class="form-actions">
          <button type="submit" name="update_assignment" class="btn btn-primary">Complete Assignment</button>
          <button type="button" class="btn" onclick="closeModal('complete-assignment-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(type) {
      if (type === 'assign-vehicle') {
        document.getElementById('assign-vehicle-modal').style.display = 'flex';
        
        // Set default assignment date to today
        document.getElementById('assignment_date').value = new Date().toISOString().slice(0, 10);
        
        // Set default return date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('expected_return_date').value = tomorrow.toISOString().slice(0, 10);
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function completeAssignment(assignmentId) {
      document.getElementById('complete_assignment_id').value = assignmentId;
      document.getElementById('actual_return_date').value = new Date().toISOString().slice(0, 10);
      document.getElementById('complete-assignment-modal').style.display = 'flex';
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    };
  </script>
</body>
</html>