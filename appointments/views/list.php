<?php 
require_once '../../includes/header.php';
require_once '../../includes/db_connect.php';
require_once '../Appointment.php';

$appointment = new Appointment($db);

// Get clinic settings for working hours
$stmt = $db->prepare("SELECT working_hours FROM clinic_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$clinicSettings = $stmt->fetch(PDO::FETCH_ASSOC);
$workingHours = json_decode($clinicSettings['working_hours'] ?? '{}', true);

// Get filter parameters
$selectedDate = isset($_GET['date']) ? new DateTime($_GET['date']) : new DateTime();
$weekStart = (clone $selectedDate)->modify('monday this week');
$weekEnd = (clone $selectedDate)->modify('sunday this week');

$filters = [
    'start_date' => $weekStart->format('Y-m-d'),
    'end_date' => $weekEnd->format('Y-m-d'),
    'status' => $_GET['status'] ?? ''
];

$appointments = $appointment->getAppointments($filters);

// Organize appointments by date and time
$appointmentsByDate = [];
foreach ($appointments as $apt) {
    $date = $apt['appointment_date'];
    if (!isset($appointmentsByDate[$date])) {
        $appointmentsByDate[$date] = [];
    }
    $appointmentsByDate[$date][] = $apt;
}

// Function to check if a day is an off day
function isOffDay($date, $workingHours) {
    $dayName = strtolower(date('l', strtotime($date)));
    return !isset($workingHours[$dayName]) || 
           !$workingHours[$dayName]['enabled'] || 
           empty($workingHours[$dayName]['start']) || 
           empty($workingHours[$dayName]['end']);
}
?>

<div id="notification" class="fixed top-4 right-4 z-50 transform transition-transform duration-300 translate-x-full">
    <div class="bg-white border-l-4 border-green-500 shadow-lg rounded-lg p-4 max-w-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p id="notification-message" class="text-sm text-gray-700"></p>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Appointments</h2>
        <div class="flex items-center space-x-4">
            <div class="flex rounded-lg shadow-sm">
                <button onclick="toggleView('week')" 
                        id="weekViewBtn"
                        class="px-4 py-2 text-sm font-medium rounded-l-lg border">
                    <i class="fas fa-calendar-week mr-2"></i>Week
                </button>
                <button onclick="toggleView('list')" 
                        id="listViewBtn"
                        class="px-4 py-2 text-sm font-medium rounded-r-lg border-t border-r border-b">
                    <i class="fas fa-list mr-2"></i>List
                </button>
            </div>
            <a href="schedule.php" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Schedule Appointment
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Week</label>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="changeWeek('prev')"
                            class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <input type="date" name="date" 
                           value="<?php echo $selectedDate->format('Y-m-d'); ?>"
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    <button type="button" onclick="changeWeek('next')"
                            class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status"
                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    <option value="">All Status</option>
                    <option value="scheduled" <?php echo $filters['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="confirmed" <?php echo $filters['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="no_show" <?php echo $filters['status'] === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-200">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Week View Container -->
    <div id="weekView" class="view-container">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Week Header -->
            <div class="grid grid-cols-7 bg-gray-50 border-b">
                <?php
                $currentDay = clone $weekStart;
                for ($i = 0; $i < 7; $i++): 
                    $currentDate = $currentDay->format('Y-m-d');
                    $isToday = $currentDate === (new DateTime())->format('Y-m-d');
                    $isOff = isOffDay($currentDate, $workingHours);
                ?>
                    <div class="p-4 border-r <?php echo $isToday ? 'bg-blue-50' : ''; ?> 
                             <?php echo $isOff ? 'bg-gray-200' : ''; ?>">
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo $currentDay->format('D'); ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?php echo $currentDay->format('M j'); ?>
                        </div>
                        <?php if ($isOff): ?>
                            <div class="text-xs text-red-600 mt-1">Off Day</div>
                        <?php endif; ?>
                    </div>
                <?php 
                    $currentDay->modify('+1 day');
                endfor; 
                ?>
            </div>

            <!-- Appointments Grid -->
            <div class="grid grid-cols-7">
                <?php
                $currentDay = clone $weekStart;
                for ($i = 0; $i < 7; $i++):
                    $currentDate = $currentDay->format('Y-m-d');
                    $dayAppointments = $appointmentsByDate[$currentDate] ?? [];
                ?>
                    <div class="min-h-[200px] p-4 border-r border-b">
                        <?php if (empty($dayAppointments)): ?>
                            <div class="text-sm text-gray-500 text-center py-4">No appointments</div>
                        <?php else: ?>
                            <?php foreach ($dayAppointments as $apt): ?>
                                <div class="mb-3 p-2 rounded-md shadow-sm border 
                                          <?php echo getStatusColor($apt['status']); ?>">
                                    <div class="text-sm font-medium">
                                        <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                                    </div>
                                    <div class="text-sm">
                                        <?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?>
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        <?php echo ucfirst($apt['appointment_type']); ?>
                                    </div>
                                    <div class="mt-1 flex justify-between items-center">
                                        <span class="text-xs font-medium 
                                                  <?php echo getStatusTextColor($apt['status']); ?>">
                                            <?php echo ucfirst($apt['status']); ?>
                                        </span>
                                        <a href="view.php?id=<?php echo $apt['id']; ?>" 
                                           class="text-xs text-blue-600 hover:text-blue-800">
                                            View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php 
                    $currentDay->modify('+1 day');
                endfor; 
                ?>
            </div>
        </div>
    </div>

    <!-- List View Container -->
    <div id="listView" class="view-container hidden">
        <div class="bg-white rounded-lg shadow overflow-hidden">
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
                    <?php foreach ($appointments as $apt): 
                        $isOff = isOffDay($apt['appointment_date'], $workingHours);
                    ?>
                        <tr class="<?php echo $isOff ? 'bg-gray-100' : ''; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($apt['appointment_date'])); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                                </div>
                                <?php if ($isOff): ?>
                                    <div class="text-xs text-red-600 mt-1">Off Day</div>
                                <?php endif; ?>
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
                                <select onchange="updateStatus(<?php echo $apt['id']; ?>, this.value)"
                                        class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <?php foreach (['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status): ?>
                                        <option value="<?php echo $status; ?>" 
                                                <?php echo $apt['status'] === $status ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="view.php?id=<?php echo $apt['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const messageEl = document.getElementById('notification-message');
    const notificationBorder = notification.querySelector('div');
    const icon = notification.querySelector('svg');
    
    // Set message
    messageEl.textContent = message;
    
    // Set colors based on type
    if (type === 'success') {
        notificationBorder.classList.remove('border-red-500');
        notificationBorder.classList.add('border-green-500');
        icon.classList.remove('text-red-500');
        icon.classList.add('text-green-500');
    } else {
        notificationBorder.classList.remove('border-green-500');
        notificationBorder.classList.add('border-red-500');
        icon.classList.remove('text-green-500');
        icon.classList.add('text-red-500');
    }
    
    // Show notification
    notification.classList.remove('translate-x-full');
    
    // Hide after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
    }, 3000);
}

async function updateStatus(id, status) {
    try {
        const response = await fetch('../update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&status=${status}`
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showNotification('Status updated successfully', 'success');
        } else {
            showNotification(result.message || 'Failed to update status', 'error');
            // Revert the select to the previous value
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred while updating the status', 'error');
        // Revert the select to the previous value
        location.reload();
    }
}

function changeWeek(direction) {
    const dateInput = document.querySelector('input[name="date"]');
    const currentDate = new Date(dateInput.value);
    
    if (direction === 'prev') {
        currentDate.setDate(currentDate.getDate() - 7);
    } else {
        currentDate.setDate(currentDate.getDate() + 7);
    }
    
    dateInput.value = currentDate.toISOString().split('T')[0];
    dateInput.form.submit();
}

// Helper function to get status background color
function getStatusColor(status) {
    const colors = {
        'scheduled': 'bg-yellow-50',
        'confirmed': 'bg-green-50',
        'completed': 'bg-blue-50',
        'cancelled': 'bg-red-50',
        'no_show': 'bg-gray-50'
    };
    return colors[status] || 'bg-gray-50';
}

// Helper function to get status text color
function getStatusTextColor(status) {
    const colors = {
        'scheduled': 'text-yellow-800',
        'confirmed': 'text-green-800',
        'completed': 'text-blue-800',
        'cancelled': 'text-red-800',
        'no_show': 'text-gray-800'
    };
    return colors[status] || 'text-gray-800';
}

// Define toggleView function before it's used
function toggleView(view) {
    const weekBtn = document.getElementById('weekViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    const weekView = document.getElementById('weekView');
    const listView = document.getElementById('listView');

    if (view === 'week') {
        weekBtn.classList.add('bg-gray-100', 'text-gray-900');
        weekBtn.classList.remove('text-gray-500');
        listBtn.classList.remove('bg-gray-100', 'text-gray-900');
        listBtn.classList.add('text-gray-500');
        weekView.classList.remove('hidden');
        listView.classList.add('hidden');
    } else {
        listBtn.classList.add('bg-gray-100', 'text-gray-900');
        listBtn.classList.remove('text-gray-500');
        weekBtn.classList.remove('bg-gray-100', 'text-gray-900');
        weekBtn.classList.add('text-gray-500');
        listView.classList.remove('hidden');
        weekView.classList.add('hidden');
    }

    localStorage.setItem('preferredView', view);
}

// Initialize view when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const preferredView = localStorage.getItem('preferredView') || 'week';
    toggleView(preferredView);
});
</script>

<style>
/* Add these styles to ensure smooth transitions */
.view-container {
    transition: opacity 0.2s ease-in-out;
}

/* Style the toggle buttons */
#weekViewBtn, #listViewBtn {
    transition: all 0.2s ease-in-out;
}

#weekViewBtn:hover, #listViewBtn:hover {
    background-color: #f3f4f6;
}

/* Style for off days */
.off-day {
    background-color: #f3f4f6;
    position: relative;
}

.off-day::after {
    content: 'Off Day';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #dc2626;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Add these styles for the notification */
#notification {
    transition: all 0.3s ease-in-out;
}

#notification.translate-x-full {
    transform: translateX(100%);
}

#notification:not(.translate-x-full) {
    transform: translateX(0);
}
</style>

<?php
// Helper functions for PHP
function getStatusColor($status) {
    $colors = [
        'scheduled' => 'bg-yellow-50',
        'confirmed' => 'bg-green-50',
        'completed' => 'bg-blue-50',
        'cancelled' => 'bg-red-50',
        'no_show' => 'bg-gray-50'
    ];
    return $colors[$status] ?? 'bg-gray-50';
}

function getStatusTextColor($status) {
    $colors = [
        'scheduled' => 'text-yellow-800',
        'confirmed' => 'text-green-800',
        'completed' => 'text-blue-800',
        'cancelled' => 'text-red-800',
        'no_show' => 'text-gray-800'
    ];
    return $colors[$status] ?? 'text-gray-800';
}
?> 