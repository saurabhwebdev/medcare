<?php
// Prevent any output before our JSON response
error_reporting(0);
session_start();
require_once '../includes/db_connect.php';
require_once 'Appointment.php';

// Ensure no output has been sent yet
if (headers_sent()) {
    exit(json_encode(['status' => 'error', 'message' => 'Headers already sent']));
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $appointment = new Appointment($db);
        
        // Validate required fields
        $required_fields = ['patient_id', 'appointment_date', 'appointment_time', 'appointment_type'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
                exit;
            }
        }
        
        if ($appointment->scheduleAppointment($_POST)) {
            echo json_encode(['status' => 'success', 'message' => 'Appointment scheduled successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to schedule appointment']);
        }
    } catch (Exception $e) {
        error_log("Error scheduling appointment: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while scheduling the appointment']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
} 