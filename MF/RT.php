<?php
// Include database connection
require_once 'Connections.php';

// Initialize variables
$vehicles = [];
$messages = [];
$selected_vehicle = null;
$uploads = [];

// Fetch vehicles from database
$sql = "SELECT * FROM vehicles ORDER BY status DESC, vehicle_name ASC";
$result = mysqli_query($Connections, $sql);
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $vehicles[] = $row;
    }
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if a vehicle is selected
if (isset($_GET['vehicle_id'])) {
    $vehicle_id = mysqli_real_escape_string($Connections, $_GET['vehicle_id']);
    
    // Get vehicle details
    $sql = "SELECT * FROM vehicles WHERE id = $vehicle_id";
    $result = mysqli_query($Connections, $sql);
    if (mysqli_num_rows($result) > 0) {
        $selected_vehicle = mysqli_fetch_assoc($result);
        
        // Fetch messages for this vehicle
        $sql = "SELECT m.*, u.first_name, u.last_name, mt.type_name, p.level_name as priority
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                JOIN message_types mt ON m.message_type_id = mt.id
                JOIN priorities p ON m.priority_id = p.id
                WHERE (m.receiver_id = $vehicle_id AND m.receiver_type = 'vehicle') 
                   OR m.sender_id = $vehicle_id
                ORDER BY m.sent_at ASC";
        $result = mysqli_query($Connections, $sql);
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $messages[] = $row;
            }
        }
        
        // Fetch uploads for this vehicle
        $sql = "SELECT vu.*, u.first_name, u.last_name 
                FROM vehicle_uploads vu 
                JOIN users u ON vu.uploaded_by = u.id 
                WHERE vu.vehicle_id = $vehicle_id 
                ORDER BY vu.uploaded_at DESC";
        $result = mysqli_query($Connections, $sql);
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $uploads[] = $row;
            }
        }
    }
} else {
    // Default to first vehicle if none selected
    if (!empty($vehicles)) {
        $selected_vehicle = $vehicles[0];
        $vehicle_id = $selected_vehicle['id'];
        
        // Fetch messages for this vehicle
        $sql = "SELECT m.*, u.first_name, u.last_name, mt.type_name, p.level_name as priority
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                JOIN message_types mt ON m.message_type_id = mt.id
                JOIN priorities p ON m.priority_id = p.id
                WHERE (m.receiver_id = $vehicle_id AND m.receiver_type = 'vehicle') 
                   OR m.sender_id = $vehicle_id
                ORDER BY m.sent_at ASC";
        $result = mysqli_query($Connections, $sql);
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $messages[] = $row;
            }
        }
        
        // Fetch uploads for this vehicle
        $sql = "SELECT vu.*, u.first_name, u.last_name 
                FROM vehicle_uploads vu 
                JOIN users u ON vu.uploaded_by = u.id 
                WHERE vu.vehicle_id = $vehicle_id 
                ORDER BY vu.uploaded_at DESC";
        $result = mysqli_query($Connections, $sql);
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $uploads[] = $row;
            }
        }
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['upload_file'])) {
    $vehicle_id = mysqli_real_escape_string($Connections, $_POST['vehicle_id']);
    $uploaded_by = 2; // Dispatcher ID
    
    // Use absolute path for upload directory
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/MF/uploads/';
    
    // Create directory if it doesn't exist with proper permissions
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $error = "Failed to create upload directory. Please check permissions.";
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        $error = "Upload directory is not writable. Please check permissions.";
    } else {
        $file_name = $_FILES['upload_file']['name'];
        $file_tmp = $_FILES['upload_file']['tmp_name'];
        $file_size = $_FILES['upload_file']['size'];
        $file_error = $_FILES['upload_file']['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types (including images)
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_error === 0) {
                if ($file_size <= 5000000) { // 5MB max
                    $file_name_new = uniqid('', true) . '.' . $file_ext;
                    $file_destination = $upload_dir . $file_name_new;
                    $file_path_in_db = 'uploads/' . $file_name_new; // Relative path for DB
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        // Insert into database - this works regardless of vehicle status
                        $sql = "INSERT INTO vehicle_uploads (vehicle_id, file_name, file_path, file_type, uploaded_by) 
                                VALUES ($vehicle_id, '$file_name', '$file_path_in_db', '$file_ext', $uploaded_by)";
                        
                        if (mysqli_query($Connections, $sql)) {
                            // Create a message about the file upload
                            $upload_id = mysqli_insert_id($Connections);
                            $message_content = "Uploaded file: " . $file_name . " [File ID: " . $upload_id . "]";
                            
                            $message_type_id = 1; // Status Update
                            $priority_id = 3; // Normal
                            
                            $sql_message = "INSERT INTO messages (sender_id, receiver_id, receiver_type, message_type_id, priority_id, content) 
                                            VALUES ($uploaded_by, $vehicle_id, 'vehicle', $message_type_id, $priority_id, '$message_content')";
                            
                            if (mysqli_query($Connections, $sql_message)) {
                                $success = "File uploaded successfully and notification sent!";
                                header("Location: RT.php?vehicle_id=$vehicle_id&upload_success=1");
                                exit();
                            } else {
                                $error = "File uploaded but message creation failed: " . mysqli_error($Connections);
                            }
                        } else {
                            $error = "Database error: " . mysqli_error($Connections);
                        }
                    } else {
                        $error = "Error uploading file. Please check directory permissions.";
                    }
                } else {
                    $error = "File size too large. Maximum size is 5MB.";
                }
            } else {
                $error = "Error uploading file. Error code: $file_error";
            }
        } else {
            $error = "Invalid file type. Allowed: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX";
        }
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_upload'])) {
    $upload_id = mysqli_real_escape_string($Connections, $_POST['upload_id']);
    $vehicle_id = mysqli_real_escape_string($Connections, $_POST['vehicle_id']);
    
    // Get file info before deleting
    $sql = "SELECT * FROM vehicle_uploads WHERE id = $upload_id";
    $result = mysqli_query($Connections, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $file_info = mysqli_fetch_assoc($result);
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/MF/' . $file_info['file_path'];
        
        // Delete from database
        $sql = "DELETE FROM vehicle_uploads WHERE id = $upload_id";
        if (mysqli_query($Connections, $sql)) {
            // Delete physical file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $success = "File deleted successfully!";
            header("Location: RT.php?vehicle_id=$vehicle_id&delete_success=1");
            exit();
        } else {
            $error = "Error deleting file: " . mysqli_error($Connections);
        }
    } else {
        $error = "File not found.";
    }
}

// Handle form submissions for messages
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_message']) || (isset($_POST['quick_message']) && $_POST['quick_message'] == '1')) {
        $sender_id = 2;
        $receiver_id = mysqli_real_escape_string($Connections, $_POST['vehicle_id']);
        $content = mysqli_real_escape_string($Connections, $_POST['message_content']);
        
        $message_type_id = 1;
        $priority_id = 3;
        
        $sql = "INSERT INTO messages (sender_id, receiver_id, receiver_type, message_type_id, priority_id, content) 
                VALUES ($sender_id, $receiver_id, 'vehicle', $message_type_id, $priority_id, '$content')";
        
        if (mysqli_query($Connections, $sql)) {
            // Check if this is an AJAX request
            if (isset($_POST['quick_message']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
                exit();
            } else {
                header("Location: RT.php?vehicle_id=$receiver_id&message_sent=1");
                exit();
            }
        } else {
            $error = "Error sending message: " . mysqli_error($Connections);
            // Check if this is an AJAX request
            if (isset($_POST['quick_message']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $error]);
                exit();
            }
        }
    }
}

// Display success/error messages from URL parameters
if (isset($_GET['upload_success'])) {
    $success = "File uploaded successfully!";
}
if (isset($_GET['message_sent'])) {
    $success = "Message sent successfully!";
}
if (isset($_GET['delete_success'])) {
    $success = "File deleted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleetCom - Real-Time Communication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }
        
        header {
            background: #9a66ff;
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            background-color: #4cd964;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .main-communication {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        @media (min-width: 768px) {
            .main-communication {
                grid-template-columns: 1fr 2fr;
            }
        }
        
        .vehicles-list {
            background-color: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-height: 500px;
            overflow-y: auto;
        }
        
        .vehicles-list h2 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #2d3436;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .vehicle-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .vehicle-item:hover {
            background-color: #f8f9fa;
        }
        
        .vehicle-item.active {
            background-color: #e3f2fd;
            border-left: 4px solid #1a73e8;
        }
        
        .vehicle-icon {
            width: 40px;
            height: 40px;
            background-color: #dfe6e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #636e72;
        }
        
        .vehicle-info {
            flex: 1;
        }
        
        .vehicle-name {
            font-weight: 600;
            color: #2d3436;
        }
        
        .vehicle-status {
            font-size: 0.8rem;
            color: #636e72;
        }
        
        .communication-panel {
            background-color: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        
        .panel-header {
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }
        
        .panel-header h2 {
            font-size: 1.2rem;
            color: #2d3436;
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
            max-height: 300px;
        }
        
        .uploads-container {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .uploads-container h3 {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #2d3436;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .upload-item {
            display: flex;
            align-items: center;
            padding: 8px;
            margin-bottom: 8px;
            background-color: white;
            border-radius: 6px;
            border-left: 4px solid #4cd964;
        }
        
        .upload-icon {
            width: 30px;
            height: 30px;
            background-color: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #1a73e8;
        }
        
        .upload-info {
            flex: 1;
        }
        
        .upload-name {
            font-weight: 500;
            color: #2d3436;
            font-size: 0.9rem;
        }
        
        .upload-time {
            font-size: 0.7rem;
            color: #636e72;
        }
        
        .upload-action {
            margin-left: 10px;
        }
        
        .download-btn {
            background-color: #9a66ff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .download-btn:hover {
            background-color: #713dd9ff;
        }
        
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            max-width: 80%;
        }
        
        .message.received {
            background-color: #e3f2fd;
            border-left: 4px solid #1a73e8;
            margin-right: auto;
        }
        
        .message.sent {
            background-color: #d1ecf1;
            border-right: 4px solid #17a2b8;
            margin-left: auto;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #636e72;
            text-align: right;
            margin-top: 5px;
        }
        
        .message-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 24px;
            outline: none;
            font-size: 1rem;
        }
        
        .send-btn {
            background-color: #9a66ff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .send-btn:hover {
            background-color: #6e38dbff;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background-color: #1a73e8;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #1557b0;
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #2d3436;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background-color: #e9ecef;
        }
        
        .btn-upload {
            background-color: #9a66ff;
            color: white;
        }
        
        .btn-upload:hover {
            background-color: #8253e0;
        }
        
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
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            font-size: 1.4rem;
            color: #2d3436;
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
            color: #2d3436;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .file-input {
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        
        .file-input:hover {
            border-color: #1a73e8;
        }
        
        .file-input input {
            display: none;
        }
        
        .file-input label {
            cursor: pointer;
            display: block;
            padding: 20px;
        }
        
        .file-input i {
            font-size: 2rem;
            color: #1a73e8;
            margin-bottom: 10px;
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
        
        .notification.error {
            background-color: #e74c3c;
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
        
        .search-box {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            margin-bottom: 15px;
            width: 100%;
            font-size: 1rem;
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
            .message.file-upload {
    background-color: #f0f7ff;
    border-left: 4px solid #4a90e2;
}

.message.file-upload p {
    margin: 0;
}

.message.file-upload a {
    color: #1a73e8;
    text-decoration: none;
    font-weight: 500;
}

.message.file-upload a:hover {
    text-decoration: underline;
}

.message.file-upload .fa-file {
    color: #4a90e2;
    margin-right: 5px;
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
            <li class="active"><a href="#"><i class="fas fa-user me-2"></i> RT Communication</a></li>
            <li><a href="CC.php"><i class="fas fa-check-circle me-2"></i> Com. Center Access </a></li>
        </ul>
        <div class="bottom-links">
            <a href="#"><i class="fas fa-user me-2"></i> Account</a>
            <a href="#"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-truck"></i> FleetCom - Real-Time Communication</h1>
                    <div class="status-indicator">
                        <div class="status-dot"></div>
                        <span>Connected</span>
                    </div>
                </div>
            </header>
            
            <!-- Display error/success messages -->
            <?php if (isset($error)): ?>
            <div class="notification error" style="display: block; position: relative; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
            <div class="notification" style="display: block; position: relative; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <div class="main-communication">
                <div class="vehicles-list">
                    <h2>Fleet Vehicles</h2>
                    <input type="text" class="search-box" placeholder="Search vehicles..." onkeyup="filterVehicles(this.value)">
                    
                    <?php foreach ($vehicles as $vehicle): ?>
                    <div class="vehicle-item <?php echo ($selected_vehicle && $selected_vehicle['id'] == $vehicle['id']) ? 'active' : ''; ?>" 
                         onclick="selectVehicle(<?php echo $vehicle['id']; ?>)">
                        <div class="vehicle-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="vehicle-info">
                            <div class="vehicle-name"><?php echo $vehicle['vehicle_name'] . ' ' . $vehicle['identifier']; ?></div>
                            <div class="vehicle-status">
                                <?php echo ucfirst($vehicle['status']) . ' - ' . ucfirst(str_replace('_', ' ', $vehicle['operational_status'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="communication-panel">
                    <div class="panel-header">
                        <h2 id="panel-title">
                            <?php if ($selected_vehicle): ?>
                            Communication with <?php echo $selected_vehicle['vehicle_name'] . ' ' . $selected_vehicle['identifier']; ?>
                            <?php else: ?>
                            Select a vehicle to communicate with
                            <?php endif; ?>
                        </h2>
                    </div>
                    
                   <div class="messages-container" id="messages-container">
    <?php if (empty($messages)): ?>
    <div style="text-align: center; padding: 20px; color: #777;">
        No messages yet.
    </div>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
        <?php
        $is_file_message = (strpos($message['content'], 'Uploaded file:') === 0);
        $message_class = ($message['sender_id'] == 2) ? 'sent' : 'received';
        if ($is_file_message) {
            $message_class .= ' file-upload';
        }
        ?>
        <div class="message <?php echo $message_class; ?>">
            <?php if ($is_file_message): ?>
                <?php
                // Extract file information from the message
                $file_info = explode('[File ID:', $message['content']);
                $file_name = trim(str_replace('Uploaded file:', '', $file_info[0]));
                $file_id = trim(str_replace(']', '', $file_info[1]));
                
                // Try to get the actual file info from the database
                $file_sql = "SELECT * FROM vehicle_uploads WHERE id = $file_id";
                $file_result = mysqli_query($Connections, $file_sql);
                
                if (mysqli_num_rows($file_result) > 0) {
                    $file_data = mysqli_fetch_assoc($file_result);
                    echo '<p><i class="fas fa-file"></i> Uploaded file: <a href="' . $file_data['file_path'] . '" download>' . $file_data['file_name'] . '</a></p>';
                } else {
                    echo '<p>' . $message['content'] . '</p>';
                }
                ?>
            <?php else: ?>
                <p><?php echo $message['content']; ?></p>
            <?php endif; ?>
            <div class="message-time">
                <?php echo date('h:i A', strtotime($message['sent_at'])); ?>
                <?php if ($message['sender_id'] == 2): ?>
                <br><small>By: <?php echo $message['first_name'] . ' ' . $message['last_name']; ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
                    
                    <?php if ($selected_vehicle): ?>
                    <div class="uploads-container">
                        <h3>Uploaded Files</h3>
                        <?php if (empty($uploads)): ?>
                            <div style="text-align: center; padding: 10px; color: #777;">
                                No files uploaded yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($uploads as $upload): ?>
                            <div class="upload-item">
                                <div class="upload-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="upload-info">
                                    <div class="upload-name"><?php echo $upload['file_name']; ?></div>
                                    <div class="upload-time">
                                        <?php echo date('M j, Y h:i A', strtotime($upload['uploaded_at'])); ?>
                                        <br><small>By: <?php echo $upload['first_name'] . ' ' . $upload['last_name']; ?></small>
                                    </div>
                                </div>
                                <div class="upload-actions">
    <a href="<?php echo $upload['file_path']; ?>" download class="download-btn">
        <i class="fas fa-download"></i> Download
    </a>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="upload_id" value="<?php echo $upload['id']; ?>">
        <input type="hidden" name="vehicle_id" value="<?php echo $selected_vehicle['id']; ?>">
        <button type="submit" name="delete_upload" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this file?')">
            <i class="fas fa-trash"></i> Delete
        </button>
    </form>
</div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <form id="quick-message-form" onsubmit="sendQuickMessage(event)">
                        <div class="message-form">
                            <input type="hidden" name="vehicle_id" value="<?php echo $selected_vehicle['id']; ?>">
                            <input type="text" class="message-input" name="message_content" placeholder="Type your message..." required>
                            <button type="submit" class="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <button class="btn btn-upload" onclick="showModal('uploadModal')">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upload Modal -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h2>Upload File</h2>
                    <button type="button" class="close-btn" onclick="closeModal('uploadModal')">&times;</button>
                </div>
                
                <input type="hidden" name="vehicle_id" value="<?php echo $selected_vehicle ? $selected_vehicle['id'] : ''; ?>">
                
                <div class="form-group">
                    <label>Select File (Max 5MB)</label>
                    <div class="file-input">
                        <input type="file" id="uploadFile" name="upload_file" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" required>
                        <label for="uploadFile">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to browse or drag & drop</p>
                            <small>Supported formats: JPG, PNG, GIF, PDF, DOC, DOCX</small>
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('uploadModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload File</button>
                </div>
            </form>
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
    
    // Vehicle selection
    function selectVehicle(vehicleId) {
        window.location.href = 'RT.php?vehicle_id=' + vehicleId;
    }
    
    // Filter vehicles
    function filterVehicles(searchTerm) {
        const vehicles = document.querySelectorAll('.vehicle-item');
        searchTerm = searchTerm.toLowerCase();
        
        vehicles.forEach(vehicle => {
            const vehicleName = vehicle.querySelector('.vehicle-name').textContent.toLowerCase();
            if (vehicleName.includes(searchTerm)) {
                vehicle.style.display = 'flex';
            } else {
                vehicle.style.display = 'none';
            }
        });
    }
    
    // File upload handling
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('uploadFile');
        if (fileInput.files.length === 0) {
            e.preventDefault();
            showNotification('Please select a file to upload.', 'error');
            return;
        }
        
        const file = fileInput.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            e.preventDefault();
            showNotification('File size exceeds 5MB limit.', 'error');
            return;
        }
    });
    
    // Send quick message from main input
    function sendQuickMessage(event) {
        event.preventDefault();
        
        const form = document.getElementById('quick-message-form');
        const formData = new FormData(form);
        formData.append('quick_message', '1'); // Add this parameter
        
        fetch('RT.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Add this header to identify AJAX requests
            }
        })
        .then(response => {
            // First check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, it's probably HTML (error case)
                throw new Error('Server returned HTML instead of JSON');
            }
        })
        .then(data => {
            if (data.status === 'success') {
                showNotification(data.message);
                form.querySelector('.message-input').value = '';
                
                // Instead of reloading, you could update the messages dynamically
                // For now, let's reload to see the new message
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error sending message. Please try again.', 'error');
        });
    }
    
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = 'notification';
        if (type === 'error') {
            notification.classList.add('error');
        }
        notification.style.display = 'block';
        
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
    
    // Close modals if clicked outside
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('modal');
        for (let i = 0; i < modals.length; i++) {
            if (event.target == modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    }
    
    // Auto-scroll to bottom of messages container
    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }
    
    // Scroll to bottom on page load
    window.addEventListener('load', scrollToBottom);
    
    // File input styling
    const fileInput = document.getElementById('uploadFile');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.files.length > 0) {
                label.querySelector('p').textContent = this.files[0].name;
            } else {
                label.querySelector('p').textContent = 'Click to browse or drag & drop';
            }
        });
    }
    // Improved modal handling with animation
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('active');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}
</script>
</body>
</html>
