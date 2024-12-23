<?php 
require_once '../../includes/header.php';
require_once '../../includes/db_connect.php';
require_once '../Prescription.php';
require_once '../../patients/Patient.php';

$patientId = $_GET['patient_id'] ?? 0;

$patient = new Patient($db);
$patientData = $patient->getPatient($patientId);

if (!$patientData) {
    header('Location: /pms/patients/views/list.php');
    exit;
}

$prescription = new Prescription($db);
$prescriptions = $prescription->getPatientPrescriptions($patientId);
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold">Prescriptions</h2>
            <p class="text-gray-600">
                Patient: <?php echo htmlspecialchars($patientData['first_name'] . ' ' . $patientData['last_name']); ?>
                (<?php echo htmlspecialchars($patientData['registration_number']); ?>)
            </p>
        </div>
        <div class="space-x-4">
            <a href="create_prescription.php?patient_id=<?php echo $patientId; ?>"
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                New Prescription
            </a>
            <a href="/pms/patients/views/view.php?id=<?php echo $patientId; ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Back to Patient
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diagnosis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medications</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Follow-up</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($prescriptions)): ?>
                    <?php foreach ($prescriptions as $p): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo date('d M Y', strtotime($p['prescription_date'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 truncate max-w-xs">
                                    <?php echo htmlspecialchars($p['diagnosis']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo $p['medication_count']; ?> medications
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($p['follow_up_date']): ?>
                                    <?php echo date('d M Y', strtotime($p['follow_up_date'])); ?>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="view_prescription.php?id=<?php echo $p['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No prescriptions found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 