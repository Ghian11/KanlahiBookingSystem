<?php
/**
 * Bookings Management Page
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

// Require authentication
requireAuth();

// Handle filters
$filters = [
    'venue_id' => $_GET['venue_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get all bookings with filters
$bookings = getBookings($filters);

// Get all venues for filter dropdown
$venues = getVenues();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Kanlahi Tarlac Admin</title>
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
        
        .status-badge {
            font-size: 0.85rem;
            padding: 4px 8px;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .action-btns .btn {
            margin-right: 5px;
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
                        <a class="nav-link active" href="bookings.php">
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
                        <i class="fas fa-calendar-alt me-2"></i>Bookings Management
                    </h1>
                    <div>
                        <a href="../index.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Booking
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="venue_filter" class="form-label">Filter by Venue</label>
                                <select class="form-select" id="venue_filter" name="venue_id">
                                    <option value="">All Venues</option>
                                    <?php foreach ($venues as $venue): ?>
                                        <option value="<?php echo $venue['id']; ?>" 
                                            <?php echo ($filters['venue_id'] == $venue['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($venue['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select class="form-select" id="status_filter" name="status">
                                    <option value="">All Status</option>
                                    <option value="New" <?php echo ($filters['status'] == 'New') ? 'selected' : ''; ?>>New</option>
                                    <option value="Contacted" <?php echo ($filters['status'] == 'Contacted') ? 'selected' : ''; ?>>Contacted</option>
                                    <option value="Confirmed" <?php echo ($filters['status'] == 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="Completed" <?php echo ($filters['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by name, email, or reference..." 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>All Bookings
                        </h5>
                        <span class="text-muted">
                            Total: <?php echo count($bookings); ?> booking<?php echo count($bookings) != 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-calendar fa-3x mb-3"></i>
                                <p class="mb-0">No bookings found. Start accepting bookings from the customer form!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
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
                                        <?php foreach ($bookings as $booking): ?>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>