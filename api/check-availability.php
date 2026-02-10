<?php
/**
 * API Endpoint for Checking Venue Availability
 */

require_once '../config/database.php';
require_once '../config/functions.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['schedule']) || !isset($input['place'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data']);
    exit();
}

$schedule = $input['schedule'];
$place = $input['place'];

// Validate inputs
if (empty($schedule) || empty($place)) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule and place are required']);
    exit();
}

try {
    // Check if venue is available
    $date = date('Y-m-d', strtotime($schedule));
    $available = isVenueAvailableForBooking($place, $date);
    
    // Get venue details for capacity info
    $stmt = $pdo->prepare("SELECT capacity FROM venues WHERE name = ?");
    $stmt->execute([$place]);
    $venue = $stmt->fetch();
    
    $response = [
        'available' => $available,
        'date' => $date,
        'place' => $place,
        'capacity' => $venue ? $venue['capacity'] : null,
        'message' => $available ? 'Venue is available for the selected date and time.' : 'This venue is not available on the selected date. Please choose another date.'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Availability check error: " . $e->getMessage());
    
    // Return a safe fallback response
    $response = [
        'available' => true, // Assume available as fallback
        'date' => $date ?? date('Y-m-d', strtotime($schedule)),
        'place' => $place,
        'capacity' => null,
        'message' => 'Unable to check availability. Please try again or contact support.',
        'error' => 'System error occurred'
    ];
    
    http_response_code(500);
    echo json_encode($response);
}
