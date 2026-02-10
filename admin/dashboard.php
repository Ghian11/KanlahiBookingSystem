<?php
/**
 * Admin Dashboard
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

// Require authentication
requireAuth();

// Get real-time booking counts
try {
    // Total bookings count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $total_result = $stmt->fetch();
    $total_bookings = $total_result['total'];

    // New requests count (status = 'New')
    $stmt = $pdo->query("SELECT COUNT(*) as new_count FROM bookings WHERE status = 'New'");
    $new_result = $stmt->fetch();
    $new_requests = $new_result['new_count'];

    // Confirmed bookings count (status = 'Confirmed')
    $stmt = $pdo->query("SELECT COUNT(*) as confirmed_count FROM bookings WHERE status = 'Confirmed'");
    $confirmed_result = $stmt->fetch();
    $confirmed = $confirmed_result['confirmed_count'];

    // Today's events count
    $today = date('Y-m-d');
    $stmt = $pdo->query("SELECT COUNT(*) as today_count FROM bookings WHERE DATE(start_event) = '$today'");
    $today_result = $stmt->fetch();
    $todays_events = $today_result['today_count'];

} catch (Exception $e) {
    // Fallback values if database query fails
    $total_bookings = 0;
    $new_requests = 0;
    $confirmed = 0;
    $todays_events = 0;
}

// Get recent bookings
$recent_bookings = getBookings([], 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kanlahi Tarlac</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --gold-color: #F4B400;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        /* Gold buttons for primary actions */
        .btn-gold {
            background: linear-gradient(135deg, #F4B400 0%, #FFA000 100%);
            border: none;
            color: #333;
            font-weight: 700;
        }
        
        .btn-gold:hover {
            background: linear-gradient(135deg, #FFA000 0%, #F4B400 100%);
            color: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 180, 0, 0.4);
        }
        
        /* Purple buttons for secondary actions */
        .btn-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-purple:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-4 py-4">
                <!-- Include Topbar -->
                <?php include 'includes/topbar.php'; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary text-white me-3">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Total Bookings</h6>
                                    <h4 class="mb-0"><?php echo $total_bookings; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning text-dark me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">New Requests</h6>
                                    <h4 class="mb-0"><?php echo $new_requests; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success text-white me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Confirmed</h6>
                                    <h4 class="mb-0"><?php echo $confirmed; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info text-white me-3">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Today's Events</h6>
                                    <h4 class="mb-0"><?php echo $todays_events; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-2">
                                            <i class="fas fa-calendar-alt me-2"></i>Quick Access
                                        </h6>
                                        <p class="card-text text-muted">Manage your bookings with our interactive calendar</p>
                                    </div>
                                    <a href="calendar.php" class="btn btn-gold btn-lg">
                                        <i class="fas fa-calendar me-2"></i>Open Calendar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Bookings
                                </h5>
                                <a href="bookings.php" class="btn btn-purple">
                                    View All <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_bookings)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-calendar fa-3x mb-3"></i>
                                        <p class="mb-0">No bookings found. Start accepting bookings from the customer form!</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Reference</th>
                                                    <th>Customer</th>
                                                    <th>Venue</th>
                                                    <th>Event Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_bookings as $booking): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark fw-normal">
                                                                <?php echo htmlspecialchars($booking['ref_number']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($booking['contact_no']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo htmlspecialchars($booking['venue_name']); ?>
                                                            </span>
                                                            <br><small class="text-muted">(Capacity: <?php echo $booking['capacity']; ?>)</small>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo formatDate($booking['start_event']); ?></strong><br>
                                                            <small class="text-muted"><?php echo date('l', strtotime($booking['start_event'])); ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge status-badge <?php echo getStatusBadgeClass($booking['status']); ?>">
                                                                <?php echo htmlspecialchars($booking['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="action-btns">
                                                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="tel:<?php echo htmlspecialchars($booking['contact_no']); ?>" 
                                                               class="btn btn-sm btn-outline-success" title="Call Customer">
                                                                <i class="fas fa-phone"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>