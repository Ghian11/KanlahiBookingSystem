<?php
/**
 * Database Configuration
 * Handles database connection using PDO
 */

$host = 'localhost';
$db   = 'kanlahi_booking';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log the error
    error_log("Database connection failed: " . $e->getMessage());
    
    // For API endpoints, return JSON error
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed', 'message' => 'Unable to connect to database']);
        exit();
    }
    
    // For regular pages, throw exception
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>