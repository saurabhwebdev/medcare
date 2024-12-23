<?php
require_once '../includes/db_connect.php';
require_once 'Patient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient = new Patient($db);
    
    $data = [
        'condition_name' => $_POST['condition_name'],
        'diagnosis_date' => $_POST['diagnosis_date'],
        'status' => $_POST['status']
    ];

    if ($patient->addMedicalHistory($_POST['patient_id'], $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Medical history added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add medical history']);
    }
} 