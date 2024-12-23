<?php 
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pms/auth/login.php');
    exit();
}

require_once '../includes/db_connect.php';

// Fetch existing settings
$stmt = $db->prepare("SELECT * FROM clinic_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['clinic_name']) || empty($_POST['doctor_name'])) {
            throw new Exception("Clinic name and doctor name are required.");
        }

        // Process working hours
        $workingHours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $start = isset($_POST[$day . '_start']) && !empty($_POST[$day . '_start']) 
                     ? $_POST[$day . '_start'] 
                     : '09:00';
            $end = isset($_POST[$day . '_end']) && !empty($_POST[$day . '_end']) 
                   ? $_POST[$day . '_end'] 
                   : '17:00';

            $workingHours[$day] = [
                'enabled' => isset($_POST[$day . '_enabled']),
                'start' => $start,
                'end' => $end
            ];
        }

        // Handle logo upload
        $logo_path = $settings['logo_path'] ?? null; // Keep existing path by default
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                $logo_path = '/pms/uploads/logos/' . $new_filename;
            }
        }

        // Handle signature upload
        $signature_path = $settings['digital_signature_path'] ?? null; // Keep existing path by default
        if (!empty($_POST['signature_data'])) {
            $upload_dir = '../uploads/signatures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Convert base64 signature to image and save
            $signature_data = $_POST['signature_data'];
            $signature_data = str_replace('data:image/png;base64,', '', $signature_data);
            $signature_data = str_replace(' ', '+', $signature_data);
            $signature_data = base64_decode($signature_data);
            
            $new_filename = uniqid() . '_signature.png';
            $upload_path = $upload_dir . $new_filename;
            
            if (file_put_contents($upload_path, $signature_data)) {
                $signature_path = '/pms/uploads/signatures/' . $new_filename;
            }
        }

        if ($settings) {
            // Update existing settings
            $stmt = $db->prepare("
                UPDATE clinic_settings 
                SET clinic_name = :clinic_name,
                    doctor_name = :doctor_name,
                    qualifications = :qualifications,
                    specializations = :specializations,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    consultation_fee = :consultation_fee,
                    working_hours = :working_hours,
                    logo_path = :logo_path,
                    license_number = :license_number,
                    registration_number = :registration_number,
                    tax_id = :tax_id,
                    experience_years = :experience_years,
                    languages_spoken = :languages_spoken,
                    emergency_contact = :emergency_contact,
                    website = :website,
                    city = :city,
                    state = :state,
                    postal_code = :postal_code,
                    country = :country,
                    follow_up_fee = :follow_up_fee,
                    emergency_fee = :emergency_fee,
                    appointment_duration = :appointment_duration,
                    max_appointments_per_day = :max_appointments_per_day,
                    prescription_footer = :prescription_footer,
                    terms_conditions = :terms_conditions,
                    cancellation_policy = :cancellation_policy,
                    digital_signature_path = :digital_signature_path
                WHERE user_id = :user_id
            ");
        } else {
            // Insert new settings
            $stmt = $db->prepare("
                INSERT INTO clinic_settings 
                (clinic_name, doctor_name, qualifications, specializations, 
                email, phone, address, consultation_fee, working_hours, logo_path, user_id,
                license_number, registration_number, tax_id, experience_years,
                languages_spoken, emergency_contact, website, city, state,
                postal_code, country, follow_up_fee, emergency_fee,
                appointment_duration, max_appointments_per_day,
                prescription_footer, terms_conditions, cancellation_policy,
                digital_signature_path)
                VALUES (:clinic_name, :doctor_name, :qualifications, :specializations,
                        :email, :phone, :address, :consultation_fee, :working_hours, 
                        :logo_path, :user_id, :license_number, :registration_number,
                        :tax_id, :experience_years, :languages_spoken, :emergency_contact,
                        :website, :city, :state, :postal_code, :country,
                        :follow_up_fee, :emergency_fee, :appointment_duration,
                        :max_appointments_per_day, :prescription_footer,
                        :terms_conditions, :cancellation_policy, :digital_signature_path)
            ");
        }
        
        // Prepare data for database
        $stmt->bindValue(':clinic_name', trim($_POST['clinic_name']));
        $stmt->bindValue(':doctor_name', trim($_POST['doctor_name']));
        $stmt->bindValue(':qualifications', trim($_POST['qualifications'] ?? ''));
        $stmt->bindValue(':specializations', trim($_POST['specializations'] ?? ''));
        $stmt->bindValue(':email', trim($_POST['email'] ?? ''));
        $stmt->bindValue(':phone', trim($_POST['phone'] ?? ''));
        $stmt->bindValue(':address', trim($_POST['address'] ?? ''));
        $stmt->bindValue(':consultation_fee', floatval($_POST['consultation_fee'] ?? 0));
        $stmt->bindValue(':working_hours', json_encode($workingHours));
        $stmt->bindValue(':logo_path', $logo_path);
        $stmt->bindValue(':user_id', $_SESSION['user_id']);
        $stmt->bindValue(':license_number', trim($_POST['license_number'] ?? ''));
        $stmt->bindValue(':registration_number', trim($_POST['registration_number'] ?? ''));
        $stmt->bindValue(':tax_id', trim($_POST['tax_id'] ?? ''));
        $stmt->bindValue(':experience_years', intval($_POST['experience_years'] ?? 0));
        $stmt->bindValue(':languages_spoken', trim($_POST['languages_spoken'] ?? ''));
        $stmt->bindValue(':emergency_contact', trim($_POST['emergency_contact'] ?? ''));
        $stmt->bindValue(':website', trim($_POST['website'] ?? ''));
        $stmt->bindValue(':city', trim($_POST['city'] ?? ''));
        $stmt->bindValue(':state', trim($_POST['state'] ?? ''));
        $stmt->bindValue(':postal_code', trim($_POST['postal_code'] ?? ''));
        $stmt->bindValue(':country', trim($_POST['country'] ?? ''));
        $stmt->bindValue(':follow_up_fee', floatval($_POST['follow_up_fee'] ?? 0));
        $stmt->bindValue(':emergency_fee', floatval($_POST['emergency_fee'] ?? 0));
        $stmt->bindValue(':appointment_duration', intval($_POST['appointment_duration'] ?? 15));
        $stmt->bindValue(':max_appointments_per_day', intval($_POST['max_appointments_per_day'] ?? 0));
        $stmt->bindValue(':prescription_footer', trim($_POST['prescription_footer'] ?? ''));
        $stmt->bindValue(':terms_conditions', trim($_POST['terms_conditions'] ?? ''));
        $stmt->bindValue(':cancellation_policy', trim($_POST['cancellation_policy'] ?? ''));
        $stmt->bindValue(':digital_signature_path', $signature_path);

        $stmt->execute();

        $success = "Settings saved successfully!";
        
        // Refresh settings after update
        $stmt = $db->prepare("SELECT * FROM clinic_settings WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(Exception $e) {
        $error = "Error saving settings: " . $e->getMessage();
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Clinic Settings</h2>

                <!-- Messages -->
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div id="success-message" class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                        <div class="flex justify-between items-start">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
                                </div>
                            </div>
                            <button type="button" onclick="closeSuccessMessage()" class="text-green-500 hover:text-green-700">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <script>
                        // Function to close the success message
                        function closeSuccessMessage() {
                            document.getElementById('success-message').style.display = 'none';
                        }
                        
                        // Auto-close after 3 seconds
                        setTimeout(function() {
                            closeSuccessMessage();
                        }, 3000);
                    </script>
                <?php endif; ?>

                <form method="POST" class="space-y-6" enctype="multipart/form-data">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Clinic Name</label>
                                <input type="text" name="clinic_name" 
                                       placeholder="e.g., City Medical Center"
                                       value="<?php echo htmlspecialchars($settings['clinic_name'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Doctor Name</label>
                                <input type="text" name="doctor_name" 
                                       placeholder="e.g., Dr. John Smith"
                                       value="<?php echo htmlspecialchars($settings['doctor_name'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Clinic Logo</label>
                                <div class="mt-1 flex items-center space-x-4">
                                    <?php if(!empty($settings['logo_path'])): ?>
                                        <div class="w-24 h-24 rounded-lg overflow-hidden">
                                            <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" 
                                                 alt="Clinic Logo" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex flex-col">
                                        <input type="file" 
                                               name="logo" 
                                               accept="image/*"
                                               class="block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-md file:border-0
                                                      file:text-sm file:font-semibold
                                                      file:bg-blue-50 file:text-blue-700
                                                      hover:file:bg-blue-100">
                                        <p class="mt-1 text-sm text-gray-500">
                                            PNG, JPG, GIF up to 2MB. Recommended size: 200x200px
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Details -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Professional Details</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Qualifications</label>
                                <textarea name="qualifications" rows="3" 
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"
                                          placeholder="Enter your qualifications (e.g., MBBS, MD)"><?php echo htmlspecialchars($settings['qualifications'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Specializations</label>
                                <textarea name="specializations" rows="3" 
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"
                                          placeholder="Enter your specializations"><?php echo htmlspecialchars($settings['specializations'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" 
                                       placeholder="e.g., doctor@clinic.com"
                                       value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" name="phone" 
                                       placeholder="e.g., +1 (555) 123-4567"
                                       value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea name="address" rows="3" 
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Consultation Fee -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Consultation Fee</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fee Amount</label>
                            <input type="number" name="consultation_fee" step="0.01"
                                   value="<?php echo htmlspecialchars($settings['consultation_fee'] ?? ''); ?>"
                                   class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                        </div>
                    </div>

                    <!-- Working Hours -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Working Hours</h3>
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        
                        // Initialize working hours from saved settings or defaults
                        $workingHours = [];
                        if (!empty($settings['working_hours'])) {
                            $savedHours = json_decode($settings['working_hours'], true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $workingHours = $savedHours;
                            }
                        }

                        // Set defaults for any missing days
                        foreach ($days as $day) {
                            $dayLower = strtolower($day);
                            $isWeekend = in_array($day, ['Saturday', 'Sunday']);
                            if (!isset($workingHours[$dayLower])) {
                                $workingHours[$dayLower] = [
                                    'enabled' => !$isWeekend,
                                    'start' => '09:00',
                                    'end' => '17:00'
                                ];
                            }
                        }

                        foreach ($days as $day):
                            $dayLower = strtolower($day);
                            $daySettings = $workingHours[$dayLower];
                        ?>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 items-center">
                                <div class="font-medium"><?php echo $day; ?></div>
                                <div>
                                    <input type="time" 
                                           name="<?php echo $dayLower; ?>_start"
                                           value="<?php echo htmlspecialchars($daySettings['start']); ?>"
                                           class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"
                                           <?php if (!$daySettings['enabled']) echo 'disabled'; ?>>
                                </div>
                                <div>
                                    <input type="time" 
                                           name="<?php echo $dayLower; ?>_end"
                                           value="<?php echo htmlspecialchars($daySettings['end']); ?>"
                                           class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"
                                           <?php if (!$daySettings['enabled']) echo 'disabled'; ?>>
                                </div>
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" 
                                               name="<?php echo $dayLower; ?>_enabled"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               <?php if ($daySettings['enabled']) echo 'checked'; ?>>
                                        <span class="ml-2">Enabled</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Initialize working hours JavaScript -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Initial state setup
                                document.querySelectorAll('input[type="checkbox"][name$="_enabled"]').forEach(checkbox => {
                                    const day = checkbox.name.replace('_enabled', '');
                                    const timeInputs = document.querySelectorAll(`input[name^="${day}_"][type="time"]`);
                                    timeInputs.forEach(input => {
                                        input.disabled = !checkbox.checked;
                                    });
                                });

                                // Handle changes
                                document.querySelectorAll('input[type="checkbox"][name$="_enabled"]').forEach(checkbox => {
                                    checkbox.addEventListener('change', function() {
                                        const day = this.name.replace('_enabled', '');
                                        const timeInputs = document.querySelectorAll(`input[name^="${day}_"][type="time"]`);
                                        timeInputs.forEach(input => {
                                            input.disabled = !this.checked;
                                        });
                                    });
                                });
                            });
                        </script>
                    </div>

                    <!-- License and Registration -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">License & Registration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Medical License Number</label>
                                <input type="text" name="license_number" required
                                       placeholder="e.g., ML123456"
                                       value="<?php echo htmlspecialchars($settings['license_number'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Registration Number</label>
                                <input type="text" name="registration_number"
                                       placeholder="e.g., REG/2024/12345"
                                       value="<?php echo htmlspecialchars($settings['registration_number'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tax ID/GST Number</label>
                                <input type="text" name="tax_id"
                                       placeholder="e.g., GSTIN1234567890"
                                       value="<?php echo htmlspecialchars($settings['tax_id'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Years of Experience</label>
                                <input type="number" name="experience_years"
                                       placeholder="e.g., 10"
                                       value="<?php echo htmlspecialchars($settings['experience_years'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                        </div>
                    </div>

                    <!-- Languages and Communication -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Languages & Communication</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Languages Spoken</label>
                            <textarea name="languages_spoken" rows="2"
                                      class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"
                                      placeholder="e.g., English, Spanish, French"><?php echo htmlspecialchars($settings['languages_spoken'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Extended Contact Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Extended Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Emergency Contact</label>
                                <input type="text" name="emergency_contact"
                                       placeholder="e.g., +1 (555) 987-6543"
                                       value="<?php echo htmlspecialchars($settings['emergency_contact'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Website</label>
                                <input type="url" name="website"
                                       placeholder="e.g., https://www.myclinic.com"
                                       value="<?php echo htmlspecialchars($settings['website'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Address -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detailed Address</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Street Address</label>
                                <textarea name="address" rows="2"
                                          placeholder="e.g., 123 Medical Plaza, Suite 456"
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <input type="text" name="city"
                                       placeholder="e.g., San Francisco"
                                       value="<?php echo htmlspecialchars($settings['city'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">State/Province</label>
                                <input type="text" name="state"
                                       placeholder="e.g., California"
                                       value="<?php echo htmlspecialchars($settings['state'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                                <input type="text" name="postal_code"
                                       placeholder="e.g., 94105"
                                       value="<?php echo htmlspecialchars($settings['postal_code'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Country</label>
                                <input type="text" name="country"
                                       value="<?php echo htmlspecialchars($settings['country'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                        </div>
                    </div>

                    <!-- Fee Structure -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Fee Structure</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Consultation Fee</label>
                                <input type="number" name="consultation_fee" step="0.01"
                                       placeholder="e.g., 100.00"
                                       value="<?php echo htmlspecialchars($settings['consultation_fee'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Follow-up Fee</label>
                                <input type="number" name="follow_up_fee" step="0.01"
                                       placeholder="e.g., 50.00"
                                       value="<?php echo htmlspecialchars($settings['follow_up_fee'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Emergency Fee</label>
                                <input type="number" name="emergency_fee" step="0.01"
                                       placeholder="e.g., 200.00"
                                       value="<?php echo htmlspecialchars($settings['emergency_fee'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Settings -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Appointment Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Appointment Duration (minutes)</label>
                                <input type="number" name="appointment_duration"
                                       placeholder="e.g., 30"
                                       value="<?php echo htmlspecialchars($settings['appointment_duration'] ?? '15'); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Max Appointments Per Day</label>
                                <input type="number" name="max_appointments_per_day"
                                       placeholder="e.g., 20"
                                       value="<?php echo htmlspecialchars($settings['max_appointments_per_day'] ?? ''); ?>"
                                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2">
                            </div>
                        </div>
                    </div>

                    <!-- Policies and Terms -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Policies and Terms</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prescription Footer Text</label>
                                <textarea name="prescription_footer" rows="2"
                                          placeholder="e.g., Please take medicines as prescribed. Follow up after 7 days."
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"><?php echo htmlspecialchars($settings['prescription_footer'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
                                <textarea name="terms_conditions" rows="3"
                                          placeholder="e.g., By booking an appointment, you agree to our clinic's policies..."
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"><?php echo htmlspecialchars($settings['terms_conditions'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cancellation Policy</label>
                                <textarea name="cancellation_policy" rows="3"
                                          placeholder="e.g., Appointments can be cancelled up to 24 hours before the scheduled time..."
                                          class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm p-2"><?php echo htmlspecialchars($settings['cancellation_policy'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Digital Signature</h3>
                        <div class="space-y-4">
                            <!-- Current Signature Preview -->
                            <?php if(!empty($settings['digital_signature_path'])): ?>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Signature</label>
                                    <div class="w-64 h-32 border border-gray-300 rounded-lg overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($settings['digital_signature_path']); ?>" 
                                             alt="Current Signature" 
                                             class="w-full h-full object-contain">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Signature Pad -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Draw New Signature</label>
                                <div class="relative">
                                    <canvas id="signaturePad" 
                                            class="border border-gray-300 rounded-lg bg-white" 
                                            width="500" 
                                            height="200"></canvas>
                                    <input type="hidden" name="signature_data" id="signatureData">
                                    <div class="mt-2 space-x-2">
                                        <button type="button" 
                                                onclick="clearSignature()" 
                                                class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                            Clear
                                        </button>
                                        <button type="button" 
                                                onclick="undoSignature()" 
                                                class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                            Undo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize SignaturePad
const canvas = document.getElementById('signaturePad');
const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(255, 255, 255)',
    penColor: 'rgb(0, 0, 0)',
    minWidth: 0.5,
    maxWidth: 2.5,
    throttle: 16, // Increase smoothness of drawing
    velocityFilterWeight: 0.7 // Adjust line smoothing
});

// Resize canvas to fit container while maintaining proper coordinate mapping
function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const container = canvas.parentElement;
    const containerWidth = container.offsetWidth;
    
    // Set canvas display size
    canvas.style.width = '100%';
    canvas.style.height = '200px';
    
    // Set canvas resolution
    canvas.width = containerWidth * ratio;
    canvas.height = 200 * ratio;
    
    // Scale context to match resolution
    const context = canvas.getContext('2d');
    context.scale(ratio, ratio);
    
    // Clear and set new background
    signaturePad.clear();
}

// Set up the canvas size initially
window.onload = function() {
    resizeCanvas();
};

// Handle window resize
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(resizeCanvas, 200);
});

// Clear signature
function clearSignature() {
    signaturePad.clear();
}

// Undo last stroke
function undoSignature() {
    const data = signaturePad.toData();
    if (data) {
        data.pop(); // Remove the last stroke
        signaturePad.fromData(data);
    }
}

// Save signature data before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    if (!signaturePad.isEmpty()) {
        const signatureData = signaturePad.toDataURL('image/png');
        document.getElementById('signatureData').value = signatureData;
    }
});
</script>

<?php include '../includes/footer.php'; ?> 