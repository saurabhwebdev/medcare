<?php
class Appointment {
    private $db;
    private $clinic_settings;

    public function __construct($db) {
        $this->db = $db;
        $this->loadClinicSettings();
    }

    private function loadClinicSettings() {
        if (!isset($_SESSION['user_id'])) {
            error_log("No user_id in session when loading clinic settings");
            return;
        }

        $query = "SELECT * FROM clinic_settings WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $this->clinic_settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$this->clinic_settings) {
            error_log("No clinic settings found for user_id: " . $_SESSION['user_id']);
        }
    }

    public function scheduleAppointment($data) {
        try {
            if (!isset($_SESSION['user_id']) || !$this->clinic_settings) {
                error_log("Missing user_id or clinic settings");
                return false;
            }

            $query = "INSERT INTO appointments (
                clinic_id, patient_id, appointment_date, appointment_time,
                appointment_type, duration, notes, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $this->clinic_settings['id'],
                $data['patient_id'],
                $data['appointment_date'],
                $data['appointment_time'],
                $data['appointment_type'],
                $data['duration'] ?? $this->clinic_settings['appointment_duration'],
                $data['notes'] ?? null,
                'scheduled' // Default status for new appointments
            ]);

            if (!$result) {
                error_log("Failed to insert appointment: " . implode(", ", $stmt->errorInfo()));
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in scheduleAppointment: " . $e->getMessage());
            return false;
        }
    }

    public function getAppointments($filters = []) {
        $query = "SELECT a.*, 
                         p.first_name, p.last_name, p.registration_number,
                         p.phone as patient_phone
                  FROM appointments a
                  JOIN patients p ON a.patient_id = p.id
                  WHERE a.clinic_id = ?";
        
        $params = [$this->clinic_settings['id']];

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= " AND a.appointment_date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        $query .= " ORDER BY a.appointment_date, a.appointment_time";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAppointmentStatus($id, $status) {
        $query = "UPDATE appointments SET status = ? WHERE id = ? AND clinic_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $id, $this->clinic_settings['id']]);
    }

    public function getAvailableTimeSlots($date) {
        if (!isset($_SESSION['user_id'])) {
            error_log("No user_id in session");
            return [];
        }

        if (!$this->clinic_settings) {
            error_log("No clinic settings found");
            return [];
        }

        // Get working hours for the day
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        if (empty($this->clinic_settings['working_hours'])) {
            error_log("No working hours set in clinic settings");
            return [];
        }

        $workingHours = json_decode($this->clinic_settings['working_hours'], true);
        
        // Ensure working hours is valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid working hours JSON: " . json_last_error_msg());
            return [];
        }

        if (!isset($workingHours[$dayOfWeek]) || !$workingHours[$dayOfWeek]['enabled']) {
            error_log("No working hours for this day or day is disabled");
            return [];
        }

        $startTime = strtotime($workingHours[$dayOfWeek]['start']);
        $endTime = strtotime($workingHours[$dayOfWeek]['end']);
        
        if ($startTime === false || $endTime === false) {
            error_log("Invalid start or end time format");
            return [];
        }

        $duration = intval($this->clinic_settings['appointment_duration'] ?? 15) * 60;

        // Get existing appointments for the date
        $query = "SELECT appointment_time, duration 
                 FROM appointments 
                 WHERE appointment_date = ? 
                 AND clinic_id = ? 
                 AND status != 'cancelled'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$date, $this->clinic_settings['id']]);
        $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate available time slots
        $availableSlots = [];
        for ($time = $startTime; $time < $endTime; $time += $duration) {
            // Skip if this would create a slot that extends beyond end time
            if ($time + $duration > $endTime) {
                continue;
            }
            $slotTime = date('H:i:s', $time);
            if (!$this->isTimeSlotBooked($slotTime, $bookedSlots)) {
                $availableSlots[] = $slotTime;
            }
        }

        return $availableSlots;
    }

    private function isTimeSlotBooked($time, $bookedSlots) {
        $checkTime = strtotime($time);
        if ($checkTime === false) {
            return true; // If we can't parse the time, consider it booked
        }

        foreach ($bookedSlots as $slot) {
            if (empty($slot['appointment_time']) || empty($slot['duration'])) {
                continue;
            }

            $bookedStart = strtotime($slot['appointment_time']);
            if ($bookedStart === false) {
                continue;
            }

            $bookedEnd = $bookedStart + (intval($slot['duration']) * 60);
            
            if ($checkTime >= $bookedStart && $checkTime < $bookedEnd) {
                return true;
            }
        }
        return false;
    }

    public function getClinicId() {
        return $this->clinic_settings['id'] ?? null;
    }
} 