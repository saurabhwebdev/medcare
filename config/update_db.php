<?php
require_once 'database.php';

try {
    // Create appointments table
    $query = "CREATE TABLE IF NOT EXISTS appointments (
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
    )";
    
    $db->exec($query);
    echo "Appointments table created successfully!";
} catch(PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?> 