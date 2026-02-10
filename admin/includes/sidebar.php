<?php
/**
 * Admin Sidebar Component
 * Extracted from admin/bookings.php for reuse across admin pages
 */
?>
<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 sidebar px-3 py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="fas fa-user-shield fa-2x text-white me-3"></i>
        <div>
            <h5 class="text-white mb-0">Admin Panel</h5>
            <small class="text-light">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?></small>
        </div>
    </div>
    
    <hr class="text-white-50">
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'bookings.php') ? 'active' : ''; ?>" href="bookings.php">
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