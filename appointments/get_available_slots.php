<?php
// Prevent any output before our JSON response
error_reporting(0);
session_start();
require_once '../includes/db_connect.php';
require_once 'Appointment.php';

// Ensure no output has been sent yet
if (headers_sent()) {
    exit('Headers already sent');
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if (isset($_GET['date'])) {
    try {
        $appointment = new Appointment($db);
        $slots = $appointment->getAvailableTimeSlots($_GET['date']);
        echo json_encode($slots);
    } catch (Exception $e) {
        error_log("Error getting available slots: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to get available slots']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Date parameter is required']);
} 