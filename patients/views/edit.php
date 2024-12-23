<?php 
require_once '../../includes/header.php';
require_once '../Patient.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;
$patient = new Patient($db);
$patientData = $patient->getPatient($id);

if (!$patientData) {
    header('Location: list.php');
    exit;
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Edit Patient</h2>
            <a href="view.php?id=<?php echo $id; ?>" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Back to Details
            </a>
        </div>
        
        <form id="patientEditForm" class="space-y-6">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <!-- Personal Information -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" required
                               value="<?php echo htmlspecialchars($patientData['first_name']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" required
                               value="<?php echo htmlspecialchars($patientData['last_name']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <!-- Add other fields similar to register.php, but with values from $patientData -->
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="window.history.back()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('patientEditForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('../update_patient.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert('Patient information updated successfully');
            window.location.href = `view.php?id=${formData.get('id')}`;
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the patient information');
    }
});
</script> 