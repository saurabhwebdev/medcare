<?php
require_once '../config/database.php';
require_once 'Patient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient = new Patient($db);
    
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'date_of_birth' => $_POST['date_of_birth'],
        'gender' => $_POST['gender'],
        'blood_group' => $_POST['blood_group'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'emergency_contact_name' => $_POST['emergency_contact_name'],
        'emergency_contact_phone' => $_POST['emergency_contact_phone'],
        'insurance_provider' => $_POST['insurance_provider'],
        'insurance_id' => $_POST['insurance_id']
    ];

    if ($patient->registerPatient($data)) {
        echo json_encode(['status' => 'success', 'message' => 'Patient registered successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to register patient']);
    }
}
?> 