<?php 
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Medical Records Management</h2>
        <p class="text-gray-600">Manage prescriptions, medical history, and patient documents</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Prescriptions Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold">Prescriptions</h3>
                        <p class="text-gray-600">Manage patient prescriptions</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4">
                <a href="/pms/patients/views/list.php" class="text-blue-600 hover:text-blue-800">
                    Select patient to view prescriptions →
                </a>
            </div>
        </div>

        <!-- Medical History Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold">Medical History</h3>
                        <p class="text-gray-600">View and update patient medical history</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4">
                <a href="/pms/patients/views/list.php" class="text-blue-600 hover:text-blue-800">
                    Select patient to view history →
                </a>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold">Documents</h3>
                        <p class="text-gray-600">Manage patient documents and reports</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4">
                <a href="/pms/patients/views/list.php" class="text-blue-600 hover:text-blue-800">
                    Select patient to view documents →
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Add your recent activity data here -->
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No recent activity
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div> 