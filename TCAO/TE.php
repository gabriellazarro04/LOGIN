<?php 
include 'config.php';

// Initialize success message variable
$successMessage = '';
$isError = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_expense'])) {
        // Add new expense using prepared statements
        $trip_id = $_POST['trip_id'];
        $driver_id = $_POST['driver_id'];
        $expense_type = $_POST['expense_type'];
        $amount = $_POST['amount'];
        $expense_date = $_POST['expense_date'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        
        $sql = "INSERT INTO trip_expenses (trip_id, driver_id, expense_type, amount, expense_date, status, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $result = executeQuery($sql, [$trip_id, $driver_id, $expense_type, $amount, $expense_date, $status, $description]);
        
        if ($result !== false) {
            $successMessage = 'Expense added successfully!';
        } else {
            $successMessage = 'Error adding expense!';
            $isError = true;
        }
    }
    
    if (isset($_POST['edit_expense'])) {
        // Edit existing expense using prepared statements
        $expense_id = $_POST['expense_id'];
        $trip_id = $_POST['trip_id'];
        $driver_id = $_POST['driver_id'];
        $expense_type = $_POST['expense_type'];
        $amount = $_POST['amount'];
        $expense_date = $_POST['expense_date'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        
        $sql = "UPDATE trip_expenses SET trip_id=?, driver_id=?, expense_type=?, amount=?, expense_date=?, status=?, description=?
                WHERE id=?";
        
        $result = executeQuery($sql, [$trip_id, $driver_id, $expense_type, $amount, $expense_date, $status, $description, $expense_id]);
        
        if ($result !== false) {
            $successMessage = 'Expense updated successfully!';
        } else {
            $successMessage = 'Error updating expense!';
            $isError = true;
        }
    }
    
    if (isset($_POST['delete_expense'])) {
        // Delete expense using prepared statements
        $expense_id = $_POST['expense_id'];
        
        $sql = "DELETE FROM trip_expenses WHERE id=?";
        $result = executeQuery($sql, [$expense_id]);
        
        if ($result !== false) {
            $successMessage = 'Expense deleted successfully!';
        } else {
            $successMessage = 'Error deleting expense!';
            $isError = true;
        }
    }
}

// Fetch trip expenses data with driver information
$expenses_query = "SELECT te.*, CONCAT(d.first_name, ' ', d.last_name) as driver_name 
                   FROM trip_expenses te 
                   LEFT JOIN drivers d ON te.driver_id = d.driver_id 
                   ORDER BY te.expense_date DESC";
$expenses_result = executeQuery($expenses_query);

// Fetch trips for dropdown
$trips_query = "SELECT t.trip_id, CONCAT('TRIP-', t.trip_id, ' (', c.first_name, ' ', c.last_name, ')') as trip_display 
                FROM trips t 
                JOIN customers c ON t.customer = c.customer_id 
                ORDER BY t.trip_id DESC";
$trips_result = executeQuery($trips_query);

// Fetch drivers for dropdown
$drivers_query = "SELECT driver_id, CONCAT(first_name, ' ', last_name) as driver_name FROM drivers ORDER BY first_name";
$drivers_result = executeQuery($drivers_query);

// Store trips and drivers in arrays for later use
$trips = [];
if ($trips_result && mysqli_num_rows($trips_result) > 0) {
    while($trip = mysqli_fetch_assoc($trips_result)) {
        $trips[] = $trip;
    }
}

$drivers = [];
if ($drivers_result && mysqli_num_rows($drivers_result) > 0) {
    while($driver = mysqli_fetch_assoc($drivers_result)) {
        $drivers[] = $driver;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trip Expenses Management</title>
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
      background-color: 'f8f9fa';
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

    .pending {
      background-color: #fef5e7;
      color: #f39c12;
    }

    .rejected {
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
 <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Logo">
    </div>
    <ul>
      <li><a href="tcao-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li><a href="FU.php"><i class="fas fa-gas-pump me-2"></i> Fuel Usage </a></li>
      <li><a href="TF.php"><i class="fas fa-arrows-alt"></i> Toll Fees </a></li>
      <li class="active"><a href="TE.php"><i class="fas fa-bolt"></i> Trip Expenses </a></li>
      <li><a href="CO.php"><i class="fas fa-money-bill"></i> Cost Optimization </a></li>
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
      <button class="btn btn-primary" onclick="openModal('add-expense')">
        <i class="fas fa-plus"></i> Add Expense
      </button>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Trip Expenses</div>
        </div>
        <div class="expenses-table">
          <table>
            <thead>
              <tr>
                <th>Trip ID</th>
                <th>Driver</th>
                <th>Expense Type</th>
                <th>Amount (₱)</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($expenses_result && mysqli_num_rows($expenses_result) > 0) {
                  while($row = mysqli_fetch_assoc($expenses_result)) {
                      $status_class = '';
                      if ($row['status'] == 'Approved') $status_class = 'completed';
                      else if ($row['status'] == 'Pending') $status_class = 'pending';
                      else if ($row['status'] == 'Rejected') $status_class = 'rejected';
                      
                      echo "<tr>
                          <td>TRIP-{$row['trip_id']}</td>
                          <td>{$row['driver_name']}</td>
                          <td>{$row['expense_type']}</td>
                          <td>₱" . number_format($row['amount'], 2) . "</td>
                          <td>{$row['expense_date']}</td>
                          <td><span class='status $status_class'>{$row['status']}</span></td>
                          <td>
                            <button class='btn btn-edit' onclick='editExpense({$row['id']})'>
                              <i class='fas fa-edit'></i>
                            </button>
                            <button class='btn btn-delete' onclick='deleteExpense({$row['id']}, \"TRIP-{$row['trip_id']}\", \"{$row['expense_type']}\")'>
                              <i class='fas fa-trash'></i>
                            </button>
                          </td>
                      </tr>";
                  }
              } else {
                  echo "<tr><td colspan='7' style='text-align: center;'>No trip expenses found</td></tr>";
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
        <div class="modal-title" id="modal-title">Add Expense</div>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      <form method="POST" id="expense-form">
        <input type="hidden" id="expense_id" name="expense_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label for="trip_id">Trip ID</label>
            <select id="trip_id" name="trip_id" required>
              <option value="">Select Trip</option>
              <?php
              foreach ($trips as $trip) {
                  echo "<option value='{$trip['trip_id']}'>{$trip['trip_display']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label for="driver_id">Driver</label>
            <select id="driver_id" name="driver_id" required>
              <option value="">Select Driver</option>
              <?php
              foreach ($drivers as $driver) {
                  echo "<option value='{$driver['driver_id']}'>{$driver['driver_name']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label for="expense_type">Expense Type</label>
            <select id="expense_type" name="expense_type" required>
              <option value="">Select Expense Type</option>
              <option value="Toll Fee">Toll Fee</option>
              <option value="Parking">Parking</option>
              <option value="Maintenance">Maintenance</option>
              <option value="Emergency Repair">Emergency Repair</option>
              <option value="Cleaning">Cleaning</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="amount">Amount (₱)</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required placeholder="0.00">
          </div>
          <div class="form-group">
            <label for='expense_date'>Date</label>
            <input type="date" id="expense_date" name="expense_date" required>
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="Pending">Pending</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
            </select>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter expense description..."></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" id="submit-button" name="add_expense" class="btn btn-primary">Save</button>
          <button type="button" class="btn" onclick="closeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="delete-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Delete Expense</div>
        <span class="close" onclick="closeDeleteModal()">&times;</span>
      </div>
      <form method="POST" id="delete-form">
        <input type="hidden" id="delete_expense_id" name="expense_id" value="">
        <div class="delete-confirmation">
          <p>Are you sure you want to delete the expense for <span id="delete-trip-id" style="font-weight: bold;"></span> (<span id="delete-expense-type" style="font-weight: bold;"></span>)?</p>
          <p>This action cannot be undone.</p>
        </div>
        <div class="form-actions">
          <button type="submit" name="delete_expense" class="btn btn-delete">Delete</button>
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
      const form = document.getElementById("expense-form");
      const submitButton = document.getElementById("submit-button");

      if (type === "add-expense") {
        title.textContent = "Add Expense";
        form.reset();
        document.getElementById('expense_id').value = '';
        document.getElementById('expense_date').value = new Date().toISOString().split('T')[0];
        submitButton.name = 'add_expense';
        submitButton.textContent = 'Save';
        modal.style.display = "flex";
      }
    }

    function editExpense(expenseId) {
      // Fetch expense data via AJAX using the config.php endpoint
      fetch('config.php?action=get_expense&id=' + expenseId)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            alert(data.error);
            return;
          }
          
          const modal = document.getElementById("modal-form");
          const title = document.getElementById("modal-title");
          const form = document.getElementById("expense-form");
          const submitButton = document.getElementById("submit-button");
          
          title.textContent = "Edit Expense";
          document.getElementById('expense_id').value = data.id;
          document.getElementById('trip_id').value = data.trip_id;
          document.getElementById('driver_id').value = data.driver_id;
          document.getElementById('expense_type').value = data.expense_type;
          document.getElementById('amount').value = data.amount;
          document.getElementById('expense_date').value = data.expense_date;
          document.getElementById('status').value = data.status;
          document.getElementById('description').value = data.description || '';
          
          submitButton.name = 'edit_expense';
          submitButton.textContent = 'Update';
          
          modal.style.display = "flex";
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error fetching expense data');
        });
    }

    function deleteExpense(expenseId, tripId, expenseType) {
      const modal = document.getElementById("delete-modal");
      document.getElementById('delete_expense_id').value = expenseId;
      document.getElementById('delete-trip-id').textContent = tripId;
      document.getElementById('delete-expense-type').textContent = expenseType;
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