<?php
class Patient {
    private $db;
    private $clinic_settings;

    public function __construct($db) {
        $this->db = $db;
        $this->loadClinicSettings();
    }

    private function loadClinicSettings() {
        // First check if there are any clinic settings
        $query = "SELECT * FROM clinic_settings LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            // Create default clinic settings if none exist
            $query = "INSERT INTO clinic_settings (clinic_name, doctor_name, clinic_code) 
                     VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['Default Clinic', 'Default Doctor', 'DEF']);
            
            // Fetch the newly created settings
            $query = "SELECT * FROM clinic_settings WHERE id = LAST_INSERT_ID()";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $this->clinic_settings = $settings;
    }

    public function registerPatient($data) {
        $registration_number = $this->generateRegistrationNumber();
        
        $query = "INSERT INTO patients (
            clinic_id, registration_number, first_name, last_name, 
            date_of_birth, gender, blood_group, email, phone, 
            address, emergency_contact_name, emergency_contact_phone,
            insurance_provider, insurance_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->clinic_settings['id'],
            $registration_number,
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['blood_group'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['emergency_contact_name'],
            $data['emergency_contact_phone'],
            $data['insurance_provider'],
            $data['insurance_id']
        ]);
    }

    private function generateRegistrationNumber() {
        $prefix = $this->clinic_settings['clinic_code'] ?? 'PAT';
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM patients WHERE YEAR(created_at) = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        return sprintf("%s%d%05d", $prefix, $year % 100, $count);
    }

    // Additional methods for patient management
    public function updatePatient($id, $data) {
        $query = "UPDATE patients SET 
                  first_name = ?, last_name = ?, date_of_birth = ?,
                  gender = ?, blood_group = ?, email = ?, phone = ?,
                  address = ?, emergency_contact_name = ?,
                  emergency_contact_phone = ?, insurance_provider = ?,
                  insurance_id = ?
                  WHERE id = ? AND clinic_id = ?";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['blood_group'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['emergency_contact_name'],
            $data['emergency_contact_phone'],
            $data['insurance_provider'],
            $data['insurance_id'],
            $id,
            $this->clinic_settings['id']
        ]);
    }

    public function getPatient($id) {
        // First get the basic patient information
        $query = "SELECT * FROM patients WHERE id = ? AND clinic_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id, $this->clinic_settings['id']]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$patient) {
            return false;
        }

        // Get medical history
        $query = "SELECT * FROM medical_history WHERE patient_id = ? ORDER BY diagnosis_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $patient['medical_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get allergies
        $query = "SELECT * FROM allergies WHERE patient_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $patient['allergies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get medications
        $query = "SELECT * FROM medications WHERE patient_id = ? ORDER BY start_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $patient['medications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get documents
        $query = "SELECT * FROM patient_documents WHERE patient_id = ? ORDER BY upload_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $patient['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $patient;
    }

    public function addMedicalHistory($patient_id, $data) {
        // Implementation
    }

    public function addAllergy($patient_id, $data) {
        $query = "INSERT INTO allergies (
            patient_id, allergy_type, allergen, severity, reaction
        ) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $patient_id,
            $data['allergy_type'],
            $data['allergen'],
            $data['severity'],
            $data['reaction']
        ]);
    }

    public function addMedication($patient_id, $data) {
        $query = "INSERT INTO medications (
            patient_id, medication_name, dosage, frequency,
            start_date, end_date, prescribed_by, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $patient_id,
            $data['medication_name'],
            $data['dosage'],
            $data['frequency'],
            $data['start_date'],
            $data['end_date'],
            $data['prescribed_by'],
            $data['status']
        ]);
    }

    public function uploadDocument($patient_id, $file_data) {
        $upload_dir = '../uploads/patients/' . $patient_id . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = uniqid() . '_' . basename($file_data['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_data['tmp_name'], $file_path)) {
            $query = "INSERT INTO patient_documents 
                     (patient_id, document_type, file_name, file_path) 
                     VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                $patient_id,
                $file_data['type'],
                $file_name,
                $file_path
            ]);
        }
        return false;
    }

    public function getAllPatients($filters = []) {
        if (!$this->clinic_settings) {
            $this->loadClinicSettings();
        }
        
        $query = "SELECT * FROM patients";
        $params = [];
        
        // Add WHERE clause only if we have clinic settings
        if (isset($this->clinic_settings['id'])) {
            $query .= " WHERE clinic_id = ?";
            $params[] = $this->clinic_settings['id'];
        }
        
        // Add any additional filters here
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $query .= " AND $key LIKE ?";
                    $params[] = "%$value%";
                }
            }
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllergies($patient_id) {
        $query = "SELECT * FROM allergies WHERE patient_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMedications($patient_id) {
        $query = "SELECT * FROM medications WHERE patient_id = ? ORDER BY start_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPatients() {
        try {
            $query = "SELECT COUNT(*) FROM patients WHERE clinic_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->clinic_settings['id']]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error getting total patients: " . $e->getMessage());
            return 0;
        }
    }
} 