<?php
/**
 * Client Calendar Interface
 * Read-only calendar for clients to view availability
 */

require_once 'config/database.php';

// Get current date for default view
$currentDate = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Calendar - Check Availability</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.11/index.global.min.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --unavailable-color: #dc3545;
            --available-color: #28a745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
        }

        /* Header Styles */
        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            margin: 30px auto;
            max-width: 1000px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 30px;
        }

        /* Calendar Container */
        .calendar-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .calendar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .legend-container {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-dot.unavailable {
            background-color: var(--unavailable-color);
        }

        .legend-dot.available {
            background-color: var(--available-color);
        }

        /* FullCalendar Custom Styles */
        .fc {
            height: 600px;
        }

        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .fc-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .fc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Event Styles for Client View */
        .fc-event {
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: default !important; /* Prevent interaction */
            pointer-events: none !important; /* Disable all interactions */
        }

        /* Unavailable events - red background, hide details */
        .fc-event.unavailable {
            background-color: var(--unavailable-color) !important;
            border-color: var(--unavailable-color) !important;
            color: white !important;
        }

        /* Available slots - green background */
        .fc-event.available {
            background-color: var(--available-color) !important;
            border-color: var(--available-color) !important;
            color: white !important;
        }

        /* Hide event titles for privacy */
        .fc-event-title {
            display: none !important;
        }

        /* Custom event content for client view */
        .fc-event-main {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
        }

        .fc-event-main::after {
            content: 'UNAVAILABLE' !important;
            font-size: 0.7rem !important;
            font-weight: bold !important;
            text-align: center !important;
            color: white !important;
        }

        /* Available slot content */
        .fc-event.available .fc-event-main::after {
            content: 'AVAILABLE' !important;
            color: white !important;
        }

        /* Date cell styles */
        .fc-daygrid-day {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fc-daygrid-day:hover {
            background-color: #f8f9fa;
        }

        /* Booking Form Modal */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 2px solid #e9ecef;
            padding: 10px 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Call to Action */
        .cta-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin: 30px auto;
            max-width: 1000px;
            text-align: center;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-secondary-custom {
            background: #6c757d;
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-left: 15px;
        }

        .btn-secondary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 20px;
                margin: 20px;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .calendar-container {
                padding: 20px;
            }
            
            .fc {
                height: 500px;
            }
            
            .legend-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="hero-title mb-3">
                    <i class="fas fa-calendar-check me-3"></i>Book Your Venue
                </h1>
                <p class="hero-subtitle">
                    Check venue availability and book your event space. Simply click on an available date to get started.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php" class="btn btn-primary-custom">
                    <i class="fas fa-plus me-2"></i>Make a Booking
                </a>
                <a href="admin/login.php" class="btn btn-secondary-custom">
                    <i class="fas fa-user-shield me-2"></i>Admin Login
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="container">
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-title">
                    <i class="fas fa-calendar-alt me-2"></i>Venue Availability Calendar
                </div>
                <div class="legend-container">
                    <div class="legend-item">
                        <div class="legend-dot unavailable"></div>
                        <span>Unavailable</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot available"></div>
                        <span>Available</span>
                    </div>
                </div>
            </div>
            
            <!-- Calendar -->
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Call to Action Section -->
    <div class="cta-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-3">Ready to Book?</h3>
                <p class="text-muted mb-0">
                    Choose your preferred date from the calendar above, then fill out our booking form to reserve your venue.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php" class="btn btn-primary-custom">
                    <i class="fas fa-calendar-plus me-2"></i>Start Booking Process
                </a>
            </div>
        </div>
    </div>

    <!-- Booking Form Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i>Book This Date
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <input type="hidden" id="selectedDate">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customerName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="customerName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customerEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="customerEmail" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customerPhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="customerPhone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customerAddress" class="form-label">Address</label>
                                <input type="text" class="form-control" id="customerAddress" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eventDate" class="form-label">Event Date & Time</label>
                                <input type="datetime-local" class="form-control" id="eventDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="venueSelect" class="form-label">Venue</label>
                                <select class="form-select" id="venueSelect" required>
                                    <option value="">Select Venue...</option>
                                    <option value="Bulwagan Kanlahi">Bulwagan Kanlahi (Capacity: 200)</option>
                                    <option value="Conference Room">Conference Room (Capacity: 50)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="eventPurpose" class="form-label">Purpose of Event</label>
                            <textarea class="form-control" id="eventPurpose" rows="3" placeholder="Describe your event..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rentAmount" class="form-label">Rental Amount (PHP)</label>
                            <input type="number" class="form-control" id="rentAmount" step="0.01" min="0" required>
                            <div class="form-text">Enter the agreed rental amount for the venue</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitBookingBtn">
                        <i class="fas fa-paper-plane me-2"></i>Submit Booking Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Toasts -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="successToastBody"></div>
        </div>
        
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="errorToastBody"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Initialize calendar
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                editable: false, // Client view is read-only
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                nowIndicator: true,
                
                // Event handlers
                dateClick: function(info) {
                    handleDateClick(info.date);
                },
                
                select: function(info) {
                    handleDateClick(info.start);
                },
                
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetchEvents(successCallback, failureCallback);
                },
                
                // Customize event rendering for client view
                eventContent: function(arg) {
                    // For client view, we want to hide specific booking details
                    // and show generic "Unavailable" or "Available" status
                    return {
                        html: '<div class="event-content">' + 
                              '<div class="event-status">' + 
                              (arg.event.extendedProps.status === 'Unavailable' ? 'UNAVAILABLE' : 'AVAILABLE') +
                              '</div>' +
                              '</div>'
                    };
                }
            });
            
            calendar.render();
            
            // Event Modal Functions
            const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            const bookingForm = document.getElementById('bookingForm');
            const submitBookingBtn = document.getElementById('submitBookingBtn');
            
            function handleDateClick(date) {
                // Check if date is in the past
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(date);
                selectedDate.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    showError('Cannot book past dates. Please select a future date.');
                    return;
                }
                
                // Set the selected date
                document.getElementById('selectedDate').value = date.toISOString();
                document.getElementById('eventDate').value = formatDateForInput(date);
                
                // Open booking modal
                bookingModal.show();
            }
            
            function formatDateForInput(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }
            
            // Submit Booking
            submitBookingBtn.addEventListener('click', function() {
                const formData = {
                    title: 'New Booking Request',
                    start: document.getElementById('eventDate').value,
                    end: null, // Will be calculated on server
                    description: document.getElementById('eventPurpose').value,
                    status: 'New',
                    customer_name: document.getElementById('customerName').value,
                    place: document.getElementById('venueSelect').value,
                    rent_venue: document.getElementById('rentAmount').value
                };
                
                // Basic validation
                if (!formData.customer_name || !formData.place || !formData.description || !formData.rent_venue) {
                    showError('Please fill in all required fields.');
                    return;
                }
                
                submitBookingRequest(formData);
            });
            
            // API Functions
            function fetchEvents(successCallback, failureCallback) {
                showLoading(true);
                fetch('api/fetch_events.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            showError(data.error);
                            failureCallback(new Error(data.error));
                        } else {
                            // Transform events for client view - hide details
                            const clientEvents = data.map(event => ({
                                ...event,
                                title: event.status === 'Unavailable' ? 'Unavailable' : 'Available',
                                extendedProps: {
                                    ...event.extendedProps,
                                    status: event.status
                                },
                                backgroundColor: event.status === 'Unavailable' ? '#dc3545' : '#28a745',
                                borderColor: event.status === 'Unavailable' ? '#dc3545' : '#28a745',
                                textColor: '#ffffff'
                            }));
                            successCallback(clientEvents);
                        }
                    })
                    .catch(error => {
                        showError('Failed to fetch events: ' + error.message);
                        failureCallback(error);
                    })
                    .finally(() => {
                        showLoading(false);
                    });
            }
            
            function submitBookingRequest(bookingData) {
                showLoading(true);
                fetch('api/add_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(bookingData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Booking request submitted successfully! We will contact you shortly.');
                        bookingModal.hide();
                        bookingForm.reset();
                        calendar.refetchEvents();
                    } else {
                        showError(data.error || 'Failed to submit booking request');
                    }
                })
                .catch(error => {
                    showError('Network error: ' + error.message);
                })
                .finally(() => {
                    showLoading(false);
                });
            }
            
            // UI Helper Functions
            function showLoading(show) {
                if (show) {
                    loadingOverlay.style.display = 'flex';
                } else {
                    loadingOverlay.style.display = 'none';
                }
            }
            
            function showSuccess(message) {
                const toast = document.getElementById('successToast');
                const toastBody = document.getElementById('successToastBody');
                toastBody.textContent = message;
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            }
            
            function showError(message) {
                const toast = document.getElementById('errorToast');
                const toastBody = document.getElementById('errorToastBody');
                toastBody.textContent = message;
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            }
        });
    </script>
</body>
</html>