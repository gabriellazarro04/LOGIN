<?php 
include 'config.php';

// Initialize success message variable
$successMessage = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_report'])) {
        // Edit existing report
        $report_id = mysqli_real_escape_string($Connections, $_POST['report_id']);
        $category = mysqli_real_escape_string($Connections, $_POST['category']);
        $period = mysqli_real_escape_string($Connections, $_POST['period']);
        $status = mysqli_real_escape_string($Connections, $_POST['status']);
        $notes = mysqli_real_escape_string($Connections, $_POST['notes']);
        
        $sql = "UPDATE cost_optimization_reports SET category='$category', period='$period', 
                status='$status', notes='$notes' 
                WHERE id='$report_id'";
        
        if (mysqli_query($Connections, $sql)) {
            $successMessage = 'Report updated successfully!';
            echo "<script>window.location.href = 'CO.php?success=1';</script>";
            exit();
        } else {
            $successMessage = 'Error: ' . mysqli_error($Connections);
        }
    }
    
    if (isset($_POST['delete_report'])) {
        // Delete report
        $report_id = mysqli_real_escape_string($Connections, $_POST['report_id']);
        
        $sql = "DELETE FROM cost_optimization_reports WHERE id='$report_id'";
        
        if (mysqli_query($Connections, $sql)) {
            $successMessage = 'Report deleted successfully!';
            echo "<script>window.location.href = 'CO.php?success=1';</script>";
            exit();
        } else {
            $successMessage = 'Error: ' . mysqli_error($Connections);
        }
    }
    
    if (isset($_POST['generate_report'])) {
        // Generate a new report
        $category = mysqli_real_escape_string($Connections, $_POST['report_category']);
        $period = mysqli_real_escape_string($Connections, $_POST['period']);
        $status = "Pending Review";
        $notes = mysqli_real_escape_string($Connections, $_POST['notes']);
        
        // Generate a unique report ID
        $report_id = "RPT-" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO cost_optimization_reports (report_id, category, period, status, notes) 
                VALUES ('$report_id', '$category', '$period', '$status', '$notes')";
        
        if (mysqli_query($Connections, $sql)) {
            $successMessage = 'Report generated successfully!';
            echo "<script>window.location.href = 'CO.php?success=1';</script>";
            exit();
        } else {
            $successMessage = 'Error: ' . mysqli_error($Connections);
        }
    }
}

// Handle success parameter from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = 'Operation completed successfully!';
}

// Fetch cost optimization reports
$reports_query = "SELECT * FROM cost_optimization_reports ORDER BY created_at DESC";
$reports_result = mysqli_query($Connections, $reports_query);

// Check if there are no reports and add sample data
if (mysqli_num_rows($reports_result) == 0) {
    // Insert sample data
    $sample_data = [
        ["RPT-001", "Fuel Efficiency", "Q3 2023", "Implemented", "Reduced idle time by 15%"],
        ["RPT-002", "Route Optimization", "Q3 2023", "In Progress", "New routes being tested"],
        ["RPT-003", "Maintenance Schedule", "Q3 2023", "Pending Review", "Preventive maintenance plan"]
    ];
    
    foreach ($sample_data as $data) {
        $insert = "INSERT INTO cost_optimization_reports (report_id, category, period, status, notes) 
                   VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]')";
        mysqli_query($Connections, $insert);
    }
    
    // Re-fetch reports
    $reports_result = mysqli_query($Connections, $reports_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cost Optimization Reports</title>
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
      margin-top: 10px;
      display: flex;
      margin-left:40px;
      margin-right: 90px;
      flex-direction: column;
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      margin-left: 50px;
      margin-top: 70px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 30px;
      transition: transform 0.3s ease;
      width: 90%;
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

    .optimized {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .moderate {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .needs-improvement {
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
      margin-bottom: 10px;
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

    .chart-container {
      margin-top: 20px;
      height: 300px;
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
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
      <li><a href="FU.php"><i class="fas fa-gas-pump me-2"></i> Fuel Usage </a></li>
      <li><a href="TF.php"><i class="fas fa-arrows-alt"></i> Toll Fees </a></li>
      <li><a href="TE.php"><i class="fas fa-bolt"></i> Trip Expenses </a></li>
      <li class="active"><a href="CO.php"><i class="fas fa-money-bill"></i> Cost Optimization </a></li>
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
      <button class="btn btn-primary" onclick="openModal('generate-report')">
        <i class="fas fa-chart-line"></i> Generate Report
      </button>
    </div>

    <!-- Cost Optimization Chart -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Cost Optimization Reports</div>
      </div>
      <div class="optimization-table">
        <table>
          <thead>
            <tr>
              <th>Report ID</th>
              <th>Category</th>
              <th>Period</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (mysqli_num_rows($reports_result) > 0) {
                while($row = mysqli_fetch_assoc($reports_result)) {
                    $status_class = '';
                    if ($row['status'] == 'Implemented') $status_class = 'optimized';
                    else if ($row['status'] == 'In Progress') $status_class = 'moderate';
                    else if ($row['status'] == 'Pending Review') $status_class = 'needs-improvement';
                    
                    echo "<tr>
                        <td>{$row['report_id']}</td>
                        <td>{$row['category']}</td>
                        <td>{$row['period']}</td>
                        <td><span class='status $status_class'>{$row['status']}</span></td>
                        <td>
                          <button class='btn btn-edit' onclick='editReport({$row['id']})'>
                            <i class='fas fa-edit'></i>
                          </button>
                          <button class='btn btn-delete' onclick='deleteReport({$row['id']}, \"{$row['report_id']}\", \"{$row['category']}\")'>
                            <i class='fas fa-trash'></i>
                          </button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align: center;'>No cost optimization reports found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Generate Report Modal -->
  <div class="modal" id="generate-report-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Generate Cost Optimization Report</div>
        <span class="close" onclick="closeModal('generate-report-modal')">&times;</span>
      </div>
      <form method="POST" id="report-form">
        <div class="modal-body">
          <div class="form-group">
            <label for="report_category">Report Category</label>
            <select id="report_category" name="report_category" required>
              <option value="">Select Category</option>
              <option value="Fuel Efficiency">Fuel Efficiency</option>
              <option value="Route Optimization">Route Optimization</option>
              <option value="Maintenance Schedule">Maintenance Schedule</option>
              <option value="Driver Behavior">Driver Behavior</option>
              <option value="Toll Fees">Toll Fees</option>
              <option value="Overall Cost Analysis">Overall Cost Analysis</option>
            </select>
          </div>
          <div class="form-group">
            <label for="period">Period</label>
            <input type="text" id="period" name="period" required placeholder="e.g., Q3 2023, October 2023">
          </div>
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Enter report details, findings, or recommendations..."></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
          <button type="button" class="btn" onclick="closeModal('generate-report-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Report Modal -->
  <div class="modal" id="edit-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Edit Cost Optimization Report</div>
        <span class="close" onclick="closeModal('edit-modal')">&times;</span>
      </div>
      <form method="POST" id="edit-form">
        <input type="hidden" id="edit_report_id" name="report_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="edit_report_id_display">Report ID</label>
            <input type="text" id="edit_report_id_display" class="readonly-field" readonly placeholder="Auto-generated report ID">
          </div>
          <div class="form-group">
            <label for="edit_category">Category</label>
            <select id="edit_category" name="category" required>
              <option value="">Select Cost Optimization Category</option>
              <option value="Fuel Efficiency">Fuel Efficiency</option>
              <option value="Route Optimization">Route Optimization</option>
              <option value="Maintenance Schedule">Maintenance Schedule</option>
              <option value="Driver Behavior">Driver Behavior</option>
              <option value="Toll Fees">Toll Fees</option>
              <option value="Overall Cost Analysis">Overall Cost Analysis</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit_period">Period</label>
            <input type="text" id="edit_period" name="period" required placeholder="e.g., Q3 2023, October 2023">
          </div>
          <div class="form-group">
            <label for="edit_status">Status</label>
            <select id="edit_status" name="status" required>
              <option value="Implemented">Implemented - Cost savings achieved</option>
              <option value="In Progress">In Progress - Implementation ongoing</option>
              <option value="Pending Review">Pending Review - Awaiting approval</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit_notes">Notes</label>
            <textarea id="edit_notes" name="notes" rows="3" placeholder="Enter detailed findings, recommendations, or implementation notes..."></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="update_report" class="btn btn-primary">Update Report</button>
          <button type="button" class="btn" onclick="closeModal('edit-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Delete Report</div>
        <span class="close" onclick="closeModal('delete-modal')">&times;</span>
      </div>
      <form method="POST" id="delete-form">
        <input type="hidden" id="delete_report_id" name="report_id" value="">
        <div class="delete-confirmation">
          <p>Are you sure you want to delete the report <span id="delete-report-id" style="font-weight: bold;"></span> (<span id="delete-report-category" style="font-weight: bold;"></span>)?</p>
          <p>This action cannot be undone.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_report" class="btn btn-delete">Delete</button>
          <button type="button" class="btn" onclick="closeModal('delete-modal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="success-modal" id="success-modal">
    <div class="success-modal-content">
      <div class="<?php echo (strpos($successMessage, 'Error') === false && !empty($successMessage)) ? 'success-icon' : 'error-icon'; ?>">
        <i class="fas <?php echo (strpos($successMessage, 'Error') === false && !empty($successMessage)) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
      </div>
      <div class="success-title" id="success-title">
        <?php echo (strpos($successMessage, 'Error') === false && !empty($successMessage)) ? 'Success!' : 'Error!'; ?>
      </div>
      <div class="success-message" id="success-message">
        <?php echo $successMessage; ?>
      </div>
      <div class="success-actions">
        <button class="btn btn-primary" onclick="closeModal('success-modal')">OK</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    function editReport(reportId) {
      console.log('Editing report:', reportId);
      
      // Fetch report data via AJAX using the config.php endpoint
      fetch('config.php?action=get_report&id=' + reportId)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          console.log('Received data:', data);
          
          if (data.error) {
            alert(data.error);
            return;
          }
          
          const modal = document.getElementById("edit-modal");
          document.getElementById('edit_report_id').value = data.id;
          document.getElementById('edit_report_id_display').value = data.report_id;
          document.getElementById('edit_category').value = data.category;
          document.getElementById('edit_period').value = data.period;
          document.getElementById('edit_status').value = data.status;
          document.getElementById('edit_notes').value = data.notes || '';
          
          modal.style.display = "flex";
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error fetching report data: ' + error.message);
        });
    }

    function deleteReport(reportId, reportCode, reportCategory) {
      console.log('Deleting report:', reportId, reportCode, reportCategory);
      
      const modal = document.getElementById("delete-modal");
      document.getElementById('delete_report_id').value = reportId;
      document.getElementById('delete-report-id').textContent = reportCode;
      document.getElementById('delete-report-category').textContent = reportCategory;
      modal.style.display = "flex";
    }

    function openModal(type) {
      console.log('Opening modal:', type);
      
      if (type === 'generate-report') {
        const modal = document.getElementById("generate-report-modal");
        modal.style.display = "flex";
      }
    }

    function closeModal(modalId) {
      console.log('Closing modal:', modalId);
      document.getElementById(modalId).style.display = "none";
    }

    window.onclick = function (event) {
      const modals = ['generate-report-modal', 'edit-modal', 'delete-modal', 'success-modal'];
      
      modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
          modal.style.display = "none";
        }
      });
    };

    // Show success modal if there's a success message
    <?php if (!empty($successMessage)): ?>
    window.onload = function() {
      const successModal = document.getElementById("success-modal");
      if (successModal) {
        successModal.style.display = "flex";
      }
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
      
      // Add console logging for debugging
      console.log('Page loaded successfully');
      
      // Test if editReport function is available
      console.log('editReport function available:', typeof editReport === 'function');
    });
  </script>
</body>
</html>