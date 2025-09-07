<?php
// config.php
include 'Connections.php';

// Function to execute queries safely
function executeQuery($sql, $params = []) {
    global $Connections;
    
    // Prepare statement
    $stmt = mysqli_prepare($Connections, $sql);
    if (!$stmt) {
        error_log("Error preparing statement: " . mysqli_error($Connections));
        return false;
    }
    
    // Bind parameters if any
    if (!empty($params)) {
        $types = '';
        $values = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i'; // integer
            } elseif (is_float($param)) {
                $types .= 'd'; // double
            } else {
                $types .= 's'; // string
            }
            $values[] = $param;
        }
        
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }
    
    // Execute statement
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    // Get result for SELECT queries
    $result = mysqli_stmt_get_result($stmt);
    
    // For SELECT queries, return the result set
    if ($result !== false) {
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
    
    // For INSERT, UPDATE, DELETE queries, return true if successful
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    // Return true if any rows were affected, false otherwise
    return $affectedRows > 0;
}

// Function to fetch a single row
function fetchSingle($sql, $params = []) {
    $result = executeQuery($sql, $params);
    return !empty($result) ? $result[0] : null;
}

// Function to insert data and return the inserted ID
function insertAndGetId($sql, $params = []) {
    global $Connections;
    
    $stmt = mysqli_prepare($Connections, $sql);
    if (!$stmt) {
        error_log("Error preparing statement: " . mysqli_error($Connections));
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        $values = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $param;
        }
        
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $insertId = mysqli_stmt_insert_id($stmt);
    mysqli_stmt_close($stmt);
    
    return $insertId;
}

// Function to check if a table exists
function tableExists($tableName) {
    global $Connections;
    $result = mysqli_query($Connections, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// Function to get the last error
function getDbError() {
    global $Connections;
    return mysqli_error($Connections);
}
?>