<?php
require_once '../includes/db_connect.php';
require_once 'Patient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient = new Patient($db);
    
    $data = [
        'medication_name' => $_POST['medication_name'],
        'dosage' => $_POST['dosage'],
        'frequency' => $_POST['frequency'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'] ?: null,
        'prescribed_by' => $_POST['prescribed_by'],
        'status' => $_POST['status']
    ];

    if ($patient->addMedication($_POST['patient_id'], $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Medication added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add medication']);
    }
} 