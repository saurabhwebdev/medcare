<?php
error_reporting(0);
session_start();
require_once '../includes/db_connect.php';
require_once 'Appointment.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $appointment = new Appointment($db);
        $stmt = $db->prepare("UPDATE appointments SET notes = ? WHERE id = ? AND clinic_id = ?");
        
        $result = $stmt->execute([
            $_POST['notes'] ?? '',
            $_POST['appointment_id'],
            $appointment->getClinicId()
        ]);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Notes updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update notes']);
        }
    } catch (Exception $e) {
        error_log("Error updating notes: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while updating notes']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
} 