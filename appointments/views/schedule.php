<?php 
require_once '../../includes/header.php';
require_once '../../includes/db_connect.php';
require_once '../Appointment.php';
require_once '../../patients/Patient.php';

$appointment = new Appointment($db);
$patient = new Patient($db);

// Get patient list for dropdown
$patients = $patient->getAllPatients();

// Get clinic settings for appointment duration
$stmt = $db->prepare("SELECT appointment_duration FROM clinic_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$defaultDuration = $settings['appointment_duration'] ?? 15;
?>

<!-- Add Select2 CSS in the head section -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Add Select2 JS after jQuery -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Schedule Appointment</h2>
        <a href="list.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <form id="appointmentForm" class="space-y-6">
            <!-- Patient Selection -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Patient Information</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Patient</label>
                        <select name="patient_id" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 select2-patient">
                            <option value="">Select a patient</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo htmlspecialchars($p['registration_number'] . ' - ' . $p['first_name'] . ' ' . $p['last_name'] . ' (' . $p['phone'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Appointment Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="appointment_date" required
                               min="<?php echo date('Y-m-d'); ?>"
                               class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Time</label>
                        <select name="appointment_time" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                            <option value="">Select time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="appointment_type" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                            <option value="regular">Regular Consultation</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="emergency">Emergency</option>
                            <option value="specialized">Specialized Service</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                        <input type="number" name="duration" min="15" step="15"
                               value="<?php echo htmlspecialchars($defaultDuration); ?>"
                               class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        </textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="window.history.back()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Schedule Appointment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Select2
$(document).ready(function() {
    $('.select2-patient').select2({
        placeholder: 'Search patient by name, registration number, or phone',
        allowClear: true,
        width: '100%',
        // Customize the dropdown
        templateResult: formatPatient,
        templateSelection: formatPatient,
        // Enable search on both name and registration number
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            
            if (typeof data.text === 'undefined') {
                return null;
            }
            
            // Search in the text
            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                return data;
            }
            
            return null;
        }
    });
});

// Format the patient display in dropdown
function formatPatient(patient) {
    if (!patient.id) {
        return patient.text;
    }
    
    // Split the text to get name, registration number, and phone
    const parts = patient.text.split(' - ');
    if (parts.length !== 3) {
        return patient.text;
    }
    
    const name = parts[0];
    const regNo = parts[1];
    const phone = parts[2];
    
    const $patient = $(
        '<div class="flex flex-col">' +
            '<div class="font-medium">' + name + '</div>' +
            '<div class="text-xs text-gray-600">' +
                '<span class="mr-2">' + regNo + '</span>' +
                '<span>' + phone + '</span>' +
            '</div>' +
        '</div>'
    );
    
    return $patient;
}

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('[name="appointment_date"]');
    const timeSelect = document.querySelector('[name="appointment_time"]');
    
    dateInput.addEventListener('change', async function(e) {
        const date = e.target.value;
        
        // Clear and disable the time select while loading
        timeSelect.innerHTML = '<option value="">Loading...</option>';
        timeSelect.disabled = true;
        
        try {
            const response = await fetch(`../get_available_slots.php?date=${date}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const slots = await response.json();
            
            // Clear existing options
            timeSelect.innerHTML = '<option value="">Select time</option>';
            
            if (!slots || slots.length === 0) {
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "No available slots for this day";
                timeSelect.appendChild(option);
                timeSelect.disabled = true;
                return;
            }
            
            timeSelect.disabled = false;
            for (let slot of slots) {
                const option = document.createElement('option');
                option.value = slot;
                // Format time in 12-hour format
                const [hours, minutes] = slot.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                option.textContent = `${hour12}:${minutes} ${ampm}`;
                timeSelect.appendChild(option);
            }
        } catch (error) {
            console.error('Error fetching time slots:', error);
            timeSelect.innerHTML = '<option value="">Error loading time slots</option>';
            timeSelect.disabled = true;
        }
    });
});

document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        
        // Validate form data before submitting
        const requiredFields = ['patient_id', 'appointment_date', 'appointment_time', 'appointment_type'];
        for (const field of requiredFields) {
            if (!formData.get(field)) {
                alert(`Please fill in all required fields`);
                return;
            }
        }

        const response = await fetch('../schedule_appointment.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message); // Show success message
            window.location.href = 'list.php';
        } else {
            alert(result.message || 'Failed to schedule appointment');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while scheduling the appointment. Please try again.');
    }
});
</script>

<style>
/* Custom styles for Select2 dropdown to match other inputs */
.select2-container--default .select2-selection--single {
    height: 42px;
    padding: 8px;
    border-color: rgb(209, 213, 219);
    border-radius: 0.375rem;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #2563eb;
}

.select2-dropdown {
    border-color: rgb(209, 213, 219);
    border-radius: 0.375rem;
}

.select2-search__field {
    border-radius: 4px !important;
    padding: 8px !important;
    border-color: rgb(209, 213, 219) !important;
}

.select2-results__option {
    padding: 8px !important;
}

/* Match the focus state with other inputs */
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}
</style> 