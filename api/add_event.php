<?php
/**
 * Add Event API
 * Handles new event creation via calendar
 */

require_once '../config/database.php';
require_once '../config/auth.php';

// Check if user is authenticated
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? null;
    $start = $data['start'] ?? null;
    $end = $data['end'] ?? null;
    $description = $data['description'] ?? null;
    $status = $data['status'] ?? 'New';
    $customer_name = $data['customer_name'] ?? null;
    $place = $data['place'] ?? null;
    
    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    
    if (empty($start)) {
        $errors[] = 'Start date is required';
    }
    
    if (empty($end)) {
        $errors[] = 'End date is required';
    }
    
    if (!empty($start) && !empty($end) && strtotime($start) >= strtotime($end)) {
        $errors[] = 'End date must be after start date';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => $errors]);
        exit;
    }
    
    // Check for conflicts
    $stmt = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE ((start_event < ? AND end_event > ?) OR (start_event < ? AND end_event > ?))
        AND id != ?
    ");
    $stmt->execute([$end, $start, $end, $start, null]);
    
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'This time slot conflicts with an existing booking']);
        exit;
    }
    
    // Insert new booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (title, start_event, end_event, description, status, customer_name, place)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([$title, $start, $end, $description, $status, $customer_name, $place]);
    
    if ($result) {
        $newId = $pdo->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'Event created successfully',
            'id' => $newId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create event']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>