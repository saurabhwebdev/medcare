<?php 
// Check if session is not already started before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Patient Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/antd/dist/antd.css" rel="stylesheet">
    <script src="https://unpkg.com/antd/dist/antd.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="/pms/index.php" class="text-2xl font-bold text-blue-600">MedCare</a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Main Navigation -->
                        <div class="hidden md:flex space-x-6">
                            <a href="/pms/dashboard.php" 
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                Dashboard
                            </a>
                            
                            <!-- Patients Dropdown -->
                            <div class="relative group">
                                <button class="text-gray-700 group-hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                    <span>Patients</span>
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out">
                                    <div class="py-1">
                                        <a href="/pms/patients/views/list.php" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            All Patients
                                        </a>
                                        <a href="/pms/patients/views/register.php" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Add New Patient
                                        </a>
                                        <a href="/pms/appointments/views/list.php" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            All Appointments
                                        </a>
                                        <a href="/pms/appointments/views/schedule.php" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Schedule Appointment
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Prescriptions -->
                            <a href="/pms/patients/views/list.php?action=create_prescription" 
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                Create Prescription
                            </a>

                            <!-- Billing -->
                            <a href="/pms/billing/index.php" 
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                Billing
                            </a>

                            <!-- Reports -->
                            <a href="/pms/reports/index.php" 
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                Reports
                            </a>

                            <!-- Profile -->
                            <a href="/pms/profile/index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Profile</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- User Menu Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center text-gray-700 hover:text-blue-600">
                                <span class="text-sm font-medium mr-2">My Account</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out">
                                <div class="py-1">
                                    <a href="/pms/settings/index.php" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Settings
                                    </a>
                                    <a href="/pms/profile/index.php" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profile
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="/pms/auth/logout.php" 
                                       class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/pms/auth/login.php" class="text-blue-600">Login</a>
                        <a href="/pms/auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
            <button type="button" class="mobile-menu-button p-2 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="/pms/dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Dashboard</a>
                
                <!-- Mobile Patients Menu -->
                <div class="space-y-1">
                    <button class="mobile-submenu-button w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                        Patients
                        <svg class="float-right h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="mobile-submenu hidden pl-4">
                        <a href="/pms/patients/views/list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">All Patients</a>
                        <a href="/pms/patients/views/register.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Add New Patient</a>
                        <a href="/pms/appointments/views/list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">All Appointments</a>
                        <a href="/pms/appointments/views/schedule.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Schedule Appointment</a>
                    </div>
                </div>

                <a href="/pms/patients/views/list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Patients</a>
                <a href="/pms/billing/index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Billing</a>
                <a href="/pms/reports/index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Reports</a>
                <a href="/pms/settings/index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Settings</a>
                <a href="/pms/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:text-red-800 hover:bg-gray-50">Logout</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });

        // Mobile submenu toggle
        document.querySelectorAll('.mobile-submenu-button').forEach(button => {
            button.addEventListener('click', function() {
                this.nextElementSibling.classList.toggle('hidden');
                const svg = this.querySelector('svg');
                svg.classList.toggle('rotate-180');
            });
        });
    </script>
</body>
</html> 