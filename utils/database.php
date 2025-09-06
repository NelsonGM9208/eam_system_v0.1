<?php
/**
 * Database Utilities
 * Handles all database-related operations
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

/**
 * Get database connection with error handling
 * @return mysqli|false Database connection or false on failure
 */
function getDatabaseConnection() {
    static $con = null;
    
    if ($con === null) {
        // Database connection parameters
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "eam_system";
        
        // First, try to connect to MySQL server without specifying database
        $tempCon = new mysqli($servername, $username, $password);
        
        if ($tempCon->connect_error) {
            $errorMsg = "MySQL server connection failed: " . $tempCon->connect_error;
            logError($errorMsg);
            $con = false;
            return $con;
        }
        
        // Check if database exists
        $result = $tempCon->query("SHOW DATABASES LIKE '{$dbname}'");
        if ($result->num_rows == 0) {
            // Database doesn't exist, create it
            if ($tempCon->query("CREATE DATABASE {$dbname}")) {
                logError("Database '{$dbname}' created successfully");
            } else {
                $errorMsg = "Failed to create database: " . $tempCon->error;
                logError($errorMsg);
                $con = false;
                $tempCon->close();
                return $con;
            }
        }
        
        $tempCon->close();
        
        // Now connect to the specific database
        $con = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($con->connect_error) {
            $errorMsg = "Database connection failed: " . $con->connect_error;
            logError($errorMsg);
            $con = false;
        } else {
            // Set charset to utf8
            $con->set_charset("utf8");
        }
    }
    
    return $con;
}

/**
 * Initialize database tables if they don't exist
 * @return bool Success status
 */
function initializeDatabase() {
    $con = getDatabaseConnection();
    if (!$con) return false;
    
    // Check if users table exists
    $result = $con->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        // Create users table
        $createUsersTable = "
        CREATE TABLE `users` (
            `user_id` int(11) NOT NULL AUTO_INCREMENT,
            `firstname` varchar(50) NOT NULL,
            `lastname` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','teacher','student','sslg') NOT NULL DEFAULT 'student',
            `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
            `verification_status` enum('verified','notverified') NOT NULL DEFAULT 'notverified',
            `gender` varchar(10) DEFAULT NULL,
            `profile_photo` varchar(255) DEFAULT NULL,
            `lrn` varchar(20) DEFAULT NULL,
            `course` varchar(100) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        if (!$con->query($createUsersTable)) {
            logError("Failed to create users table: " . $con->error);
            return false;
        }
        
        logError("Users table created successfully");
    }
    
    return true;
}

/**
 * Execute a query with error handling
 * @param string $query SQL query
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types (i, s, d, b)
 * @return mysqli_result|false Query result or false on failure
 */
function executeQuery($query, $params = [], $types = '') {
    $con = getDatabaseConnection();
    if (!$con) return false;
    
    if (empty($params)) {
        $result = mysqli_query($con, $query);
        if (!$result) {
            logError("Query failed: " . mysqli_error($con));
            return false;
        }
        return $result;
    }
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        logError("Prepare failed: " . $con->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        logError("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

/**
 * Get count of records with error handling
 * @param string $table Table name
 * @param string $where WHERE clause (optional)
 * @param array $params Parameters for WHERE clause
 * @return int|false Record count or false on failure
 */
function getRecordCount($table, $where = '', $params = []) {
    $query = "SELECT COUNT(*) as total FROM {$table}";
    if (!empty($where)) {
        $query .= " WHERE {$where}";
    }
    
    $result = executeQuery($query, $params);
    if (!$result) return false;
    
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0);
}

/**
 * Get single record by ID
 * @param string $table Table name
 * @param int $id Record ID
 * @param string $idColumn ID column name (default: 'id')
 * @return array|false Record data or false on failure
 */
function getRecordById($table, $id, $idColumn = 'id') {
    $query = "SELECT * FROM {$table} WHERE {$idColumn} = ?";
    $result = executeQuery($query, [$id], 'i');
    if (!$result) return false;
    
    return $result->fetch_assoc();
}

/**
 * Insert record with error handling
 * @param string $table Table name
 * @param array $data Data to insert
 * @return int|false Insert ID or false on failure
 */
function insertRecord($table, $data) {
    if (empty($data)) return false;
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    
    $result = executeQuery($query, array_values($data), str_repeat('s', count($data)));
    if (!$result) return false;
    
    return getDatabaseConnection()->insert_id;
}

/**
 * Update record with error handling
 * @param string $table Table name
 * @param array $data Data to update
 * @param int $id Record ID
 * @param string $idColumn ID column name (default: 'id')
 * @return bool Success status
 */
function updateRecord($table, $data, $id, $idColumn = 'id') {
    if (empty($data)) return false;
    
    $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
    $query = "UPDATE {$table} SET {$setClause} WHERE {$idColumn} = ?";
    
    $params = array_values($data);
    $params[] = $id;
    
    $result = executeQuery($query, $params, str_repeat('s', count($data)) . 'i');
    return $result !== false;
}

/**
 * Delete record with error handling
 * @param string $table Table name
 * @param int $id Record ID
 * @param string $idColumn ID column name (default: 'id')
 * @return bool Success status
 */
function deleteRecord($table, $id, $idColumn = 'id') {
    $query = "DELETE FROM {$table} WHERE {$idColumn} = ?";
    $result = executeQuery($query, [$id], 'i');
    return $result !== false;
}
?>
