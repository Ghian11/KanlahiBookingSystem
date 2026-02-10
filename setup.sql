    -- Kanlahi Tarlac Booking System Database Schema
    -- Create database
    CREATE DATABASE IF NOT EXISTS kanlahi_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    USE kanlahi_booking;

    -- Create users table (for admin)
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Create venues table
    CREATE TABLE IF NOT EXISTS venues (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        capacity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Create bookings table
    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ref_number VARCHAR(20) UNIQUE,
        title VARCHAR(255) NOT NULL,
        start_event DATETIME NOT NULL,
        end_event DATETIME NOT NULL,
        description TEXT,
        status ENUM('New', 'Contacted', 'Confirmed', 'Completed', 'Unavailable') DEFAULT 'New',
        customer_name VARCHAR(100),
        address TEXT,
        contact_no VARCHAR(20),
        email VARCHAR(100),
        place VARCHAR(100),
        rent_venue DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Create venue_availability table to track blocked dates
    CREATE TABLE IF NOT EXISTS venue_availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venue_name VARCHAR(100) NOT NULL,
        event_date DATE NOT NULL,
        status ENUM('blocked', 'available') DEFAULT 'blocked',
        booking_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        UNIQUE KEY unique_venue_date (venue_name, event_date)
    );

    -- Insert default admin user (username: admin, password: admin123)
    INSERT INTO users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') 
    ON DUPLICATE KEY UPDATE username=username;

    -- Insert default venues
    INSERT INTO venues (name, capacity) VALUES 
    ('Bulwagan Kanlahi', 200),
    ('Conference Room', 50)
    ON DUPLICATE KEY UPDATE name=name;