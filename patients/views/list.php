<?php 
require_once '../../includes/header.php';
require_once '../../includes/db_connect.php';
require_once '../Patient.php';

$patient = new Patient($db);
$patients = $patient->getAllPatients();

// Check if we're selecting a patient for prescription
$action = $_GET['action'] ?? '';
$prescriptionLink = $action === 'create_prescription' 
    ? '/pms/medical-records/views/create_prescription.php?patient_id=' 
    : 'view.php?id=';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Patient List</h2>
        <a href="register.php" 
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Add New Patient
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Patients</label>
                <input type="text" id="searchInput" 
                       placeholder="Search by name or registration number..."
                       class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Gender</label>
                <select id="filterGender" 
                        class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                    <option value="">All Genders</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <button onclick="applyFilters()" 
                        class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Patient List Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Registration No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Age/Gender
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="patientTableBody">
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($patient['registration_number']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($patient['phone']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($patient['email']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php 
                                $birthDate = new DateTime($patient['date_of_birth']);
                                $today = new DateTime();
                                $age = $birthDate->diff($today)->y;
                                echo $age . ' yrs / ' . ucfirst($patient['gender']);
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?php echo $prescriptionLink . $patient['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">
                                <?php echo $action === 'create_prescription' ? 'Select' : 'View'; ?>
                            </a>
                            <a href="edit.php?id=<?php echo $patient['id']; ?>" 
                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const gender = document.getElementById('filterGender').value;
    
    // Add your filter logic here
    console.log('Filtering with:', { searchTerm, gender });
}
</script>

<?php include '../../includes/footer.php'; ?> 