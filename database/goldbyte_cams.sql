-- 1. Create the database
CREATE DATABASE IF NOT EXISTS goldbyte_cams;
USE goldbyte_cams;

-- 2. Users Table (Login credentials for everyone)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone_number VARCHAR(20) NOT NULL, -- Essential for Arkesel SMS
    role ENUM('Admin', 'Doctor', 'Patient') DEFAULT 'Patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Specialties Table (Pre-defined categories)
CREATE TABLE specialties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- 4. Doctors Table (Linked to Users & Specialties)
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialty_id INT NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL, -- Medical Practicing License
    cv_path VARCHAR(255), -- Store file path for the uploaded CV
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES specialties(id)
);

-- 5. Appointments Table (The Core Business Logic)
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT, -- Can be NULL until a doctor is assigned to that specialty
    specialty_id INT NOT NULL, -- Patient chooses specialty, not specific doctor
    appointment_date DATETIME NOT NULL,
    service_type ENUM('In-Clinic', 'Home-Service') DEFAULT 'In-Clinic',
    home_address TEXT, -- Required only if Home-Service is selected
    is_emergency BOOLEAN DEFAULT FALSE, -- Adds 200 GH₵ if TRUE
    total_fee DECIMAL(10, 2) DEFAULT 100.00, -- 100 base + 100 (Home) + 200 (Emerg)
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (specialty_id) REFERENCES specialties(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- 6. Insert Default Specialties (For Testing)
INSERT INTO specialties (name) VALUES 
('General Purpose'), 
('Dentist'), 
('Optometrist'), 
('Gynecologist'), 
('Pediatrician');