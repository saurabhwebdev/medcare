CREATE DATABASE IF NOT EXISTS patient_management;
USE patient_management;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clinic settings table
CREATE TABLE IF NOT EXISTS clinic_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    clinic_name VARCHAR(200) NOT NULL,
    doctor_name VARCHAR(200),
    clinic_code VARCHAR(10) NOT NULL DEFAULT 'DEF',
    license_number VARCHAR(100),
    registration_number VARCHAR(100),
    tax_id VARCHAR(50),
    qualifications TEXT,
    specializations TEXT,
    experience_years INT,
    languages_spoken TEXT,
    email VARCHAR(100),
    phone VARCHAR(20),
    emergency_contact VARCHAR(20),
    website VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    consultation_fee DECIMAL(10,2),
    follow_up_fee DECIMAL(10,2),
    emergency_fee DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'USD',
    payment_methods JSON,
    working_hours JSON,
    break_hours JSON,
    appointment_duration INT DEFAULT 15,
    max_appointments_per_day INT,
    logo_path VARCHAR(255),
    digital_signature_path VARCHAR(255),
    clinic_photos JSON,
    social_media JSON,
    notification_preferences JSON,
    prescription_footer TEXT,
    terms_conditions TEXT,
    cancellation_policy TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clinic_id INT,
    registration_number VARCHAR(20),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    blood_group VARCHAR(5),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    insurance_provider VARCHAR(100),
    insurance_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (clinic_id) REFERENCES clinic_settings(id)
);

-- Medical history table
CREATE TABLE IF NOT EXISTS medical_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    condition_name VARCHAR(100),
    diagnosis_date DATE,
    status ENUM('active', 'resolved', 'ongoing'),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Allergies table
CREATE TABLE IF NOT EXISTS allergies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    allergy_type VARCHAR(50),
    allergen VARCHAR(100),
    severity ENUM('mild', 'moderate', 'severe'),
    reaction TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Medications table
CREATE TABLE IF NOT EXISTS medications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    medication_name VARCHAR(100),
    dosage VARCHAR(50),
    frequency VARCHAR(50),
    start_date DATE,
    end_date DATE,
    prescribed_by VARCHAR(100),
    status ENUM('active', 'discontinued', 'completed'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Patient documents table
CREATE TABLE IF NOT EXISTS patient_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    document_type VARCHAR(50),
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clinic_id INT,
    patient_id INT,
    appointment_date DATE,
    appointment_time TIME,
    appointment_type ENUM('regular', 'follow_up', 'emergency', 'specialized') NOT NULL,
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    duration INT DEFAULT 15,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (clinic_id) REFERENCES clinic_settings(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Prescriptions table
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    clinic_id INT NOT NULL,
    prescription_date DATE NOT NULL,
    diagnosis TEXT,
    notes TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (clinic_id) REFERENCES clinic_settings(id)
);

-- Prescription medications table (for medications prescribed in each prescription)
CREATE TABLE IF NOT EXISTS prescription_medications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prescription_id INT NOT NULL,
    medication_name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration VARCHAR(50),
    instructions TEXT,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id)
); 