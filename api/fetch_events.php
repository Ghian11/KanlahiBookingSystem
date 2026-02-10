<?php
/**
 * Fetch Events API
 * Returns bookings data for FullCalendar
 */

require_once '../config/database.php';
require_once '../config/auth.php';

// Check if user is authenticated
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, title, start_event as start, end_event as end, 
               description, status, customer_name, place
        FROM bookings 
        ORDER BY start_event
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format events for FullCalendar
    $events = [];
    foreach ($bookings as $booking) {
        $event = [
            'id' => $booking['id'],
            'title' => $booking['title'],
            'start' => $booking['start'],
            'end' => $booking['end'],
            'description' => $booking['description'],
            'status' => $booking['status'],
            'customer_name' => $booking['customer_name'],
            'place' => $booking['place'],
            'backgroundColor' => getStatusColor($booking['status']),
            'borderColor' => getStatusColor($booking['status']),
            'textColor' => '#ffffff'
        ];
        $events[] = $event;
    }
    
    echo json_encode($events);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Fetch events error: " . $e->getMessage());
    
    // Return empty array as fallback
    $events = [];
    http_response_code(500);
    echo json_encode(['error' => 'Unable to fetch events', 'events' => $events]);
}

/**
 * Get color based on booking status
 */
function getStatusColor($status) {
    switch ($status) {
        case 'New':
            return '#007bff'; // Blue
        case 'Contacted':
            return '#ffc107'; // Yellow
        case 'Confirmed':
            return '#28a745'; // Green
        case 'Completed':
            return '#6c757d'; // Gray
        case 'Unavailable':
            return '#dc3545'; // Red
        default:
            return '#007bff';
    }
}
?>