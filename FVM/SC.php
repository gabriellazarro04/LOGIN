<?php
// SC.php - Scheduling
include 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        $vehicle_id = $_POST['vehicle_id'];
        $driver_id = $_POST['driver_id'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $purpose = $_POST['purpose'];
        $status = $_POST['status'];
        
        $result = insertAndGetId(
            "INSERT INTO schedules (vehicle_id, driver_id, start_time, end_time, purpose, status) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$vehicle_id, $driver_id, $start_time, $end_time, $purpose, $status]
        );
        
        if ($result) {
            $success = "Schedule added successfully!";
        } else {
            $error = "Error adding schedule!";
        }
    }
    
    if (isset($_POST['update_schedule'])) {
        $schedule_id = $_POST['schedule_id'];
        $vehicle_id = $_POST['vehicle_id'];
        $driver_id = $_POST['driver_id'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $purpose = $_POST['purpose'];
        $status = $_POST['status'];
        
        $result = executeQuery(
            "UPDATE schedules SET vehicle_id=?, driver_id=?, start_time=?, end_time=?, purpose=?, status=? 
             WHERE schedule_id=?",
            [$vehicle_id, $driver_id, $start_time, $end_time, $purpose, $status, $schedule_id]
        );
        
        if ($result) {
            $success = "Schedule updated successfully!";
        } else {
            $error = "Error updating schedule!";
        }
    }
    
    if (isset($_POST['delete_schedule'])) {
        $schedule_id = $_POST['schedule_id'];
        
        $result = executeQuery(
            "DELETE FROM schedules WHERE schedule_id=?",
            [$schedule_id]
        );
        
        if ($result) {
            $success = "Schedule deleted successfully!";
        } else {
            $error = "Error deleting schedule!";
        }
    }
}

// Get all schedules
$schedules = executeQuery("
    SELECT s.*, v.make, v.model, v.license_plate, d.first_name, d.last_name 
    FROM schedules s
    LEFT JOIN vehicles v ON s.vehicle_id = v.vehicle_id
    LEFT JOIN drivers d ON s.driver_id = d.driver_id
    ORDER BY s.start_time DESC
");

// Get all vehicles and drivers for the form (not just available ones for editing)
$allVehicles = executeQuery("SELECT * FROM vehicles");
$allDrivers = executeQuery("SELECT * FROM drivers");

// Get statistics for dashboard
$totalSchedules = count($schedules);
$scheduled = array_filter($schedules, function($s) { return $s['status'] === 'scheduled'; });
$inProgress = array_filter($schedules, function($s) { return $s['status'] === 'in_progress'; });
$completed = array_filter($schedules, function($s) { return $s['status'] === 'completed'; });
$cancelled = array_filter($schedules, function($s) { return $s['status'] === 'cancelled'; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scheduling - VRDS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* Same CSS as before, with some additions for the delete button */
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

    .total-schedules {
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
      background-color: #9300b4ff;
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
      <li class="active"><a href="SC.php"><i class="fas fa-calendar-alt me-2"></i> Scheduling</a></li>
      <li><a href="MA.php"><i class="fas fa-tools me-2"></i> Maintenance</a></li>
      <li><a href="VA.php"><i class="fas fa-tasks me-2"></i> Vehicle Assignment</a></li>
      <li><a href="VM.php"><i class="fas fa-tasks me-2"></i> Vehicle Monitoring</a></li>
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
      <div class="page-title">Scheduling Management</div>
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
      <button class="btn btn-primary" onclick="openModal('add-schedule')">
        <i class="fas fa-plus"></i> Add New Schedule
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Vehicle Schedule</div>
        </div>
        <div class="schedule-list">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($schedules as $schedule): 
                $statusClass = str_replace('_', '-', $schedule['status']);
              ?>
              <tr>
                <td>#S-<?php echo $schedule['schedule_id']; ?></td>
                <td><?php echo $schedule['make'] . ' ' . $schedule['model'] . ' (' . $schedule['license_plate'] . ')'; ?></td>
                <td><?php echo $schedule['first_name'] . ' ' . $schedule['last_name']; ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($schedule['start_time'])); ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($schedule['end_time'])); ?></td>
                <td><?php echo $schedule['purpose']; ?></td>
                <td><span class='status <?php echo $statusClass; ?>'><?php echo ucfirst(str_replace('_', ' ', $schedule['status'])); ?></span></td>
                <td class="action-buttons">
                  <button class="btn btn-edit" onclick="editSchedule(<?php echo $schedule['schedule_id']; ?>, '<?php echo $schedule['vehicle_id']; ?>', '<?php echo $schedule['driver_id']; ?>', '<?php echo date('Y-m-d\TH:i', strtotime($schedule['start_time'])); ?>', '<?php echo date('Y-m-d\TH:i', strtotime($schedule['end_time'])); ?>', `<?php echo addslashes($schedule['purpose']); ?>`, '<?php echo $schedule['status']; ?>')">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-delete" onclick="deleteSchedule(<?php echo $schedule['schedule_id']; ?>, '<?php echo $schedule['make'] . ' ' . $schedule['model']; ?>')">
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

  <!-- Add Schedule Modal -->
  <div class="modal" id="add-schedule-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Add New Schedule</div>
        <span class="close" onclick="closeModal('add-schedule-modal')">&times;</span>
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
            <label for="driver_id">Driver</label>
            <select id="driver_id" name="driver_id" required>
              <option value="">Select Driver</option>
              <?php foreach ($allDrivers as $driver): ?>
              <option value="<?php echo $driver['driver_id']; ?>">
                <?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="start_time">Start Time</label>
              <input type="datetime-local" id="start_time" name="start_time" required />
            </div>
            <div class="form-group">
              <label for="end_time">End Time</label>
              <input type="datetime-local" id="end_time" name="end_time" required />
            </div>
          </div>
          
          <div class="form-group">
            <label for="purpose">Purpose</label>
            <textarea id="purpose" name="purpose" rows="3" required></textarea>
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
          <button type="submit" name="add_schedule" class="btn btn-primary">Add Schedule</button>
          <button type="button" class="btn" onclick="closeModal('add-schedule-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Schedule Modal -->
  <div class="modal" id="edit-schedule-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Edit Schedule</div>
        <span class="close" onclick="closeModal('edit-schedule-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="edit_schedule_id" name="schedule_id" value="">
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
            <label for="edit_driver_id">Driver</label>
            <select id="edit_driver_id" name="driver_id" required>
              <option value="">Select Driver</option>
              <?php foreach ($allDrivers as $driver): ?>
              <option value="<?php echo $driver['driver_id']; ?>">
                <?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="edit_start_time">Start Time</label>
              <input type="datetime-local" id="edit_start_time" name="start_time" required />
            </div>
            <div class="form-group">
              <label for="edit_end_time">End Time</label>
              <input type="datetime-local" id="edit_end_time" name="end_time" required />
            </div>
          </div>
          
          <div class="form-group">
            <label for="edit_purpose">Purpose</label>
            <textarea id="edit_purpose" name="purpose" rows="3" required></textarea>
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
          <button type="submit" name="update_schedule" class="btn btn-primary">Update Schedule</button>
          <button type="button" class="btn" onclick="closeModal('edit-schedule-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-schedule-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Confirm Deletion</div>
        <span class="close" onclick="closeModal('delete-schedule-modal')">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" id="delete_schedule_id" name="schedule_id" value="">
        <div class="delete-confirmation">
          Are you sure you want to delete the schedule for: <strong id="delete_schedule_vehicle"></strong>?
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_schedule" class="btn btn-delete">Delete Schedule</button>
          <button type="button" class="btn" onclick="closeModal('delete-schedule-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(type) {
      if (type === 'add-schedule') {
        document.getElementById('add-schedule-modal').style.display = 'flex';
        
        // Set default start time to now
        const now = new Date();
        document.getElementById('start_time').value = now.toISOString().slice(0, 16);
        
        // Set default end time to 1 hour from now
        now.setHours(now.getHours() + 1);
        document.getElementById('end_time').value = now.toISOString().slice(0, 16);
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function editSchedule(scheduleId, vehicleId, driverId, startTime, endTime, purpose, status) {
      // Populate the edit form with existing data
      document.getElementById('edit_schedule_id').value = scheduleId;
      document.getElementById('edit_vehicle_id').value = vehicleId;
      document.getElementById('edit_driver_id').value = driverId;
      document.getElementById('edit_start_time').value = startTime;
      document.getElementById('edit_end_time').value = endTime;
      document.getElementById('edit_purpose').value = purpose;
      document.getElementById('edit_status').value = status;
      
      // Show the edit modal
      document.getElementById('edit-schedule-modal').style.display = 'flex';
    }

    function deleteSchedule(scheduleId, vehicleName) {
      document.getElementById('delete_schedule_id').value = scheduleId;
      document.getElementById('delete_schedule_vehicle').textContent = vehicleName;
      document.getElementById('delete-schedule-modal').style.display = 'flex';
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    };
  </script>
</body>
</html>