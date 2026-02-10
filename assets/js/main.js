// Modern Booking System JavaScript
// Base URL for API calls - handles both root and admin contexts
const API_BASE_URL = window.location.pathname.startsWith('/admin/') ? '../api/' : 'api/';

class BookingSystem {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupAvailabilityChecker();
        this.setupProgressIndicator();
        this.setupTooltips();
        this.setupFormValidation();
    }

    setupEventListeners() {
        // Real-time availability checking
        const scheduleInput = document.getElementById('schedule');
        const placeInput = document.getElementById('place');
        const availabilityStatus = document.getElementById('availability-status');
        
        if (scheduleInput && placeInput) {
            scheduleInput.addEventListener('change', () => this.checkAvailability());
            placeInput.addEventListener('change', () => this.checkAvailability());
        }

        // Form submission with enhanced UX
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Admin panel enhancements
        this.setupAdminPanel();
    }

    setupAvailabilityChecker() {
        // Debounce function for API calls
        const debounce = (func, wait) => {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };

        this.debouncedCheckAvailability = debounce(() => this.performAvailabilityCheck(), 500);
    }

    async checkAvailability() {
        const scheduleInput = document.getElementById('schedule');
        const placeInput = document.getElementById('place');
        const availabilityStatus = document.getElementById('availability-status');
        
        if (!scheduleInput || !placeInput || !availabilityStatus) return;

        const schedule = scheduleInput.value;
        const place = placeInput.value;

        if (!schedule || !place) {
            availabilityStatus.style.display = 'none';
            return;
        }

        availabilityStatus.style.display = 'flex';
        this.updateAvailabilityStatus('checking', 'Checking availability...');

        try {
            const response = await fetch(`${API_BASE_URL}check-availability.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    schedule: schedule,
                    place: place
                })
            });

            const result = await response.json();
            this.updateAvailabilityStatus(result.available ? 'available' : 'unavailable', 
                result.available ? 'Venue is available!' : 'This date is not available. Please choose another date.');
            
        } catch (error) {
            console.error('Availability check failed:', error);
            this.updateAvailabilityStatus('error', 'Unable to check availability. Please try again.');
        }
    }

    updateAvailabilityStatus(status, message) {
        const availabilityStatus = document.getElementById('availability-status');
        const statusDot = availabilityStatus.querySelector('.availability-dot');
        const statusText = availabilityStatus.querySelector('.status-text');

        availabilityStatus.className = `availability-status ${status}`;
        statusDot.className = `availability-dot ${status}`;
        statusText.textContent = message;
    }

    setupProgressIndicator() {
        const form = document.querySelector('form');
        if (!form) return;

        const steps = form.querySelectorAll('.form-section');
        const progressCircles = document.querySelectorAll('.progress-circle');
        const progressLines = document.querySelectorAll('.progress-line::after');

        const updateProgress = () => {
            let completedSteps = 0;
            
            steps.forEach((step, index) => {
                const inputs = step.querySelectorAll('input, select, textarea');
                let filledInputs = 0;
                
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        filledInputs++;
                    }
                });
                
                const completionRatio = filledInputs / inputs.length;
                
                if (completionRatio === 1) {
                    completedSteps++;
                    progressCircles[index].classList.add('completed');
                } else if (completionRatio > 0) {
                    progressCircles[index].classList.add('active');
                } else {
                    progressCircles[index].classList.remove('completed', 'active');
                }
            });

            // Update progress line
            const progressPercentage = (completedSteps / steps.length) * 100;
            document.documentElement.style.setProperty('--progress-width', `${progressPercentage}%`);
        };

        form.addEventListener('input', updateProgress);
        updateProgress();
    }

    setupFormValidation() {
        const form = document.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    this.showFieldError(input, 'This field is required');
                    isValid = false;
                } else {
                    this.clearFieldError(input);
                }
            });

            if (!isValid) {
                e.preventDefault();
                this.showToast('Please fill in all required fields', 'error');
            }
        });

        // Real-time validation
        form.addEventListener('input', (e) => {
            const input = e.target;
            if (input.hasAttribute('required') && input.value.trim()) {
                this.clearFieldError(input);
            }
        });
    }

    showFieldError(input, message) {
        const parent = input.parentElement;
        let errorElement = parent.querySelector('.field-error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            errorElement.style.color = '#e53e3e';
            errorElement.style.fontSize = '0.875rem';
            errorElement.style.marginTop = '0.25rem';
            parent.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        input.style.borderColor = '#e53e3e';
        input.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
    }

    clearFieldError(input) {
        const parent = input.parentElement;
        const errorElement = parent.querySelector('.field-error');
        
        if (errorElement) {
            errorElement.remove();
        }
        
        input.style.borderColor = '#e2e8f0';
        input.style.boxShadow = 'none';
    }

    async handleFormSubmit(e) {
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
        submitBtn.disabled = true;

        try {
            // Additional validation
            const form = e.target;
            const availabilityStatus = document.getElementById('availability-status');
            
            if (availabilityStatus && availabilityStatus.classList.contains('unavailable')) {
                e.preventDefault();
                this.showToast('Please select an available date and time', 'error');
                return;
            }

            // Simulate API call delay for better UX
            await new Promise(resolve => setTimeout(resolve, 1000));

        } catch (error) {
            console.error('Form submission error:', error);
        } finally {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    setupTooltips() {
        // Add tooltips for form fields
        const tooltips = {
            'schedule': 'Select your preferred date and time for the event',
            'place': 'Choose between BULWAGAN (capacity: 200) or CONFERENCE HALL (capacity: 50)',
            'purpose_of_event': 'Describe the purpose of your event (e.g., Seminar, Meeting, Workshop)',
            'rent_venue': 'Enter the rental cost for the venue in PHP'
        };

        Object.keys(tooltips).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.title = tooltips[id];
                element.style.cursor = 'help';
            }
        });
    }

    showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        
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

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    setupAdminPanel() {
        // Admin panel enhancements
        this.setupDataTableEnhancements();
        this.setupStatusFilters();
        this.setupBulkActions();
    }

    setupDataTableEnhancements() {
        const table = document.querySelector('.table');
        if (!table) return;

        // Add hover effects and row selection
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                rows.forEach(r => r.classList.remove('table-primary'));
                row.classList.add('table-primary');
            });
        });
    }

    setupStatusFilters() {
        const statusButtons = document.querySelectorAll('.status-filter-btn');
        statusButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Highlight active filter
                statusButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Filter table rows (if implemented)
                this.filterTableByStatus(btn.dataset.status);
            });
        });
    }

    setupBulkActions() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="booking_ids[]"]');
        
        if (selectAll && checkboxes.length > 0) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAll.checked = allChecked;
                });
            });
        }
    }

    filterTableByStatus(status) {
        // Implementation for filtering table rows by status
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const rowStatus = row.querySelector('.status-badge')?.textContent?.trim();
            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

// Initialize the booking system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new BookingSystem();
});

// Utility functions for API endpoints
const API = {
    checkAvailability: async (schedule, place) => {
        try {
            const response = await fetch(`${API_BASE_URL}check-availability.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ schedule, place })
            });
            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            return { available: false, message: 'Unable to check availability' };
        }
    },

    updateBookingStatus: async (bookingId, status) => {
        try {
            const response = await fetch(`${API_BASE_URL}bookings/${bookingId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status })
            });
            return await response.json();
        } catch (error) {
            console.error('Status update failed:', error);
            return { success: false };
        }
    }
};

// Export for use in other modules
window.BookingSystem = BookingSystem;
window.API = API;