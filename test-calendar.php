<?php
/**
 * Test Calendar System
 * Add sample data to test the calendar functionality
 */

require_once 'config/database.php';

// Add sample bookings for testing
$sampleBookings = [
    [
        'title' => 'Wedding Reception',
        'start_event' => date('Y-m-d H:i:s', strtotime('+1 day 10:00')),
        'end_event' => date('Y-m-d H:i:s', strtotime('+1 day 18:00')),
        'description' => 'John & Jane Wedding Reception',
        'status' => 'Confirmed',
        'customer_name' => 'John Doe',
        'place' => 'Bulwagan Kanlahi'
    ],
    [
        'title' => 'Business Meeting',
        'start_event' => date('Y-m-d H:i:s', strtotime('+2 days 9:00')),
        'end_event' => date('Y-m-d H:i:s', strtotime('+2 days 17:00')),
        'description' => 'Quarterly business review meeting',
        'status' => 'New',
        'customer_name' => 'ABC Corporation',
        'place' => 'Conference Room'
    ],
    [
        'title' => 'Birthday Party',
        'start_event' => date('Y-m-d H:i:s', strtotime('+3 days 14:00')),
        'end_event' => date('Y-m-d H:i:s', strtotime('+3 days 22:00')),
        'description' => 'Sarah\'s 25th Birthday Party',
        'status' => 'Contacted',
        'customer_name' => 'Sarah Wilson',
        'place' => 'Bulwagan Kanlahi'
    ],
    [
        'title' => 'Training Workshop',
        'start_event' => date('Y-m-d H:i:s', strtotime('+4 days 8:00')),
        'end_event' => date('Y-m-d H:i:s', strtotime('+4 days 16:00')),
        'description' => 'Employee training workshop',
        'status' => 'Confirmed',
        'customer_name' => 'Tech Solutions Inc.',
        'place' => 'Conference Room'
    ],
    [
        'title' => 'Unavailable',
        'start_event' => date('Y-m-d H:i:s', strtotime('+5 days 10:00')),
        'end_event' => date('Y-m-d H:i:s', strtotime('+5 days 16:00')),
        'description' => 'Venue maintenance',
        'status' => 'Unavailable',
        'customer_name' => null,
        'place' => 'Bulwagan Kanlahi'
    ]
];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Calendar Test Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 600px;
        }
        .success-icon {
            font-size: 3rem;
            color: #28a745;
        }
        .error-icon {
            font-size: 3rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card'>
                    <div class='card-body text-center p-5'>
                        <h1 class='mb-4'>
                            <i class='fas fa-calendar-check me-2'></i>Calendar System Test
                        </h1>";

try {
    $count = 0;
    
    foreach ($sampleBookings as $booking) {
        // Check if booking already exists (same date and venue)
        $checkStmt = $pdo->prepare("
            SELECT id FROM bookings 
            WHERE DATE(start_event) = DATE(?) AND place = ?
        ");
        $checkStmt->execute([$booking['start_event'], $booking['place']]);
        
        if ($checkStmt->rowCount() == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO bookings (title, start_event, end_event, description, status, customer_name, place)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $booking['title'],
                $booking['start_event'],
                $booking['end_event'],
                $booking['description'],
                $booking['status'],
                $booking['customer_name'],
                $booking['place']
            ]);
            
            if ($result) {
                $count++;
            }
        }
    }
    
    echo "<div class='success-icon mb-3'>
            <i class='fas fa-check-circle'></i>
          </div>
          <h2 class='text-success mb-3'>Success!</h2>
          <p class='lead'>Added {$count} sample bookings to the database.</p>
          <div class='row mt-4'>
              <div class='col-md-6'>
                  <a href='admin/calendar.php' class='btn btn-primary btn-lg w-100'>
                      <i class='fas fa-calendar me-2'></i>View Admin Calendar
                  </a>
              </div>
              <div class='col-md-6'>
                  <a href='client-calendar.php' class='btn btn-success btn-lg w-100'>
                      <i class='fas fa-eye me-2'></i>View Client Calendar
                  </a>
              </div>
          </div>
          <div class='mt-4'>
              <p class='text-muted'>Sample bookings include:</p>
              <ul class='list-unstyled text-start'>
                  <li><i class='fas fa-circle text-primary me-2'></i> Wedding Reception (Confirmed)</li>
                  <li><i class='fas fa-circle text-warning me-2'></i> Business Meeting (New)</li>
                  <li><i class='fas fa-circle text-info me-2'></i> Birthday Party (Contacted)</li>
                  <li><i class='fas fa-circle text-success me-2'></i> Training Workshop (Confirmed)</li>
                  <li><i class='fas fa-circle text-danger me-2'></i> Unavailable (Maintenance)</li>
              </ul>
          </div>";

} catch (Exception $e) {
    echo "<div class='error-icon mb-3'>
            <i class='fas fa-exclamation-triangle'></i>
          </div>
          <h2 class='text-danger mb-3'>Error!</h2>
          <p class='lead'>Failed to add sample bookings: " . $e->getMessage() . "</p>
          <a href='admin/calendar.php' class='btn btn-primary btn-lg mt-3'>
              <i class='fas fa-calendar me-2'></i>View Admin Calendar (No Sample Data)
          </a>";
}

echo "      </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>