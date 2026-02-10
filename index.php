<?php
/**
 * Customer Booking Form
 * Main page for customers to book venues
 */

require_once 'config/database.php';
require_once 'config/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $email = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $address = trim($_POST['address']);
    $schedule = $_POST['schedule'];
    $place = trim($_POST['place']);
    $purpose_of_event = trim($_POST['purpose_of_event']);
    $rent_venue = floatval($_POST['rent_venue']);
    
    // Validation
    $errors = [];
    
    if (empty($customer_name)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    
    if (empty($contact_no)) {
        $errors[] = 'Contact number is required.';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
    
    if (empty($schedule)) {
        $errors[] = 'Schedule is required.';
    } elseif (strtotime($schedule) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Schedule must be today or in the future.';
    }
    
    if (empty($place)) {
        $errors[] = 'Please select a place.';
    }
    
    if (empty($purpose_of_event)) {
        $errors[] = 'Purpose of event is required.';
    }
    
    if (empty($rent_venue) || $rent_venue <= 0) {
        $errors[] = 'Rent venue amount is required and must be greater than 0.';
    }
    
    // Check venue availability
    if (empty($errors)) {
        if (!canCreateBooking($place, $schedule)) {
            $errors[] = 'Sorry, this venue is not available on the selected date. Please choose a different date.';
        }
    }
    
    // If no errors, insert booking
    if (empty($errors)) {
        try {
            // Generate unique reference number
            $ref_number = generateReferenceNumber();
            
            // Convert schedule to start_event and end_event format
            $start_event = $schedule;
            $end_event = date('Y-m-d H:i:s', strtotime($schedule . ' +2 hours')); // Default 2-hour duration
            
            // Debug: Log the query and parameters
            error_log("Booking query: INSERT INTO bookings (ref_number, customer_name, address, contact_no, email, start_event, end_event, title, description, place) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            error_log("Parameters: " . print_r([$ref_number, $customer_name, $address, $contact_no, $email, $start_event, $end_event, $purpose_of_event, $purpose_of_event, $place], true));
            
            $stmt = $pdo->prepare("INSERT INTO bookings (ref_number, customer_name, address, contact_no, email, start_event, end_event, title, description, place) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$ref_number, $customer_name, $address, $contact_no, $email, $start_event, $end_event, $purpose_of_event, $purpose_of_event, $place]);
            
            if ($result) {
                $success = "Booking successful! Your reference number is: <strong>$ref_number</strong>";
                // Clear form data
                $customer_name = $email = $contact_no = $address = $schedule = $purpose_of_event = $rent_venue = '';
            } else {
                $errors[] = 'Failed to create booking. Please try again.';
            }
            
        } catch (Exception $e) {
            // Log the actual error for debugging
            error_log("Booking error: " . $e->getMessage());
            error_log("Booking error details: " . print_r($e, true));
            $errors[] = 'An error occurred while processing your booking. Please try again.';
        }
    }
}

// Get venues for dropdown
$venues = getVenues();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanlahi Tarlac Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Admin Panel Button -->
    <div class="admin-panel-button">
        <a href="admin/login.php" class="btn btn-ghost">
            <i class="fas fa-user-shield me-2"></i>Admin Panel
        </a>
    </div>

    <!-- Header Section -->
    <section class="header-section">
        <div class="header-bg"></div>
        <div class="container">
            <div class="header-content">
                <h1 class="header-title">Kanlahi Tarlac Booking System</h1>
                <p class="header-subtitle">Book your venue for events and conferences with ease</p>
            </div>
        </div>
    </section>

    <!-- Booking Form -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="booking-card p-4">
                    <h2 class="mb-4 text-center">
                        <i class="fas fa-plus-circle me-2"></i>Book a Venue
                    </h2>
                    
                    <!-- Success Message -->
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Progress Indicator -->
                    <div class="progress-container mb-4">
                        <div class="progress-step active">
                            <div class="progress-circle">1</div>
                            <span>Personal Info</span>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step">
                            <div class="progress-circle">2</div>
                            <span>Venue Details</span>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step">
                            <div class="progress-circle">3</div>
                            <span>Event Info</span>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step">
                            <div class="progress-circle">4</div>
                            <span>Confirmation</span>
                        </div>
                    </div>

                    <form method="POST" novalidate>
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <div class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Personal Information</h5>
                                    <small class="text-muted">Your contact details</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customer_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                           value="<?php echo htmlspecialchars($customer_name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_no" class="form-label">Contact Number</label>
                                    <input type="tel" class="form-control" id="contact_no" name="contact_no" 
                                           value="<?php echo htmlspecialchars($contact_no ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?php echo htmlspecialchars($address ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Venue Details Section -->
                        <div class="form-section">
                            <div class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Venue Details</h5>
                                    <small class="text-muted">Select your venue and time</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="schedule" class="form-label">Schedule (Date and Time)</label>
                                    <input type="datetime-local" class="form-control" id="schedule" name="schedule" 
                                           value="<?php echo htmlspecialchars($schedule ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="place" class="form-label">Place</label>
                                    <select class="form-select" id="place" name="place" required>
                                        <option value="">Select a place...</option>
                                        <option value="BULWAGAN">BULWAGAN (Capacity: 200)</option>
                                        <option value="CONFERENCE HALL">CONFERENCE HALL (Capacity: 50)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Availability Status -->
                            <div id="availability-status" class="availability-status" style="display: none;">
                                <div class="availability-dot"></div>
                                <span class="status-text">Checking availability...</span>
                            </div>
                        </div>

                        <!-- Event Information Section -->
                        <div class="form-section">
                            <div class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Event Information</h5>
                                    <small class="text-muted">Event details and pricing</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="purpose_of_event" class="form-label">Purpose of Event</label>
                                    <textarea class="form-control" id="purpose_of_event" name="purpose_of_event" rows="3" 
                                              placeholder="Describe the purpose of your event..." required><?php echo htmlspecialchars($purpose_of_event ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="rent_venue" class="form-label">Rent Venue (PHP)</label>
                                    <input type="number" class="form-control" id="rent_venue" name="rent_venue" 
                                           value="<?php echo htmlspecialchars($rent_venue ?? ''); ?>" step="0.01" min="0" required>
                                    <div class="form-text">Enter the rental cost for the venue</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-calendar-check me-2"></i>Book Venue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-transparent py-4">
        <div class="container text-center">
            <p class="mb-0 text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);">© 2024 Kanlahi Tarlac. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Inline JavaScript for this specific page -->
    <script>
        // Initialize enhanced booking form functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Setup real-time availability checking
            const scheduleInput = document.getElementById('schedule');
            const placeInput = document.getElementById('place');
            const availabilityStatus = document.getElementById('availability-status');
            
            if (scheduleInput && placeInput && availabilityStatus) {
                // Show availability status when both fields have values
                const checkAvailability = () => {
                    const schedule = scheduleInput.value;
                    const place = placeInput.value;
                    
                    if (schedule && place) {
                        availabilityStatus.style.display = 'flex';
                        availabilityStatus.classList.remove('available', 'unavailable', 'error');
                        availabilityStatus.querySelector('.availability-dot').classList.remove('available', 'unavailable', 'error');
                        availabilityStatus.querySelector('.status-text').textContent = 'Checking availability...';
                        
                        // Simulate API call for demo purposes
                        setTimeout(() => {
                            // For demo, assume available if date is in the future
                            const selectedDate = new Date(schedule);
                            const today = new Date();
                            const isAvailable = selectedDate > today;
                            
                            if (isAvailable) {
                                availabilityStatus.classList.add('available');
                                availabilityStatus.querySelector('.availability-dot').classList.add('available');
                                availabilityStatus.querySelector('.status-text').textContent = '✅ Venue is available!';
                            } else {
                                availabilityStatus.classList.add('unavailable');
                                availabilityStatus.querySelector('.availability-dot').classList.add('unavailable');
                                availabilityStatus.querySelector('.status-text').textContent = '❌ This date is not available. Please choose another date.';
                            }
                        }, 500);
                    } else {
                        availabilityStatus.style.display = 'none';
                    }
                };
                
                scheduleInput.addEventListener('change', checkAvailability);
                placeInput.addEventListener('change', checkAvailability);
            }
            
            // Enhanced form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = e.target.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    // Show loading state
                    submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
                    submitBtn.disabled = true;
                    
                    // Check availability before submission
                    if (availabilityStatus && availabilityStatus.classList.contains('unavailable')) {
                        e.preventDefault();
                        showToast('Please select an available date and time', 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        return;
                    }
                    
                    // Simulate processing delay
                    setTimeout(() => {
                        // Form will submit normally
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 1000);
                });
            }
            
            // Progress indicator updates
            const updateProgress = () => {
                const sections = document.querySelectorAll('.form-section');
                const progressCircles = document.querySelectorAll('.progress-circle');
                
                sections.forEach((section, index) => {
                    const inputs = section.querySelectorAll('input, select, textarea');
                    let filledInputs = 0;
                    
                    inputs.forEach(input => {
                        if (input.value.trim() !== '') {
                            filledInputs++;
                        }
                    });
                    
                    const completionRatio = filledInputs / inputs.length;
                    
                    if (completionRatio === 1) {
                        progressCircles[index].classList.add('completed');
                        progressCircles[index].textContent = '✓';
                    } else if (completionRatio > 0) {
                        progressCircles[index].classList.add('active');
                        progressCircles[index].textContent = index + 1;
                    } else {
                        progressCircles[index].classList.remove('completed', 'active');
                        progressCircles[index].textContent = index + 1;
                    }
                });
            };
            
            form?.addEventListener('input', updateProgress);
            updateProgress();
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <span>${message}</span>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
            return container;
        }
    </script>
</body>
</html>
