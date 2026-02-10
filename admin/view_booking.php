<?php
/**
 * View Booking Details Page
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

// Require authentication
requireAuth();

// Get booking ID from URL
$booking_id = $_GET['id'] ?? null;

if (!$booking_id) {
    header('Location: bookings.php');
    exit();
}

// Get booking details
$booking = getBookingById($booking_id);

if (!$booking) {
    header('Location: bookings.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $internal_notes = trim($_POST['internal_notes'] ?? '');
    
    if (updateBooking($booking_id, $status, $internal_notes)) {
        $success = "Booking status updated successfully!";
        // Refresh booking data
        $booking = getBookingById($booking_id);
    } else {
        $error = "Failed to update booking status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Kanlahi Tarlac Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
        
        .booking-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        }
        
        .status-badge {
            font-size: 1.1rem;
            padding: 8px 16px;
        }
        
        .detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        }
        
        .action-bar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar px-3 py-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-user-shield fa-2x text-white me-3"></i>
                    <div>
                        <h5 class="text-white mb-0">Admin Panel</h5>
                        <small class="text-light">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></small>
                    </div>
                </div>
                
                <hr class="text-white-50">
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-calendar-alt me-2"></i>Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-plus-circle me-2"></i>Add Booking
                        </a>
                    </li>
                    <li class="nav-item mt-auto">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 mb-0">
                        <i class="fas fa-eye me-2"></i>Booking Details
                    </h1>
                    <div>
                        <a href="bookings.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Booking Header -->
                <div class="booking-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">
                                Booking Reference: <span class="badge bg-primary"><?php echo htmlspecialchars($booking['ref_number'] ?? 'N/A'); ?></span>
                            </h3>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge status-badge <?php echo getStatusBadgeClass($booking['status']); ?>">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($booking['venue_name']); ?>
                                </span>
                                <span class="badge bg-info">
                                    <i class="fas fa-calendar me-1"></i><?php echo formatDate($booking['start_event']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="tel:<?php echo htmlspecialchars($booking['contact_no'] ?? ''); ?>" 
                                   class="btn btn-success btn-lg">
                                    <i class="fas fa-phone me-2"></i>Call Customer
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($booking['email'] ?? ''); ?>" 
                                   class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Customer Information -->
                    <div class="col-lg-8">
                        <div class="detail-card p-4 mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-user me-2"></i>Customer Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Full Name</label>
                                    <div class="fw-bold"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Email Address</label>
                                    <div class="fw-bold"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Contact Number</label>
                                    <div class="fw-bold"><?php echo htmlspecialchars($booking['contact_no']); ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Booking Date</label>
                                    <div class="fw-bold"><?php echo formatDate($booking['created_at']); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Event Details -->
                        <div class="detail-card p-4 mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-calendar-alt me-2"></i>Event Details
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Venue</label>
                                    <div class="fw-bold"><?php echo htmlspecialchars($booking['venue_name']); ?></div>
                                    <small class="text-muted">Capacity: <?php echo $booking['capacity']; ?> people</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Event Date</label>
                                    <div class="fw-bold"><?php echo formatDate($booking['start_event']); ?></div>
                                    <small class="text-muted"><?php echo date('l', strtotime($booking['start_event'])); ?></small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Purpose of Event</label>
                                <div class="border p-3 rounded bg-light">
                                    <?php echo nl2br(htmlspecialchars($booking['description'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Panel -->
                    <div class="col-lg-4">
                        <div class="action-bar p-4">
                            <h5 class="mb-3">
                                <i class="fas fa-cog me-2"></i>Manage Booking
                            </h5>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="New" <?php echo ($booking['status'] == 'New') ? 'selected' : ''; ?>>New</option>
                                        <option value="Contacted" <?php echo ($booking['status'] == 'Contacted') ? 'selected' : ''; ?>>Contacted</option>
                                        <option value="Confirmed" <?php echo ($booking['status'] == 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Completed" <?php echo ($booking['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="internal_notes" class="form-label">Internal Notes</label>
                                    <textarea class="form-control" id="internal_notes" name="internal_notes" rows="4" 
                                              placeholder="Add internal notes here..."><?php echo htmlspecialchars($booking['internal_notes'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_status" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Booking
                                    </button>
                                </div>
                            </form>

                            <hr class="my-4">
                            
                            <div class="text-center">
                                <h6 class="text-muted mb-3">Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    <a href="tel:<?php echo htmlspecialchars($booking['contact_no']); ?>" 
                                       class="btn btn-success">
                                        <i class="fas fa-phone me-2"></i>Call Customer
                                    </a>
                                    <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-envelope me-2"></i>Send Email
                                    </a>
                                </div>
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