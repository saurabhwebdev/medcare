<?php 
require_once '../../includes/db_connect.php';
require_once '../Prescription.php';
require_once '../../patients/Patient.php';

$patientId = $_GET['patient_id'] ?? 0;

// Check for patient_id before any output
if (!$patientId) {
    header('Location: /pms/patients/views/list.php');
    exit;
}

$patient = new Patient($db);
$patientData = $patient->getPatient($patientId);

if (!$patientData) {
    header('Location: /pms/patients/views/list.php');
    exit;
}

// Include header after all potential redirects
require_once '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Create Prescription</h2>
            <div class="text-sm text-gray-600">
                Patient: <?php echo htmlspecialchars($patientData['first_name'] . ' ' . $patientData['last_name']); ?>
                (<?php echo htmlspecialchars($patientData['registration_number']); ?>)
            </div>
        </div>

        <form id="prescriptionForm" class="space-y-6">
            <input type="hidden" name="patient_id" value="<?php echo $patientId; ?>">
            
            <!-- Prescription Details -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Prescription Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="prescription_date" required
                               value="<?php echo date('Y-m-d'); ?>"
                               class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Follow-up Date</label>
                        <input type="date" name="follow_up_date"
                               class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Diagnosis</label>
                        <textarea name="diagnosis" rows="2" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Medications -->
            <div class="bg-gray-50 p-4 rounded-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Medications</h3>
                    <button type="button" onclick="addMedicationRow()"
                            class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Add Medication
                    </button>
                </div>
                <div id="medicationsContainer" class="space-y-4">
                    <!-- Medication rows will be added here -->
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Additional Notes</h3>
                <textarea name="notes" rows="3"
                          class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"></textarea>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="window.history.back()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Prescription
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let medicationRowCount = 0;

function addMedicationRow() {
    const container = document.getElementById('medicationsContainer');
    const rowHtml = `
        <div class="medication-row grid grid-cols-1 md:grid-cols-6 gap-4 p-4 bg-white rounded-md shadow-sm">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Medication Name</label>
                <input type="text" name="medications[${medicationRowCount}][name]" required
                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Dosage</label>
                <input type="text" name="medications[${medicationRowCount}][dosage]" required
                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Frequency</label>
                <input type="text" name="medications[${medicationRowCount}][frequency]" required
                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Duration</label>
                <input type="text" name="medications[${medicationRowCount}][duration]"
                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Instructions</label>
                <textarea name="medications[${medicationRowCount}][instructions]" rows="2"
                          class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"></textarea>
            </div>
            <div class="flex items-end">
                <button type="button" onclick="this.closest('.medication-row').remove()"
                        class="px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Remove
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
    medicationRowCount++;
}

// Add initial medication row
addMedicationRow();

document.getElementById('prescriptionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('../create_prescription.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            window.location.href = `view_prescription.php?id=${result.prescription_id}`;
        } else {
            alert(result.message || 'Failed to create prescription');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the prescription');
    }
});
</script> 