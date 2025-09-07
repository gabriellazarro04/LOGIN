<?php
// get_action.php
require_once 'Connections.php';

if (isset($_GET['id'])) {
    $action_id = mysqli_real_escape_string($Connections, $_GET['id']);
    
    $sql = "SELECT * FROM communication_actions WHERE id = $action_id";
    $result = mysqli_query($Connections, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $action = mysqli_fetch_assoc($result);
        echo json_encode($action);
    } else {
        echo json_encode(['error' => 'Action not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>