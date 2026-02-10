<?php
/**
 * Helper Functions
 * Common utility functions for the booking system
 */

require_once 'database.php';

// Ensure we're not in an API context when including functions
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === false) {
    // Only start session for non-API requests
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Generate unique 8-character alphanumeric reference number
 */
function generateReferenceNumber() {
    global $pdo;
    
    $attempts = 0;
    $max_attempts = 10;
    
    do {
        $ref_number = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));
        
        // Check if reference number already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE ref_number = ?");
        $stmt->execute([$ref_number]);
        $count = $stmt->fetchColumn();
        
        $attempts++;
        
        // If no conflicts found, return the reference number
        if ($count == 0) {
            return $ref_number;
        }
        
    } while ($attempts < $max_attempts);
    
    // Fallback: use timestamp-based reference
    return 'REF' . strtoupper(substr(md5(time() . mt_rand()), 0, 5));
}

/**
 * Get all venues
 */
function getVenues() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM venues ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get venue by ID
 */
function getVenueById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM venues WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all bookings with venue information
 */
function getBookings($filters = [], $limit = null) {
    global $pdo;
    
    $sql = "SELECT b.*, v.name as venue_name, v.capacity 
            FROM bookings b 
            JOIN venues v ON b.place = v.name";
    
    $conditions = [];
    $params = [];
    
    if (!empty($filters['venue_id'])) {
        $venue = getVenueById($filters['venue_id']);
        if ($venue) {
            $conditions[] = "b.place = ?";
            $params[] = $venue['name'];
        }
    }
    
    if (!empty($filters['status'])) {
        $conditions[] = "b.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = "(b.customer_name LIKE ? OR b.email LIKE ? OR b.ref_number LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get booking by ID
 */
function getBookingById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT b.*, v.name as venue_name, v.capacity FROM bookings b JOIN venues v ON b.place = v.name WHERE b.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update booking status and notes
 */
function updateBooking($id, $status, $internal_notes = '') {
    global $pdo;
    
    // Get current booking details
    $booking = getBookingById($id);
    if (!$booking) {
        return false;
    }
    
    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status = ?, internal_notes = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$status, $internal_notes, $id]);
    
    if ($result) {
        // Handle venue availability based on status
        handleVenueAvailability($booking['place'], $booking['start_event'], $status, $id);
    }
    
    return $result;
}

/**
 * Handle venue availability based on booking status
 */
function handleVenueAvailability($venue_name, $schedule, $status, $booking_id) {
    global $pdo;
    
    $event_date = date('Y-m-d', strtotime($schedule));
    
    if ($status === 'Confirmed') {
        // Block the date when booking is confirmed
        $stmt = $pdo->prepare("
            INSERT INTO venue_availability (venue_name, event_date, status, booking_id) 
            VALUES (?, ?, 'blocked', ?) 
            ON DUPLICATE KEY UPDATE status = 'blocked', booking_id = ?
        ");
        $stmt->execute([$venue_name, $event_date, $booking_id, $booking_id]);
    } elseif ($status === 'Completed') {
        // Make the date available when booking is completed
        $stmt = $pdo->prepare("
            UPDATE venue_availability 
            SET status = 'available' 
            WHERE venue_name = ? AND event_date = ? AND booking_id = ?
        ");
        $stmt->execute([$venue_name, $event_date, $booking_id]);
    } elseif ($status === 'New' || $status === 'Contacted') {
        // Remove availability record for pending bookings
        $stmt = $pdo->prepare("
            DELETE FROM venue_availability 
            WHERE venue_name = ? AND event_date = ? AND booking_id = ?
        ");
        $stmt->execute([$venue_name, $event_date, $booking_id]);
    }
}

/**
 * Check if booking can be created (additional validation)
 */
function canCreateBooking($place, $schedule) {
    global $pdo;
    
    $event_date = date('Y-m-d', strtotime($schedule));
    
    // Check if there's already a confirmed booking for this date
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE place = ? AND DATE(start_event) = ? AND status = 'Confirmed'
    ");
    $stmt->execute([$place, $event_date]);
    $confirmed_count = $stmt->fetchColumn();
    
    // If there's already a confirmed booking, cannot create new booking
    if ($confirmed_count > 0) {
        return false;
    }
    
    // Also check venue_availability table
    $stmt = $pdo->prepare("
        SELECT status FROM venue_availability 
        WHERE venue_name = ? AND event_date = ?
    ");
    $stmt->execute([$place, $event_date]);
    $result = $stmt->fetch();
    
    // If venue is blocked, cannot create new booking
    if ($result && $result['status'] === 'blocked') {
        return false;
    }
    
    return true;
}

/**
 * Check if venue is available on a specific date
 */
function isVenueAvailable($venue_name, $date) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT status FROM venue_availability 
            WHERE venue_name = ? AND event_date = ?
        ");
        $stmt->execute([$venue_name, $date]);
        $result = $stmt->fetch();
        
        // If no record exists, venue is available
        if (!$result) {
            return true;
        }
        
        // Venue is available only if status is 'available'
        return $result['status'] === 'available';
    } catch (Exception $e) {
        // Log error and return true (available) as fallback
        error_log("Error checking venue availability: " . $e->getMessage());
        return true;
    }
}

/**
 * Check if venue is available for booking (including future bookings)
 */
function isVenueAvailableForBooking($venue_name, $date) {
    global $pdo;
    
    try {
        // Check if there's already a confirmed booking for this date
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE place = ? AND DATE(start_event) = ? AND status = 'Confirmed'
        ");
        $stmt->execute([$venue_name, $date]);
        $confirmed_count = $stmt->fetchColumn();
        
        // If there's already a confirmed booking, venue is not available
        if ($confirmed_count > 0) {
            return false;
        }
        
        // Also check venue_availability table
        $stmt = $pdo->prepare("
            SELECT status FROM venue_availability 
            WHERE venue_name = ? AND event_date = ?
        ");
        $stmt->execute([$venue_name, $date]);
        $result = $stmt->fetch();
        
        // If no record exists, venue is available
        if (!$result) {
            return true;
        }
        
        // Venue is available only if status is 'available'
        return $result['status'] === 'available';
    } catch (Exception $e) {
        // Log error and return true (available) as fallback
        error_log("Error checking venue availability for booking: " . $e->getMessage());
        return true;
    }
}

/**
 * Get available dates for a venue
 */
function getAvailableDates($venue_name, $limit_days = 365) {
    global $pdo;
    
    $end_date = date('Y-m-d', strtotime("+$limit_days days"));
    
    $stmt = $pdo->prepare("
        SELECT event_date, status FROM venue_availability 
        WHERE venue_name = ? AND event_date <= ?
        ORDER BY event_date
    ");
    $stmt->execute([$venue_name, $end_date]);
    
    $availability = [];
    while ($row = $stmt->fetch()) {
        $availability[$row['event_date']] = $row['status'];
    }
    
    return $availability;
}

/**
 * Get booking statistics
 */
function getBookingStats() {
    global $pdo;
    
    $stats = [
        'total' => 0,
        'New' => 0,
        'Contacted' => 0,
        'Confirmed' => 0,
        'Completed' => 0
    ];
    
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $stats['total'] = $stmt->fetchColumn();
    
    // Count by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
    while ($row = $stmt->fetch()) {
        $stats[$row['status']] = $row['count'];
    }
    
    return $stats;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'New':
            return 'bg-primary';
        case 'Contacted':
            return 'bg-warning text-dark';
        case 'Confirmed':
            return 'bg-success';
        case 'Completed':
            return 'bg-secondary';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get current admin user information
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
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