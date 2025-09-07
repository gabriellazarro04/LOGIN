<?php
/**
 * Unified Database Configuration File
 * 
 * This file contains the configuration settings for connecting to the database.
 * It uses MySQLi for database connections and includes error handling.
 * Replace all individual connection files with this single config file.
 */

// Database configuration constants
define('DB_SERVER', 'localhost:3306');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'log2'); // Fixed: removed space before database name

// Attempt to connect to MySQL database
$Connections = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($Connections === false) {
    // Log error 
    error_log("[" . date('Y-m-d H:i:s') . "] Database connection failed: " . mysqli_connect_error());
    
    // Return JSON error for API calls or display message for regular pages
    if (php_sapi_name() === 'cli' || 
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json')) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Database connection failed']));
    } else {
        die("ERROR: Could not connect to the database. Please try again later.");
    }
}

// Set character set to utf8
mysqli_set_charset($Connections, "utf8");

// Function to safely execute queries (prevents SQL injection)
function executeQuery($query, $params = []) {
    global $Connections;
    
    if (!empty($params)) {
        // Prepare statement
        $stmt = mysqli_prepare($Connections, $query);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . mysqli_error($Connections));
            return false;
        }
        
        // Bind parameters if any
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        
        // Execute query
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Failed to execute statement: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return false;
        }
        
        // For SELECT queries, return result
        if (stripos($query, 'SELECT') === 0) {
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } else {
            // For INSERT/UPDATE/DELETE, return affected rows
            $affectedRows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $affectedRows;
        }
    } else {
        // Regular query execution
        $result = mysqli_query($Connections, $query);
        if (!$result) {
            error_log("Query failed: " . mysqli_error($Connections) . " - Query: " . $query);
        }
        return $result;
    }
}

// Function to safely fetch data (prevents SQL injection)
function safe_query($connection, $query, $params = []) {
    if (!empty($params)) {
        // Prepare statement
        $stmt = mysqli_prepare($connection, $query);
        if (!$stmt) {
            return ['error' => 'Failed to prepare statement'];
        }
        
        // Bind parameters if any
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        
        // Execute query
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return ['error' => 'Failed to execute query'];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        // Regular query execution
        $result = mysqli_query($connection, $query);
        if (!$result) {
            return ['error' => 'Query failed: ' . mysqli_error($connection)];
        }
        return $result;
    }
}

// Function to get report data
function get_report_data($report_id) {
    global $Connections;
    
    $query = "SELECT id, report_id, category, period, status, notes FROM cost_optimization_reports WHERE id = ?";
    $result = safe_query($Connections, $query, [$report_id]);
    
    if (is_array($result) && isset($result['error'])) {
        return $result;
    }
    
    if ($result && mysqli_num_rows($result) > 0) {
        $report = mysqli_fetch_assoc($result);
        return $report;
    } else {
        return ['error' => 'Report not found'];
    }
}

// Function to get expense data
function get_expense_data($expense_id) {
    global $Connections;
    
    $query = "SELECT * FROM trip_expenses WHERE id = ?";
    $result = safe_query($Connections, $query, [$expense_id]);
    
    if (is_array($result) && isset($result['error'])) {
        return $result;
    }
    
    if ($result && mysqli_num_rows($result) > 0) {
        $expense = mysqli_fetch_assoc($result);
        return $expense;
    } else {
        return ['error' => 'Expense not found'];
    }
}

// Function to get toll record data
function get_toll_record_data($toll_id) {
    global $Connections;
    
    $query = "SELECT * FROM toll_fees WHERE id = ?";
    $result = safe_query($Connections, $query, [$toll_id]);
    
    if (is_array($result) && isset($result['error'])) {
        return $result;
    }
    
    if ($result && mysqli_num_rows($result) > 0) {
        $toll = mysqli_fetch_assoc($result);
        return $toll;
    } else {
        return ['error' => 'Toll record not found'];
    }
}

// Function to get fuel record data
function get_fuel_record_data($fuel_id) {
    global $Connections;
    
    $query = "SELECT * FROM fuel_usage WHERE id = ?";
    $result = safe_query($Connections, $query, [$fuel_id]);
    
    if (is_array($result) && isset($result['error'])) {
        return $result;
    }
    
    if ($result && mysqli_num_rows($result) > 0) {
        $fuel = mysqli_fetch_assoc($result);
        return $fuel;
    } else {
        return ['error' => 'Fuel record not found'];
    }
}

// Function to get dashboard data
function get_dashboard_data() {
    global $Connections;
    
    $data = [];
    
    // Get fuel costs
    $fuel_query = "SELECT SUM(cost) as total FROM fuel_usage";
    $fuel_result = safe_query($Connections, $fuel_query);
    if (!is_array($fuel_result) && $fuel_result && $fuel_row = mysqli_fetch_assoc($fuel_result)) {
        $data['total_fuel_cost'] = floatval($fuel_row['total'] ?? 0);
    } else {
        $data['total_fuel_cost'] = 0;
    }
    
    // Get toll fees (only active ones)
    $toll_query = "SELECT SUM(fee_amount) as total FROM toll_fees WHERE status = 'Active'";
    $toll_result = safe_query($Connections, $toll_query);
    if (!is_array($toll_result) && $toll_result && $toll_row = mysqli_fetch_assoc($toll_result)) {
        $data['total_toll_fees'] = floatval($toll_row['total'] ?? 0);
    } else {
        $data['total_toll_fees'] = 0;
    }
    
    // Get trip expenses (only approved ones)
    $expense_query = "SELECT SUM(amount) as total FROM trip_expenses WHERE status = 'Approved'";
    $expense_result = safe_query($Connections, $expense_query);
    if (!is_array($expense_result) && $expense_result && $expense_row = mysqli_fetch_assoc($expense_result)) {
        $data['total_trip_expenses'] = floatval($expense_row['total'] ?? 0);
    } else {
        $data['total_trip_expenses'] = 0;
    }
    
    // Get potential savings from recommendations table
    $savings_query = "SELECT SUM(estimated_savings) as total FROM recommendations WHERE status = 'Pending'";
    $savings_result = safe_query($Connections, $savings_query);
    if (!is_array($savings_result) && $savings_result && $savings_row = mysqli_fetch_assoc($savings_result)) {
        $data['potential_savings'] = floatval($savings_row['total'] ?? 0);
    } else {
        $data['potential_savings'] = 0;
    }
    
    // Get recent activities from all tables
    $activities_query = "
        (SELECT 'Fuel' as type, CONCAT('Added fuel record: ', liters, 'L') as description, date as activity_date, cost as amount
         FROM fuel_usage 
         ORDER BY created_at DESC 
         LIMIT 3)
        UNION ALL
        (SELECT 'Toll' as type, CONCAT('Toll fee: ', toll_name) as description, effective_date as activity_date, fee_amount as amount
         FROM toll_fees 
         ORDER BY created_at DESC 
         LIMIT 3)
        UNION ALL
        (SELECT 'Expense' as type, CONCAT('Trip expense: ', expense_type) as description, expense_date as activity_date, amount
         FROM trip_expenses 
         ORDER BY created_at DESC 
         LIMIT 3)
        UNION ALL
        (SELECT 'Report' as type, CONCAT('Cost report: ', category) as description, created_at as activity_date, NULL as amount
         FROM cost_optimization_reports 
         ORDER BY created_at DESC 
         LIMIT 3)
        ORDER BY activity_date DESC 
        LIMIT 8";
    
    $activities_result = safe_query($Connections, $activities_query);
    $data['recent_activities'] = [];
    
    if (!is_array($activities_result)) {
        while ($activity = mysqli_fetch_assoc($activities_result)) {
            $data['recent_activities'][] = $activity;
        }
    }
    
    // Get optimization recommendations
    $recommendations_query = "SELECT * FROM recommendations ORDER BY created_at DESC LIMIT 5";
    $recommendations_result = safe_query($Connections, $recommendations_query);
    $data['recommendations'] = [];
    
    if (!is_array($recommendations_result)) {
        while ($recommendation = mysqli_fetch_assoc($recommendations_result)) {
            $data['recommendations'][] = $recommendation;
        }
    }
    
    return $data;
}

// Handle API requests if this file is called directly
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_report':
            if (isset($_GET['id'])) {
                $report_id = intval($_GET['id']);
                echo json_encode(get_report_data($report_id));
            } else {
                echo json_encode(['error' => 'No ID provided']);
            }
            break;
            
        case 'get_expense':
            if (isset($_GET['id'])) {
                $expense_id = intval($_GET['id']);
                echo json_encode(get_expense_data($expense_id));
            } else {
                echo json_encode(['error' => 'No ID provided']);
            }
            break;
            
        case 'get_toll_record':
            if (isset($_GET['id'])) {
                $toll_id = intval($_GET['id']);
                echo json_encode(get_toll_record_data($toll_id));
            } else {
                echo json_encode(['error' => 'No ID provided']);
            }
            break;
            
        case 'get_fuel_record':
            if (isset($_GET['id'])) {
                $fuel_id = intval($_GET['id']);
                echo json_encode(get_fuel_record_data($fuel_id));
            } else {
                echo json_encode(['error' => 'No ID provided']);
            }
            break;
            
        case 'get_dashboard_data':
            echo json_encode(get_dashboard_data());
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit;
}
