-- Blood Bank Management System Database Schema
-- Run this script to set up the database tables

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('donor', 'staff', 'hospital') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    national_id VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    weight_kg DECIMAL(5,2),
    county VARCHAR(100),
    town VARCHAR(100),
    medical_notes TEXT,
    is_eligible BOOLEAN DEFAULT TRUE,
    deferral_until DATE NULL,
    deferral_reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20),
    county VARCHAR(100),
    license_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    location VARCHAR(255),
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);

CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    appointment_id INT NULL,
    donation_date DATE NOT NULL,
    volume_ml INT DEFAULT 450,
    hemoglobin DECIMAL(4,2) NULL,
    blood_pressure VARCHAR(20) NULL,
    pulse INT NULL,
    donation_site VARCHAR(255),
    staff_id INT NULL,
    status ENUM('completed', 'rejected') DEFAULT 'completed',
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
);

CREATE TABLE blood_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    component ENUM('whole_blood', 'plasma', 'platelets', 'red_cells') DEFAULT 'whole_blood',
    volume_ml INT NOT NULL,
    collected_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    location VARCHAR(255) DEFAULT 'Main Storage',
    status ENUM('available', 'reserved', 'used', 'expired', 'discarded') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    patient_name VARCHAR(255) NOT NULL,
    patient_age INT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    component ENUM('whole_blood', 'plasma', 'platelets', 'red_cells') DEFAULT 'whole_blood',
    units_requested INT NOT NULL,
    urgency ENUM('routine', 'urgent', 'emergency') DEFAULT 'routine',
    clinical_notes TEXT,
    required_by DATE NULL,
    status ENUM('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
    fulfilled_at TIMESTAMP NULL,
    fulfilled_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    FOREIGN KEY (fulfilled_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    component ENUM('whole_blood', 'plasma', 'platelets', 'red_cells') NOT NULL,
    min_units INT DEFAULT 10,
    critical_units INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_blood_component (blood_type, component)
);

-- Create indexes for better performance
CREATE INDEX idx_donors_user_id ON donors(user_id);
CREATE INDEX idx_donors_blood_type ON donors(blood_type);
CREATE INDEX idx_hospitals_user_id ON hospitals(user_id);
CREATE INDEX idx_appointments_donor_id ON appointments(donor_id);
CREATE INDEX idx_appointments_scheduled_at ON appointments(scheduled_at);
CREATE INDEX idx_donations_donor_id ON donations(donor_id);
CREATE INDEX idx_donations_donation_date ON donations(donation_date);
CREATE INDEX idx_blood_units_blood_type ON blood_units(blood_type);
CREATE INDEX idx_blood_units_component ON blood_units(component);
CREATE INDEX idx_blood_units_status ON blood_units(status);
CREATE INDEX idx_blood_units_expiry_date ON blood_units(expiry_date);
CREATE INDEX idx_requests_hospital_id ON requests(hospital_id);
CREATE INDEX idx_requests_blood_type ON requests(blood_type);
CREATE INDEX idx_requests_urgency ON requests(urgency);
CREATE INDEX idx_requests_status ON requests(status);

-- Insert demo users (passwords are all 'password')
INSERT INTO users (name, email, password_hash, role) VALUES
('John Donor', 'donor@test.ke', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewfLkI0qQcO8mRK', 'donor'),
('Jane Staff', 'staff@bloodbank.ke', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewfLkI0qQcO8mRK', 'staff'),
('City Hospital', 'hospital@test.ke', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewfLkI0qQcO8mRK', 'hospital');

-- Insert demo donor
INSERT INTO donors (user_id, national_id, phone, date_of_birth, gender, blood_type, weight_kg, county, town) VALUES
(1, '12345678', '0712345678', '1990-01-01', 'male', 'O+', 75.5, 'Nairobi', 'Westlands');

-- Insert demo hospital
INSERT INTO hospitals (user_id, phone, county, license_number) VALUES
(3, '0723456789', 'Nairobi', 'HOSP001');