# Kanlahi Tarlac Booking System

A complete booking management system built with PHP and MySQL, featuring a customer booking interface and a secure admin panel.

## Features

### Customer Side
- **Booking Form**: Clean, responsive form for customers to book venues
- **Reference Number**: Automatic generation of unique 8-character alphanumeric booking reference
- **Validation**: Client and server-side validation for all form fields
- **Success Display**: Clear confirmation with booking reference number

### Admin Panel
- **Secure Login**: Password-protected admin authentication
- **Dashboard**: Overview with booking statistics and recent bookings
- **Booking Management**: Complete CRUD operations for bookings
- **Status Management**: Update booking status (New, Contacted, Confirmed, Completed)
- **Internal Notes**: Add private notes for each booking
- **Filtering**: Filter bookings by venue, status, or search terms
- **Customer Contact**: Direct call and email links

## Technical Specifications

### Database Schema
- **users**: Admin user accounts with password hashing
- **venues**: Venue information (name, capacity)
- **bookings**: Booking details with reference numbers and status tracking

### Security Features
- **Password Hashing**: Uses `password_hash()` for secure password storage
- **SQL Injection Prevention**: PDO prepared statements throughout
- **Session Management**: Secure session handling for admin authentication
- **Input Validation**: Comprehensive validation on all user inputs

### Frontend
- **Bootstrap 5**: Responsive, modern UI design
- **Font Awesome**: Professional iconography
- **Custom Styling**: Gradient themes and polished design

## Installation

### 1. Database Setup
1. Import the `setup.sql` file into your MySQL database
2. This will create the database, tables, and sample data

```sql
mysql -u username -p < setup.sql
```

### 2. Web Server Configuration
1. Place all files in your web server directory (e.g., `htdocs` for XAMPP)
2. Ensure PHP and MySQL are properly configured

### 3. Database Configuration
Edit `config/database.php` with your database credentials:

```php
$host = 'localhost';
$db   = 'kanlahi_booking';
$user = 'your_username';
$pass = 'your_password';
```

## Usage

### Customer Booking
1. Visit the main page (`index.php`)
2. Fill out the booking form with:
   - Full Name
   - Email Address
   - Phone Number
   - Venue Selection
   - Event Date
   - Event Description
3. Submit the form to receive a booking reference number

### Admin Panel
1. Access the admin login at `admin/login.php`
2. Default credentials:
   - Username: `admin`
   - Password: `admin123`
3. Use the dashboard to:
   - View booking statistics
   - Manage all bookings
   - Update booking statuses
   - Add internal notes
   - Filter and search bookings

## File Structure

```
BookingSystem/
├── config/
│   ├── database.php      # Database connection
│   ├── auth.php          # Authentication functions
│   └── functions.php     # Helper functions
├── admin/
│   ├── login.php         # Admin login page
│   ├── dashboard.php     # Admin dashboard
│   ├── bookings.php      # Bookings management
│   ├── view_booking.php  # Individual booking view
│   └── logout.php        # Logout handler
├── index.php             # Customer booking form
├── setup.sql             # Database schema
└── README.md             # This file
```

## Security Notes

- Always change the default admin password after installation
- Ensure your web server has proper file permissions
- Consider using HTTPS in production environments
- Regularly backup your database

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is open source and available under the MIT License.# KanlahiBookingSystem
# KanlahiBookingSystem
