<?php
// Include database connection
require_once 'Connections.php';

// Fetch communication actions from database
$actions = [];
$sql = "SELECT ca.*, u.username 
        FROM communication_actions ca 
        JOIN users u ON ca.created_by = u.id 
        ORDER BY ca.created_at DESC";
$result = mysqli_query($Connections, $sql);
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $actions[] = $row;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_action'])) {
        $action_name = mysqli_real_escape_string($Connections, $_POST['action_name']);
        $action_type = mysqli_real_escape_string($Connections, $_POST['action_type']);
        $target_vehicle = mysqli_real_escape_string($Connections, $_POST['target_vehicle']);
        $priority_level = mysqli_real_escape_string($Connections, $_POST['priority_level']);
        $message_content = mysqli_real_escape_string($Connections, $_POST['message_content']);
        $created_by = 2; // Assuming dispatcher is creating (ID 2 from sample data)
        
        // Insert action
        $sql = "INSERT INTO communication_actions 
                (action_name, action_type, target_vehicle, priority_level, message_content, created_by) 
                VALUES ('$action_name', '$action_type', '$target_vehicle', '$priority_level', '$message_content', $created_by)";
        
        if (mysqli_query($Connections, $sql)) {
            // Action added successfully
            echo json_encode(['status' => 'success', 'message' => 'Action added successfully!']);
            exit();
        } else {
            // Error handling
            echo json_encode(['status' => 'error', 'message' => 'Error adding action: ' . mysqli_error($Connections)]);
            exit();
        }
    }
    
    if (isset($_POST['update_action'])) {
        $action_id = mysqli_real_escape_string($Connections, $_POST['action_id']);
        $action_name = mysqli_real_escape_string($Connections, $_POST['action_name']);
        $action_type = mysqli_real_escape_string($Connections, $_POST['action_type']);
        $target_vehicle = mysqli_real_escape_string($Connections, $_POST['target_vehicle']);
        $priority_level = mysqli_real_escape_string($Connections, $_POST['priority_level']);
        $message_content = mysqli_real_escape_string($Connections, $_POST['message_content']);
        
        // Update action
        $sql = "UPDATE communication_actions 
                SET action_name='$action_name', action_type='$action_type', 
                    target_vehicle='$target_vehicle', priority_level='$priority_level', 
                    message_content='$message_content', updated_at=NOW() 
                WHERE id=$action_id";
        
        if (mysqli_query($Connections, $sql)) {
            // Action updated successfully
            echo json_encode(['status' => 'success', 'message' => 'Action updated successfully!']);
            exit();
        } else {
            // Error handling
            echo json_encode(['status' => 'error', 'message' => 'Error updating action: ' . mysqli_error($Connections)]);
            exit();
        }
    }
    
    if (isset($_POST['delete_action'])) {
        $action_id = mysqli_real_escape_string($Connections, $_POST['action_id']);
        
        // Delete action
        $sql = "DELETE FROM communication_actions WHERE id=$action_id";
        
        if (mysqli_query($Connections, $sql)) {
            // Action deleted successfully
            echo json_encode(['status' => 'success', 'message' => 'Action deleted successfully!']);
            exit();
        } else {
            // Error handling
            echo json_encode(['status' => 'error', 'message' => 'Error deleting action: ' . mysqli_error($Connections)]);
            exit();
        }
    }
    
    // Handle AJAX request for getting a single action
    if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_action' && isset($_GET['id'])) {
        $action_id = mysqli_real_escape_string($Connections, $_GET['id']);
        $sql = "SELECT ca.*, u.username 
                FROM communication_actions ca 
                JOIN users u ON ca.created_by = u.id 
                WHERE ca.id = $action_id";
        $result = mysqli_query($Connections, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $action = mysqli_fetch_assoc($result);
            echo json_encode($action);
        } else {
            echo json_encode(['error' => 'Action not found']);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Communication Action Access</title>
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
      min-height: 100vh;
    }

    .page-header {
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

    .refresh-btn {
      background-color: #9a66ff;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .refresh-btn:hover {
      background-color: #8253e0;
    }

    /* Communication Actions */
    .actions-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .action-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      transition: transform 0.3s ease;
    }

    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .action-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .action-title {
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
    }

    .action-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      background-color: rgba(155, 102, 255, 0.1);
      color: #9a66ff;
    }

    .action-attributes {
      margin-bottom: 15px;
    }

    .attribute {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #f5f5f5;
    }

    .attribute-name {
      font-weight: 600;
      color: #7f8c8d;
    }

    .attribute-value {
      color: #2c3e50;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
    }

    .btn {
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
    }

    .btn-view {
      background-color: #3498db;
      color: white;
    }

    .btn-view:hover {
      background-color: #2980b9;
    }

    .btn-edit {
      background-color: #9a66ff;
      color: white;
    }

    .btn-edit:hover {
      background-color: #7947ddff;
    }

    .btn-delete {
      background-color: #e74c3c;
      color: white;
    }

    .btn-delete:hover {
      background-color: #c0392b;
    }

    .btn-add {
      background-color: #9a66ff;
      color: white;
      padding: 10px 15px;
      margin-bottom: 20px;
    }

    .btn-add:hover {
      background-color: #27ae60;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background-color: white;
      padding: 25px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }

    .modal-title {
      font-size: 20px;
      font-weight: 600;
      color: #2c3e50;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #636e72;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
      color: #2c3e50;
    }

    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      background-color: #4cd964;
      color: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      display: none;
      z-index: 1001;
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
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

      .actions-container {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
   <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Viahale Logo">
    </div>
    <ul>
      <li><a href="dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li><a href="RT.php"><i class="fas fa-user me-2"></i> RT Communication</a></li>
      <li class="active"><a href="CC.php"><i class="fas fa-check-circle me-2"></i> Com. Center Access</a></li>
    </ul>
    <div class="bottom-links">
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="#"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Communication Action Access</h1>
      <button class="refresh-btn" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>

    <button class="btn btn-add" onclick="showModal('addModal')">
      <i class="fas fa-plus"></i> Add New Action
    </button>

    <!-- Communication Actions -->
    <div class="actions-container" id="actions-container">
    <?php if (empty($actions)): ?>
    <div style="text-align: center; padding: 20px; color: #777; grid-column: 1 / -1;">
        No communication actions found. <a href="#" onclick="showModal('addModal')">Create your first action</a>.
    </div>
    <?php else: ?>
        <?php foreach ($actions as $action): ?>
        <div class="action-card">
            <div class="action-header">
                <div class="action-title"><?php echo $action['action_name']; ?></div>
                <div class="action-icon">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
            </div>
            <div class="action-attributes">
                <div class="attribute">
                    <span class="attribute-name">Type:</span>
                    <span class="attribute-value"><?php echo $action['action_type']; ?></span>
                </div>
                <div class="attribute">
                    <span class="attribute-name">Target:</span>
                    <span class="attribute-value"><?php echo $action['target_vehicle']; ?></span>
                </div>
                <div class="attribute">
                    <span class="attribute-name">Priority:</span>
                    <span class="attribute-value"><?php echo $action['priority_level']; ?></span>
                </div>
                <div class="attribute">
                    <span class="attribute-name">Status:</span>
                    <span class="attribute-value"><?php echo $action['status']; ?></span>
                </div>
                <div class="attribute">
                    <span class="attribute-name">Created:</span>
                    <span class="attribute-value"><?php echo date('Y-m-d H:i', strtotime($action['created_at'])); ?></span>
                </div>
            </div>
            <div class="action-buttons">
                </button>
                <button class="btn btn-edit" onclick="editAction(<?php echo $action['id']; ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-delete" onclick="deleteAction(<?php echo $action['id']; ?>)">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
  </div>

  <!-- Add Action Modal -->
  <div class="modal" id="addModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Add Communication Action</h2>
        <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
      </div>
      
      <div class="form-group">
        <label for="actionName">Action Name</label>
        <input type="text" class="form-control" id="actionName" placeholder="Enter action name">
      </div>
      
      <div class="form-group">
        <label for="actionType">Action Type</label>
        <select class="form-control" id="actionType">
          <option value="">Select action type</option>
          <option value="Broadcast">Broadcast Message</option>
          <option value="Alert">Alert Notification</option>
          <option value="Request">Information Request</option>
          <option value="Update">Status Update</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="targetVehicle">Target Vehicle</label>
        <select class="form-control" id="targetVehicle">
          <option value="">Select target vehicle</option>
          <option value="Delivery Van #102">Delivery Van #102</option>
          <option value="Refrigerator Truck #205">Refrigerator Truck #205</option>
          <option value="Cargo Truck #308">Cargo Truck #308</option>
          <option value="Van #411">Van #411</option>
          <option value="All">All Vehicles</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="priorityLevel">Priority Level</label>
        <select class="form-control" id="priorityLevel">
          <option value="Low">Low</option>
          <option value="Normal" selected>Normal</option>
          <option value="High">High</option>
          <option value="Emergency">Emergency</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="messageContent">Message Content</label>
        <textarea class="form-control" id="messageContent" rows="3" placeholder="Enter message content"></textarea>
      </div>
      
      <div class="form-actions">
        <button class="btn btn-view" onclick="closeModal('addModal')">Cancel</button>
        <button class="btn btn-add" onclick="addAction()">Add Action</button>
      </div>
    </div>
  </div>

  <!-- Edit Action Modal -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Edit Communication Action</h2>
        <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
      </div>
      
      <input type="hidden" id="editId">
      
      <div class="form-group">
        <label for="editActionName">Action Name</label>
        <input type="text" class="form-control" id="editActionName" placeholder="Enter action name">
      </div>
      
      <div class="form-group">
        <label for="editActionType">Action Type</label>
        <select class="form-control" id="editActionType">
          <option value="Broadcast">Broadcast Message</option>
          <option value="Alert">Alert Notification</option>
          <option value="Request">Information Request</option>
          <option value="Update">Status Update</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="editTargetVehicle">Target Vehicle</label>
        <select class="form-control" id="editTargetVehicle">
          <option value="Delivery Van #102">Delivery Van #102</option>
          <option value="Refrigerator Truck #205">Refrigerator Truck #205</option>
          <option value="Cargo Truck #308">Cargo Truck #308</option>
          <option value="Van #411">Van #411</option>
          <option value="All">All Vehicles</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="editPriorityLevel">Priority Level</label>
        <select class="form-control" id="editPriorityLevel">
          <option value="Low">Low</option>
          <option value="Normal">Normal</option>
          <option value="High">High</option>
          <option value="Emergency">Emergency</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="editMessageContent">Message Content</label>
        <textarea class="form-control" id="editMessageContent" rows="3" placeholder="Enter message content"></textarea>
      </div>
      
      <div class="form-actions">
        <button class="btn btn-view" onclick="closeModal('editModal')">Cancel</button>
        <button class="btn btn-edit" onclick="updateAction()">Update Action</button>
      </div>
    </div>
  </div>

  <!-- View Action Modal -->
  <div class="modal" id="viewModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Communication Action Details</h2>
        <button class="close-btn" onclick="closeModal('viewModal')">&times;</button>
      </div>
      
      <div class="action-attributes">
        <div class="attribute">
          <span class="attribute-name">Action Name:</span>
          <span class="attribute-value" id="viewActionName"></span>
        </div>
        <div class="attribute">
          <span class="attribute-name">Action Type:</span>
          <span class="attribute-value" id="viewActionType"></span>
        </div>
        <div class="attribute">
          <span class="attribute-name">Target Vehicle:</span>
          <span class="attribute-value" id="viewTargetVehicle"></span>
        </div>
        <div class="attribute">
          <span class="attribute-name">Priority Level:</span>
          <span class="attribute-value" id="viewPriorityLevel"></span>
        </div>
        <div class="attribute">
          <span class="attribute-name">Message Content:</span>
          <span class="attribute-value" id="viewMessageContent"></span>
        </div>
        <div class="attribute">
          <span class="attribute-name">Created On:</span>
          <span class="attribute-value" id="viewCreatedOn"></span>
        </div>
        <div class="attribute">
          <span class="attribute-name">Status:</span>
          <span class="attribute-value" id="viewStatus"></span>
        </div>
      </div>
      
      <div class="form-actions">
        <button class="btn btn-view" onclick="closeModal('viewModal')">Close</button>
      </div>
    </div>
  </div>

  <div class="notification" id="notification">
    Operation completed successfully!
  </div>

  <script>
    // Modal functions
    function showModal(modalId) {
      document.getElementById(modalId).style.display = 'flex';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function showNotification(message) {
      const notification = document.getElementById('notification');
      notification.textContent = message;
      notification.style.display = 'block';
      
      setTimeout(() => {
        notification.style.display = 'none';
      }, 3000);
    }

    function refreshData() {
      window.location.reload();
    }

    // CRUD Operations
    function viewAction(id) {
      // Create AJAX request to get action details
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "CC.php?ajax=get_action&id=" + id, true);
      
      xhr.onreadystatechange = function() {
          if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
              const action = JSON.parse(this.responseText);
              
              document.getElementById('viewActionName').textContent = action.action_name;
              document.getElementById('viewActionType').textContent = action.action_type;
              document.getElementById('viewTargetVehicle').textContent = action.target_vehicle;
              document.getElementById('viewPriorityLevel').textContent = action.priority_level;
              document.getElementById('viewMessageContent').textContent = action.message_content;
              document.getElementById('viewCreatedOn').textContent = action.created_at;
              document.getElementById('viewStatus').textContent = action.status;
              
              showModal('viewModal');
          }
      }
      
      xhr.send();
    }

    function editAction(id) {
      // Create AJAX request to get action details for editing
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "CC.php?ajax=get_action&id=" + id, true);
      
      xhr.onreadystatechange = function() {
          if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
              const action = JSON.parse(this.responseText);
              
              document.getElementById('editId').value = action.id;
              document.getElementById('editActionName').value = action.action_name;
              document.getElementById('editActionType').value = action.action_type;
              document.getElementById('editTargetVehicle').value = action.target_vehicle;
              document.getElementById('editPriorityLevel').value = action.priority_level;
              document.getElementById('editMessageContent').value = action.message_content;
              
              showModal('editModal');
          }
      }
      
      xhr.send();
    }

    function updateAction() {
      const id = document.getElementById('editId').value;
      const actionName = document.getElementById('editActionName').value;
      const actionType = document.getElementById('editActionType').value;
      const targetVehicle = document.getElementById('editTargetVehicle').value;
      const priorityLevel = document.getElementById('editPriorityLevel').value;
      const messageContent = document.getElementById('editMessageContent').value;
      
      // Create form data
      var formData = new FormData();
      formData.append('update_action', '1');
      formData.append('action_id', id);
      formData.append('action_name', actionName);
      formData.append('action_type', actionType);
      formData.append('target_vehicle', targetVehicle);
      formData.append('priority_level', priorityLevel);
      formData.append('message_content', messageContent);
      
      // Create AJAX request
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "CC.php", true);
      
      xhr.onreadystatechange = function() {
          if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
              const response = JSON.parse(this.responseText);
              if (response.status === 'success') {
                  closeModal('editModal');
                  showNotification(response.message);
                  // Refresh the page to show updated action
                  setTimeout(function() {
                      window.location.reload();
                  }, 1000);
              } else {
                  showNotification(response.message);
              }
          }
      }
      
      xhr.send(formData);
    }

    function deleteAction(id) {
      if (confirm('Are you sure you want to delete this action?')) {
          // Create form data
          var formData = new FormData();
          formData.append('delete_action', '1');
          formData.append('action_id', id);
          
          // Create AJAX request
          var xhr = new XMLHttpRequest();
          xhr.open("POST", "CC.php", true);
          
          xhr.onreadystatechange = function() {
              if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                  const response = JSON.parse(this.responseText);
                  if (response.status === 'success') {
                      showNotification(response.message);
                      // Refresh the page
                      setTimeout(function() {
                          window.location.reload();
                      }, 1000);
                  } else {
                      showNotification(response.message);
                  }
              }
          }
          
          xhr.send(formData);
      }
    }

    // Function to add action via AJAX
    function addAction() {
      // Get form values
      var actionName = document.getElementById('actionName').value;
      var actionType = document.getElementById('actionType').value;
      var targetVehicle = document.getElementById('targetVehicle').value;
      var priorityLevel = document.getElementById('priorityLevel').value;
      var messageContent = document.getElementById('messageContent').value;
      
      // Validate form
      if (!actionName || !actionType || !targetVehicle || !priorityLevel || !messageContent) {
          showNotification('Please fill all fields');
          return;
      }
      
      // Create form data
      var formData = new FormData();
      formData.append('add_action', '1');
      formData.append('action_name', actionName);
      formData.append('action_type', actionType);
      formData.append('target_vehicle', targetVehicle);
      formData.append('priority_level', priorityLevel);
      formData.append('message_content', messageContent);
      
      // Create AJAX request
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "CC.php", true);
      
      xhr.onreadystatechange = function() {
          if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
              const response = JSON.parse(this.responseText);
              if (response.status === 'success') {
                  closeModal('addModal');
                  showNotification(response.message);
                  // Refresh the page to show new action
                  setTimeout(function() {
                      window.location.reload();
                  }, 1000);
              } else {
                  showNotification(response.message);
              }
          }
      }
      
      xhr.send(formData);
    }
  </script>
</body>
</html>