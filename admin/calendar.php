<?php
/**
 * Admin Calendar Interface
 * Full-screen interactive calendar for managing bookings
 */

require_once '../config/auth.php';
require_once '../config/functions.php';

// Check if user is authenticated
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user info
$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Calendar - Booking Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.11/index.global.min.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border-left: 4px solid white;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* Header */
        .top-header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #333;
        }

        .user-role {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        /* Calendar Container */
        .calendar-container {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .calendar-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: calc(100vh - 120px);
            position: relative;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .calendar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .calendar-actions {
            display: flex;
            gap: 10px;
        }

        .btn-custom {
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary-custom {
            background: #6c757d;
            color: white;
        }

        .btn-danger-custom {
            background: #dc3545;
            color: white;
        }

        /* FullCalendar Custom Styles */
        .fc {
            height: 100%;
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
            padding: 6px 16px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .fc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .fc-button-active {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        /* Event Styles */
        .fc-event {
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Status Colors */
        .status-new { background-color: #007bff !important; }
        .status-contacted { background-color: #ffc107 !important; }
        .status-confirmed { background-color: #28a745 !important; }
        .status-completed { background-color: #6c757d !important; }
        .status-unavailable { background-color: #dc3545 !important; }

        /* Modal Styles */
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

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .calendar-container {
                padding: 15px;
            }
            
            .calendar-card {
                height: calc(100vh - 80px);
            }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
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

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-calendar-alt me-2"></i>Booking System</h4>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="bookings.php" class="nav-link">
                <i class="fas fa-list"></i> Bookings List
            </a>
            <a href="calendar.php" class="nav-link active">
                <i class="fas fa-calendar"></i> Calendar View
            </a>
            <a href="view_booking.php" class="nav-link">
                <i class="fas fa-eye"></i> View Bookings
            </a>
            <hr style="border-color: rgba(255,255,255,0.2); margin: 10px 20px;">
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <h1 class="page-title">Calendar Management</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($user['username'], 0, 2); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Calendar Container -->
        <div class="calendar-container">
            <div class="calendar-card">
                <div class="calendar-header">
                    <div class="calendar-title">
                        <i class="fas fa-calendar-alt me-2"></i>Interactive Booking Calendar
                    </div>
                    <div class="calendar-actions">
                        <button class="btn btn-custom btn-primary-custom" id="addEventBtn">
                            <i class="fas fa-plus me-2"></i>Add Booking
                        </button>
                        <button class="btn btn-custom btn-secondary-custom" id="refreshBtn">
                            <i class="fas fa-refresh me-2"></i>Refresh
                        </button>
                    </div>
                </div>
                
                <!-- Loading Overlay -->
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="spinner"></div>
                </div>

                <!-- Calendar -->
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Manage Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <input type="hidden" id="eventId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eventTitle" class="form-label">Booking Title</label>
                                <input type="text" class="form-control" id="eventTitle" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eventCustomer" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="eventCustomer">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eventStart" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control" id="eventStart" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eventEnd" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control" id="eventEnd" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eventPlace" class="form-label">Venue</label>
                                <select class="form-select" id="eventPlace">
                                    <option value="">Select Venue...</option>
                                    <option value="Bulwagan Kanlahi">Bulwagan Kanlahi</option>
                                    <option value="Conference Room">Conference Room</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eventStatus" class="form-label">Status</label>
                                <select class="form-select" id="eventStatus">
                                    <option value="New">New</option>
                                    <option value="Contacted">Contacted</option>
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" rows="3" placeholder="Event details..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEventBtn">
                        <i class="fas fa-save me-2"></i>Save Changes
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
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.11/index.global.min.js"></script>

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
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                nowIndicator: true,
                
                // Event handlers
                dateClick: function(info) {
                    openEventModal(null, info.date);
                },
                
                select: function(info) {
                    openEventModal(null, info.start, info.end);
                },
                
                eventClick: function(info) {
                    openEventModal(info.event);
                },
                
                eventDrop: function(info) {
                    updateEvent(info.event);
                },
                
                eventResize: function(info) {
                    updateEvent(info.event);
                },
                
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetchEvents(successCallback, failureCallback);
                }
            });
            
            calendar.render();
            
            // Event Modal Functions
            const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            const eventForm = document.getElementById('eventForm');
            const deleteEventBtn = document.getElementById('deleteEventBtn');
            const saveEventBtn = document.getElementById('saveEventBtn');
            
            function openEventModal(event, startDate = null, endDate = null) {
                const modal = document.getElementById('eventModal');
                const modalLabel = document.getElementById('eventModalLabel');
                const eventId = document.getElementById('eventId');
                const eventTitle = document.getElementById('eventTitle');
                const eventCustomer = document.getElementById('eventCustomer');
                const eventStart = document.getElementById('eventStart');
                const eventEnd = document.getElementById('eventEnd');
                const eventPlace = document.getElementById('eventPlace');
                const eventStatus = document.getElementById('eventStatus');
                const eventDescription = document.getElementById('eventDescription');
                
                if (event) {
                    // Edit existing event
                    modalLabel.textContent = 'Edit Booking';
                    eventId.value = event.id;
                    eventTitle.value = event.title;
                    eventCustomer.value = event.extendedProps.customer_name || '';
                    eventPlace.value = event.extendedProps.place || '';
                    eventStatus.value = event.extendedProps.status || 'New';
                    eventDescription.value = event.extendedProps.description || '';
                    
                    // Format dates for datetime-local input
                    const start = new Date(event.start);
                    const end = event.end ? new Date(event.end) : new Date(start.getTime() + 60 * 60 * 1000); // 1 hour default
                    
                    eventStart.value = formatDateForInput(start);
                    eventEnd.value = formatDateForInput(end);
                    
                    deleteEventBtn.style.display = 'inline-block';
                } else {
                    // Add new event
                    modalLabel.textContent = 'Add New Booking';
                    eventForm.reset();
                    eventId.value = '';
                    
                    if (startDate) {
                        const start = new Date(startDate);
                        const end = endDate ? new Date(endDate) : new Date(start.getTime() + 60 * 60 * 1000);
                        
                        eventStart.value = formatDateForInput(start);
                        eventEnd.value = formatDateForInput(end);
                    }
                    
                    deleteEventBtn.style.display = 'none';
                }
                
                eventModal.show();
            }
            
            function formatDateForInput(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }
            
            // Save Event
            saveEventBtn.addEventListener('click', function() {
                const eventId = document.getElementById('eventId').value;
                const eventData = {
                    title: document.getElementById('eventTitle').value,
                    start: document.getElementById('eventStart').value,
                    end: document.getElementById('eventEnd').value,
                    description: document.getElementById('eventDescription').value,
                    status: document.getElementById('eventStatus').value,
                    customer_name: document.getElementById('eventCustomer').value,
                    place: document.getElementById('eventPlace').value
                };
                
                if (eventId) {
                    updateExistingEvent(eventId, eventData);
                } else {
                    createNewEvent(eventData);
                }
            });
            
            // Delete Event
            deleteEventBtn.addEventListener('click', function() {
                const eventId = document.getElementById('eventId').value;
                if (eventId) {
                    if (confirm('Are you sure you want to delete this booking?')) {
                        deleteEvent(eventId);
                    }
                }
            });
            
            // Refresh Calendar
            document.getElementById('refreshBtn').addEventListener('click', function() {
                showLoading(true);
                calendar.refetchEvents().finally(() => {
                    showLoading(false);
                });
            });
            
            // Add Event Button
            document.getElementById('addEventBtn').addEventListener('click', function() {
                openEventModal(null, new Date());
            });
            
            // API Functions
            function fetchEvents(successCallback, failureCallback) {
                showLoading(true);
                fetch('../api/fetch_events.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            showError(data.error);
                            failureCallback(new Error(data.error));
                        } else {
                            successCallback(data);
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
            
            function createNewEvent(eventData) {
                showLoading(true);
                fetch('../api/add_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(eventData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Booking created successfully!');
                        eventModal.hide();
                        calendar.refetchEvents();
                    } else {
                        showError(data.error || 'Failed to create booking');
                    }
                })
                .catch(error => {
                    showError('Network error: ' + error.message);
                })
                .finally(() => {
                    showLoading(false);
                });
            }
            
            function updateExistingEvent(eventId, eventData) {
                showLoading(true);
                fetch(`../api/update_event.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: eventId,
                        title: eventData.title,
                        start: eventData.start,
                        end: eventData.end,
                        description: eventData.description,
                        status: eventData.status,
                        customer_name: eventData.customer_name,
                        place: eventData.place
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Booking updated successfully!');
                        eventModal.hide();
                        calendar.refetchEvents();
                    } else {
                        showError(data.error || 'Failed to update booking');
                    }
                })
                .catch(error => {
                    showError('Network error: ' + error.message);
                })
                .finally(() => {
                    showLoading(false);
                });
            }
            
            function updateEvent(event) {
                const eventData = {
                    id: event.id,
                    title: event.title,
                    start: event.start.toISOString(),
                    end: event.end ? event.end.toISOString() : null,
                    description: event.extendedProps.description || '',
                    status: event.extendedProps.status || 'New',
                    customer_name: event.extendedProps.customer_name || '',
                    place: event.extendedProps.place || ''
                };
                
                updateExistingEvent(event.id, eventData);
            }
            
            function deleteEvent(eventId) {
                showLoading(true);
                fetch(`../api/update_event.php`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: eventId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Booking deleted successfully!');
                        eventModal.hide();
                        calendar.refetchEvents();
                    } else {
                        showError(data.error || 'Failed to delete booking');
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