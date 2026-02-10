<?php
/**
 * Update Event API
 * Handles drag-and-drop updates and event modifications
 */

require_once '../config/database.php';
require_once '../config/auth.php';

// Check if user is authenticated
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'PUT') {
        // Parse PUT data
        parse_str(file_get_contents("php://input"), $putData);
        
        $id = $putData['id'] ?? null;
        $start = $putData['start'] ?? null;
        $end = $putData['end'] ?? null;
        $title = $putData['title'] ?? null;
        $description = $putData['description'] ?? null;
        $status = $putData['status'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Event ID is required']);
            exit;
        }
        
        // Update event
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET title = ?, start_event = ?, end_event = ?, description = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$title, $start, $end, $description, $status, $id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update event']);
        }
        
    } elseif ($method === 'DELETE') {
        // Parse DELETE data
        parse_str(file_get_contents("php://input"), $deleteData);
        
        $id = $deleteData['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Event ID is required']);
            exit;
        }
        
        // Delete event
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete event']);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>