<?php 
include 'includes/header.php';
require_once 'includes/db_connect.php';
require_once 'appointments/Appointment.php';
require_once 'patients/Patient.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pms/auth/login.php');
    exit();
}

// Initialize classes
$appointment = new Appointment($db);
$patient = new Patient($db);

// Get total patients count
$totalPatients = $patient->getTotalPatients();

// Get today's date
$today = date('Y-m-d');

// Get today's appointments
$todayAppointments = $appointment->getAppointments(['start_date' => $today, 'end_date' => $today]);
$todayAppointmentsCount = count($todayAppointments);

// Get upcoming appointments (next 7 days)
$nextWeek = date('Y-m-d', strtotime('+7 days'));
$upcomingAppointments = $appointment->getAppointments([
    'start_date' => date('Y-m-d', strtotime('+1 day')),
    'end_date' => $nextWeek
]);

// Get appointment statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
        COUNT(CASE WHEN status = 'no_show' THEN 1 END) as no_show
    FROM appointments 
    WHERE clinic_id = (SELECT id FROM clinic_settings WHERE user_id = ?)
    AND appointment_date BETWEEN ? AND ?
");
$stmt->execute([$_SESSION['user_id'], date('Y-m-01'), date('Y-m-t')]);
$monthlyStats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>
                <p class="mt-4 text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Total Patients -->
                    <div class="bg-blue-50 overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users text-blue-600 text-3xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Patients
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php echo $totalPatients; ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-blue-100 px-5 py-3">
                            <a href="/pms/patients/views/list.php" class="text-sm text-blue-700 hover:text-blue-900">
                                View all patients →
                            </a>
                        </div>
                    </div>

                    <!-- Today's Appointments -->
                    <div class="bg-green-50 overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-calendar-check text-green-600 text-3xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Today's Appointments
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php echo $todayAppointmentsCount; ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-100 px-5 py-3">
                            <a href="/pms/appointments/views/list.php" class="text-sm text-green-700 hover:text-green-900">
                                View all appointments →
                            </a>
                        </div>
                    </div>

                    <!-- Monthly Overview -->
                    <div class="bg-yellow-50 overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-bar text-yellow-600 text-3xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            This Month's Appointments
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php echo array_sum($monthlyStats); ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-yellow-100 px-5 py-3">
                            <div class="text-sm text-yellow-700">
                                <?php echo $monthlyStats['completed']; ?> completed
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment Statistics -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Appointment Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-sm font-medium text-gray-500">Scheduled</div>
                            <div class="mt-1 text-2xl font-semibold text-yellow-600">
                                <?php echo $monthlyStats['scheduled']; ?>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-sm font-medium text-gray-500">Confirmed</div>
                            <div class="mt-1 text-2xl font-semibold text-green-600">
                                <?php echo $monthlyStats['confirmed']; ?>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-sm font-medium text-gray-500">Completed</div>
                            <div class="mt-1 text-2xl font-semibold text-blue-600">
                                <?php echo $monthlyStats['completed']; ?>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-sm font-medium text-gray-500">Cancelled</div>
                            <div class="mt-1 text-2xl font-semibold text-red-600">
                                <?php echo $monthlyStats['cancelled']; ?>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-sm font-medium text-gray-500">No Show</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-600">
                                <?php echo $monthlyStats['no_show']; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Appointments</h3>
                    <div class="bg-white shadow overflow-hidden rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach (array_slice($upcomingAppointments, 0, 5) as $apt): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo date('M j, Y', strtotime($apt['appointment_date'])); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($apt['registration_number']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                       <?php echo $apt['appointment_type'] === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                                <?php echo ucfirst($apt['appointment_type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                       <?php echo getStatusColor($apt['status']); ?>">
                                                <?php echo ucfirst($apt['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="/pms/appointments/views/view.php?id=<?php echo $apt['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($upcomingAppointments)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No upcoming appointments
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    return match($status) {
        'scheduled' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'no_show' => 'bg-gray-100 text-gray-800',
        default => 'bg-gray-100 text-gray-800'
    };
}
?>

<?php include 'includes/footer.php'; ?> 