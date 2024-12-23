<?php 
require_once '../../includes/header.php';
require_once '../../includes/db_connect.php';
require_once '../Appointment.php';
require_once '../../patients/Patient.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pms/auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$appointment = new Appointment($db);
$patient = new Patient($db);

// Get clinic settings to get clinic_id
$stmt = $db->prepare("SELECT id FROM clinic_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$clinicSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$clinicSettings) {
    echo "<div class='container mx-auto px-4 py-6'><div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4'>Clinic settings not found.</div></div>";
    exit();
}

// Get appointment details with patient information
$query = "SELECT a.*, 
                 p.first_name, p.last_name, p.registration_number,
                 p.phone as patient_phone, p.email as patient_email
          FROM appointments a
          JOIN patients p ON a.patient_id = p.id
          WHERE a.id = ? AND a.clinic_id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$_GET['id'], $clinicSettings['id']]);
$appointmentDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointmentDetails) {
    echo "<div class='container mx-auto px-4 py-6'><div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4'>Appointment not found.</div></div>";
    exit();
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Appointment Details</h2>
        <div class="space-x-2">
            <a href="list.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="switchTab('details')" 
                        class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-tab="details">
                    Appointment Details
                </button>
                <button onclick="switchTab('history')" 
                        class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-tab="history">
                    Appointment History
                </button>
                <button onclick="switchTab('notes')" 
                        class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-tab="notes">
                    Notes & Updates
                </button>
            </nav>
        </div>

        <!-- Details Tab -->
        <div id="details-tab" class="tab-content space-y-6">
            <!-- Quick Status Card -->
            <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-600">Status</div>
                        <div class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $statusColors[$appointmentDetails['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($appointmentDetails['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Date & Time</div>
                        <div class="mt-1 font-medium">
                            <?php echo date('M j, Y', strtotime($appointmentDetails['appointment_date'])); ?>
                            at <?php echo date('h:i A', strtotime($appointmentDetails['appointment_time'])); ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Type</div>
                        <div class="mt-1 font-medium"><?php echo ucfirst($appointmentDetails['appointment_type']); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Duration</div>
                        <div class="mt-1 font-medium"><?php echo $appointmentDetails['duration']; ?> minutes</div>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Patient Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($appointmentDetails['first_name'] . ' ' . $appointmentDetails['last_name']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Registration Number</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($appointmentDetails['registration_number']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($appointmentDetails['patient_phone']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($appointmentDetails['patient_email']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Tab -->
        <div id="history-tab" class="tab-content hidden">
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Appointment History</h3>
                <?php
                // Get appointment history for this patient
                $historyQuery = "SELECT 
                    a.*, 
                    DATE_FORMAT(a.appointment_date, '%Y-%m-%d') as formatted_date,
                    DATE_FORMAT(a.appointment_time, '%h:%i %p') as formatted_time
                    FROM appointments a
                    WHERE a.patient_id = ? AND a.clinic_id = ?
                    ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                $historyStmt = $db->prepare($historyQuery);
                $historyStmt->execute([$appointmentDetails['patient_id'], $clinicSettings['id']]);
                $appointmentHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="relative timeline-container">
                    <!-- Timeline Track -->
                    <div class="absolute w-full h-1 bg-gray-200 top-5"></div>
                    
                    <!-- Timeline Items -->
                    <div class="relative flex justify-between items-start timeline-items">
                        <?php 
                        $count = count($appointmentHistory);
                        foreach ($appointmentHistory as $index => $history): 
                            $isCurrentAppointment = $history['id'] == $_GET['id'];
                            $statusColors = [
                                'scheduled' => ['dot' => 'bg-yellow-500', 'text' => 'text-yellow-800'],
                                'confirmed' => ['dot' => 'bg-green-500', 'text' => 'text-green-800'],
                                'completed' => ['dot' => 'bg-blue-500', 'text' => 'text-blue-800'],
                                'cancelled' => ['dot' => 'bg-red-500', 'text' => 'text-red-800'],
                                'no_show' => ['dot' => 'bg-gray-500', 'text' => 'text-gray-800']
                            ];
                            $colorClass = $statusColors[$history['status']] ?? ['dot' => 'bg-gray-500', 'text' => 'text-gray-800'];
                        ?>
                            <div class="relative flex-1 text-center">
                                <!-- Timeline Dot -->
                                <div class="<?php echo $isCurrentAppointment ? 'w-4 h-4 ring-4 ring-blue-200' : 'w-3 h-3'; ?> 
                                            <?php echo $colorClass['dot']; ?> rounded-full mx-auto mb-2">
                                </div>
                                
                                <!-- Date -->
                                <div class="text-sm font-medium mb-1">
                                    <?php echo date('M j, Y', strtotime($history['formatted_date'])); ?>
                                </div>
                                
                                <!-- Time -->
                                <div class="text-sm text-gray-600 mb-1">
                                    <?php echo $history['formatted_time']; ?>
                                </div>
                                
                                <!-- Status -->
                                <div class="<?php echo $colorClass['text']; ?> text-sm font-medium">
                                    <?php echo ucfirst($history['status']); ?>
                                </div>
                                
                                <!-- Type -->
                                <div class="text-sm text-gray-600 mt-1">
                                    <?php echo ucfirst($history['appointment_type']); ?>
                                </div>
                                
                                <?php if (!empty($history['notes'])): ?>
                                    <!-- Notes Tooltip -->
                                    <div class="group relative">
                                        <div class="cursor-help text-sm text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-info-circle"></i> Notes
                                        </div>
                                        <div class="hidden group-hover:block absolute z-10 w-48 p-2 mt-1 text-sm 
                                                    text-left text-gray-700 bg-white rounded-lg shadow-lg 
                                                    -translate-x-1/2 left-1/2">
                                            <?php echo htmlspecialchars($history['notes']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Tab -->
        <div id="notes-tab" class="tab-content hidden">
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-semibold mb-4">Notes & Updates</h3>
                <!-- Notes Form -->
                <form id="notesForm" class="mb-6">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointmentDetails['id']; ?>">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Add Note</label>
                        <textarea name="notes" rows="3" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter appointment notes..."><?php echo htmlspecialchars($appointmentDetails['notes']); ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Save Notes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 pt-4 mt-6 border-t border-gray-200">
            <button onclick="window.history.back()" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Back
            </button>
            <button onclick="updateStatus('<?php echo $appointmentDetails['id']; ?>')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Update Status
            </button>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Show selected tab content
    document.getElementById(`${tabName}-tab`).classList.remove('hidden');
    
    // Update tab button styles
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Highlight active tab
    const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
    activeBtn.classList.add('border-blue-500', 'text-blue-600');
}

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    switchTab('details');
});

async function updateStatus(appointmentId) {
    const newStatus = prompt('Enter new status (scheduled, confirmed, completed, cancelled, no_show):');
    if (!newStatus) return;

    const validStatuses = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'];
    if (!validStatuses.includes(newStatus.toLowerCase())) {
        alert('Invalid status. Please enter one of: scheduled, confirmed, completed, cancelled, no_show');
        return;
    }

    try {
        const response = await fetch('../update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${appointmentId}&status=${newStatus}`
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert('Status updated successfully');
            location.reload();
        } else {
            alert(result.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
    }
}

// Handle notes form submission
document.getElementById('notesForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    try {
        const formData = new FormData(e.target);
        const response = await fetch('../update_notes.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            alert('Notes updated successfully');
        } else {
            alert(result.message || 'Failed to update notes');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating notes');
    }
});
</script>

<?php include '../../includes/footer.php'; ?> 