<?php 
require_once '../../includes/header.php';
require_once '../../includes/db_connect.php';
require_once '../Patient.php';
require_once '../../medical-records/Prescription.php';

$id = $_GET['id'] ?? 0;
$patient = new Patient($db);
$patientData = $patient->getPatient($id);

if (!$patientData) {
    $_SESSION['error'] = "Patient not found";
    header('Location: list.php');
    exit;
}

// Add error/success message display
if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
    echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['error']) . '</span>';
    echo '</div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">';
    echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['success']) . '</span>';
    echo '</div>';
    unset($_SESSION['success']);
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Patient Details</h2>
        <div class="space-x-4">
            <a href="edit.php?id=<?php echo $id; ?>" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Edit Patient
            </a>
            <a href="list.php" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Personal Information</h3>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($patientData['registration_number']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php echo htmlspecialchars($patientData['first_name'] . ' ' . $patientData['last_name']); ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($patientData['date_of_birth']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo ucfirst(htmlspecialchars($patientData['gender'])); ?></dd>
                </div>
            </dl>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($patientData['email']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($patientData['phone']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($patientData['address'])); ?></dd>
                </div>
            </dl>
        </div>

        <!-- Medical History -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Medical History</h3>
                <button onclick="showAddMedicalHistoryModal()" 
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Add Entry
                </button>
            </div>
            <div class="overflow-y-auto max-h-64">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Condition</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($patientData['medical_history'])): ?>
                            <?php foreach ($patientData['medical_history'] as $history): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($history['condition_name']); ?></td>
                                    <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($history['diagnosis_date']); ?></td>
                                    <td class="px-4 py-2 text-sm"><?php echo ucfirst(htmlspecialchars($history['status'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-sm text-gray-500 text-center">No medical history records</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Allergies -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Allergies</h3>
                <button onclick="showAddAllergyModal()" 
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Add Allergy
                </button>
            </div>
            <div class="overflow-y-auto max-h-64">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Allergen</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Severity</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Reaction</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $allergies = $patient->getAllergies($id);
                        if (!empty($allergies)): 
                            foreach ($allergies as $allergy): 
                        ?>
                            <tr>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($allergy['allergen']); ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($allergy['allergy_type']); ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $allergy['severity'] === 'severe' ? 'bg-red-100 text-red-800' : 
                                            ($allergy['severity'] === 'moderate' ? 'bg-yellow-100 text-yellow-800' : 
                                            'bg-green-100 text-green-800'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($allergy['severity'])); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($allergy['reaction']); ?></td>
                            </tr>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-sm text-gray-500 text-center">No allergies recorded</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Medications -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Medications</h3>
                <button onclick="showAddMedicationModal()" 
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Add Medication
                </button>
            </div>
            <div class="overflow-y-auto max-h-64">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Medication</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Dosage</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Frequency</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $medications = $patient->getMedications($id);
                        if (!empty($medications)): 
                            foreach ($medications as $medication): 
                        ?>
                            <tr>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($medication['medication_name']); ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $medication['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                            ($medication['status'] === 'discontinued' ? 'bg-red-100 text-red-800' : 
                                            'bg-gray-100 text-gray-800'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($medication['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <?php 
                                        echo htmlspecialchars($medication['start_date']) . ' to ' . 
                                             ($medication['end_date'] ?? 'Ongoing');
                                    ?>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-sm text-gray-500 text-center">No medications recorded</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Documents</h3>
                <button onclick="showUploadDocumentModal()" 
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Upload Document
                </button>
            </div>
            <div class="overflow-y-auto max-h-64">
                <ul class="divide-y divide-gray-200">
                    <?php if (!empty($patientData['documents'])): ?>
                        <?php foreach ($patientData['documents'] as $document): ?>
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($document['file_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($document['upload_date']); ?></p>
                                </div>
                                <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                                   class="text-blue-600 hover:text-blue-900"
                                   target="_blank">View</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="py-3 text-sm text-gray-500 text-center">No documents uploaded</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Medical Records -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Medical Records</h3>
                <div class="space-x-2">
                    <button onclick="showAddMedicalHistoryModal()" 
                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Add Entry
                    </button>
                    <a href="/pms/medical-records/views/create_prescription.php?patient_id=<?php echo $id; ?>"
                       class="px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700">
                        Create Prescription
                    </a>
                </div>
            </div>

            <script>
            // Initialize the first tab as active when the page loads
            document.addEventListener('DOMContentLoaded', function() {
                switchTab('history');
            });
            </script>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button onclick="switchTab('history')" 
                            class="tab-button active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Medical History
                    </button>
                    <button onclick="switchTab('prescriptions')" 
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Prescriptions
                    </button>
                </nav>
            </div>

            <!-- Medical History Tab -->
            <div id="historyTab" class="tab-content">
                <div class="overflow-y-auto max-h-64">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Condition</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($patientData['medical_history'])): ?>
                                <?php foreach ($patientData['medical_history'] as $history): ?>
                                    <tr>
                                        <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($history['condition_name']); ?></td>
                                        <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($history['diagnosis_date']); ?></td>
                                        <td class="px-4 py-2 text-sm"><?php echo ucfirst(htmlspecialchars($history['status'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm text-gray-500 text-center">No medical history records</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Prescriptions Tab -->
            <div id="prescriptionsTab" class="tab-content hidden">
                <div class="overflow-y-auto max-h-64">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Diagnosis</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Medications</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php 
                            $prescription = new Prescription($db);
                            $prescriptions = $prescription->getPatientPrescriptions($id);
                            if (!empty($prescriptions)): 
                                foreach ($prescriptions as $p): 
                            ?>
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                                        <?php echo date('d M Y', strtotime($p['prescription_date'])); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <div class="truncate max-w-xs">
                                            <?php echo htmlspecialchars($p['diagnosis']); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?php echo $p['medication_count']; ?> medications
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-blue-600">
                                        <a href="/pms/medical-records/views/view_prescription.php?id=<?php echo $p['id']; ?>">View</a>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-sm text-gray-500 text-center">No prescriptions found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Medical History Modal -->
<div id="medicalHistoryModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4">Add Medical History</h3>
            <form id="medicalHistoryForm" class="space-y-4">
                <input type="hidden" name="patient_id" value="<?php echo $id; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Condition</label>
                    <input type="text" name="condition_name" required
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Diagnosis Date</label>
                    <input type="date" name="diagnosis_date" required
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" required
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <option value="active">Active</option>
                        <option value="resolved">Resolved</option>
                        <option value="ongoing">Ongoing</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideModal('medicalHistoryModal')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Allergy Modal -->
<div id="allergyModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4">Add Allergy</h3>
            <form id="allergyForm" class="space-y-4">
                <input type="hidden" name="patient_id" value="<?php echo $id; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Allergy Type</label>
                    <select name="allergy_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="food">Food</option>
                        <option value="medication">Medication</option>
                        <option value="environmental">Environmental</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Allergen</label>
                    <input type="text" name="allergen" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Severity</label>
                    <select name="severity" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="mild">Mild</option>
                        <option value="moderate">Moderate</option>
                        <option value="severe">Severe</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reaction</label>
                    <textarea name="reaction" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideModal('allergyModal')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Medication Modal -->
<div id="medicationModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4">Add Medication</h3>
            <form id="medicationForm" class="space-y-4">
                <input type="hidden" name="patient_id" value="<?php echo $id; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Medication Name</label>
                    <input type="text" name="medication_name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dosage</label>
                    <input type="text" name="dosage" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Frequency</label>
                    <input type="text" name="frequency" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Prescribed By</label>
                    <input type="text" name="prescribed_by" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="active">Active</option>
                        <option value="discontinued">Discontinued</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideModal('medicationModal')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddMedicalHistoryModal() {
    document.getElementById('medicalHistoryModal').classList.remove('hidden');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

document.getElementById('medicalHistoryForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('../add_medical_history.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving the medical history');
    }
});

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Show selected tab content
    document.getElementById(tabName + 'Tab').classList.remove('hidden');
    
    // Update tab button styles
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Style active tab button
    event.currentTarget.classList.remove('border-transparent', 'text-gray-500');
    event.currentTarget.classList.add('border-blue-500', 'text-blue-600');
}
</script> 