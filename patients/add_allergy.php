<?php
require_once '../includes/db_connect.php';
require_once 'Patient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient = new Patient($db);
    
    $data = [
        'allergy_type' => $_POST['allergy_type'],
        'allergen' => $_POST['allergen'],
        'severity' => $_POST['severity'],
        'reaction' => $_POST['reaction']
    ];

    if ($patient->addAllergy($_POST['patient_id'], $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Allergy added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add allergy']);
    }
} 