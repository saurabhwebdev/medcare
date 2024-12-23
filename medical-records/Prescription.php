<?php
class Prescription {
    private $db;
    private $clinic_settings;

    public function __construct($db) {
        $this->db = $db;
        $this->loadClinicSettings();
    }

    private function loadClinicSettings() {
        $query = "SELECT * FROM clinic_settings LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->clinic_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPrescription($data) {
        try {
            $this->db->beginTransaction();

            // Insert prescription
            $query = "INSERT INTO prescriptions (
                patient_id, clinic_id, prescription_date, 
                diagnosis, notes, follow_up_date
            ) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['patient_id'],
                $this->clinic_settings['id'],
                $data['prescription_date'],
                $data['diagnosis'],
                $data['notes'],
                $data['follow_up_date']
            ]);

            $prescriptionId = $this->db->lastInsertId();

            // Insert prescribed medications
            foreach ($data['medications'] as $medication) {
                $query = "INSERT INTO prescription_medications (
                    prescription_id, medication_name, dosage, 
                    frequency, duration, instructions
                ) VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    $prescriptionId,
                    $medication['name'],
                    $medication['dosage'],
                    $medication['frequency'],
                    $medication['duration'],
                    $medication['instructions']
                ]);
            }

            $this->db->commit();
            return $prescriptionId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPrescription($id) {
        // Get prescription details
        $query = "SELECT p.*, 
                        CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                        pat.registration_number
                 FROM prescriptions p
                 JOIN patients pat ON p.patient_id = pat.id
                 WHERE p.id = ? AND p.clinic_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id, $this->clinic_settings['id']]);
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prescription) {
            return false;
        }

        // Get prescribed medications
        $query = "SELECT * FROM prescription_medications WHERE prescription_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $prescription['medications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $prescription;
    }

    public function getPatientPrescriptions($patientId) {
        $query = "SELECT p.*, COUNT(pm.id) as medication_count 
                 FROM prescriptions p
                 LEFT JOIN prescription_medications pm ON p.id = pm.prescription_id
                 WHERE p.patient_id = ? AND p.clinic_id = ?
                 GROUP BY p.id
                 ORDER BY p.prescription_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$patientId, $this->clinic_settings['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 