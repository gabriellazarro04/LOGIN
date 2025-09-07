<?php
// config.php
$Connections = mysqli_connect("localhost:3306", "root", "", "log2");

// Check connection
if (!$Connections) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to execute queries safely
function executeQuery($sql, $params = []) {
    global $Connections;
    
    // Prepare statement
    $stmt = mysqli_prepare($Connections, $sql);
    if (!$stmt) {
        die("Error preparing statement: " . mysqli_error($Connections));
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
            } elseif (is_null($param)) {
                $types .= 's'; // treat NULL as string
                $param = ''; // set to empty string for binding
            } else {
                $types .= 's'; // string
            }
            $values[] = $param;
        }
        
        // Bind parameters using reference to avoid strict standards warning
        $bindParams = [$stmt, $types];
        foreach ($values as &$value) {
            $bindParams[] = &$value;
        }
        
        call_user_func_array('mysqli_stmt_bind_param', $bindParams);
    }
    
    // Execute statement
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    
    // Check if this is a SELECT query by looking for result set
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result !== false) {
        // For SELECT queries, return the result set
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    } else {
        // For INSERT, UPDATE, DELETE queries, return affected rows
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affectedRows;
    }
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
        die("Error preparing statement: " . mysqli_error($Connections));
    }
    
    if (!empty($params)) {
        $types = '';
        $values = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_null($param)) {
                $types .= 's'; // treat NULL as string
                $param = ''; // set to empty string for binding
            } else {
                $types .= 's';
            }
            $values[] = $param;
        }
        
        // Bind parameters using reference to avoid strict standards warning
        $bindParams = [$stmt, $types];
        foreach ($values as &$value) {
            $bindParams[] = &$value;
        }
        
        call_user_func_array('mysqli_stmt_bind_param', $bindParams);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
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
?>