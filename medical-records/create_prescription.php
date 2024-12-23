<?php
require_once '../includes/db_connect.php';
require_once 'Prescription.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $prescription = new Prescription($db);
        
        // Prepare medications data
        $medications = [];
        foreach ($_POST['medications'] as $med) {
            $medications[] = [
                'name' => $med['name'],
                'dosage' => $med['dosage'],
                'frequency' => $med['frequency'],
                'duration' => $med['duration'] ?? null,
                'instructions' => $med['instructions'] ?? null
            ];
        }

        $data = [
            'patient_id' => $_POST['patient_id'],
            'prescription_date' => $_POST['prescription_date'],
            'diagnosis' => $_POST['diagnosis'],
            'notes' => $_POST['notes'],
            'follow_up_date' => $_POST['follow_up_date'] ?: null,
            'medications' => $medications
        ];

        $prescriptionId = $prescription->createPrescription($data);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Prescription created successfully',
            'prescription_id' => $prescriptionId
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create prescription: ' . $e->getMessage()
        ]);
    }
} 