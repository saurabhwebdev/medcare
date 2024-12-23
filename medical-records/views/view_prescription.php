<?php 
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /pms/auth/login.php');
    exit;
}

require_once '../../includes/db_connect.php';
require_once '../Prescription.php';

$id = $_GET['id'] ?? 0;
$prescription = new Prescription($db);
$prescriptionData = $prescription->getPrescription($id);

// Get clinic settings
$query = "SELECT * FROM clinic_settings WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$clinicSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescriptionData) {
    header('Location: /pms/patients/views/list.php');
    exit;
}

// Only include header for non-print view
if (!isset($_GET['print'])) {
    require_once '../../includes/header.php';
}

// Define these variables for use in the generatePDF function
$patientName = preg_replace("/[^a-zA-Z0-9]/", "_", $prescriptionData['patient_name']);
$patientId = $prescriptionData['registration_number'];
$prescriptionDate = date("Y-m-d", strtotime($prescriptionData['prescription_date']));
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// Pass PHP variables to JavaScript
const patientName = "<?php echo addslashes($patientName); ?>";
const patientId = "<?php echo addslashes($patientId); ?>";
const prescriptionDate = "<?php echo addslashes($prescriptionDate); ?>";
</script>

<?php if (!isset($_GET['print'])): ?>
<!-- Regular View -->
<div class="container mx-auto px-4 py-6 non-printable">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Prescription Details</h2>
        <button onclick="window.print()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Print Prescription
        </button>
    </div>

    <!-- Regular View Content -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Patient Information</h3>
                    <p class="text-gray-600">
                        Name: <?php echo htmlspecialchars($prescriptionData['patient_name']); ?><br>
                        ID: <?php echo htmlspecialchars($prescriptionData['registration_number']); ?>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-gray-600">
                        Date: <?php echo date('d M Y', strtotime($prescriptionData['prescription_date'])); ?><br>
                        <?php if ($prescriptionData['follow_up_date']): ?>
                            Follow-up: <?php echo date('d M Y', strtotime($prescriptionData['follow_up_date'])); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Diagnosis -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Diagnosis</h3>
            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($prescriptionData['diagnosis'])); ?></p>
        </div>

        <!-- Medications -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Prescribed Medications</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medication</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($prescriptionData['medications'] as $medication): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($medication['medication_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($medication['dosage']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($medication['frequency']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($medication['duration'] ?? 'As needed'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo htmlspecialchars($medication['instructions'] ?? '-'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Notes -->
        <?php if (!empty($prescriptionData['notes'])): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Additional Notes</h3>
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($prescriptionData['notes'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="/pms/medical-records/views/list_prescriptions.php?patient_id=<?php echo $prescriptionData['patient_id']; ?>"
               class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Back to List
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Print View -->
<div class="<?php echo isset($_GET['print']) ? '' : 'print-only'; ?>">
    <div class="prescription-container">
        <!-- Header with Clinic Info -->
        <div class="header">
            <div class="clinic-info">
                <?php if (!empty($clinicSettings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($clinicSettings['logo_path']); ?>" 
                         alt="Clinic Logo" class="clinic-logo">
                <?php endif; ?>
                <div class="clinic-details">
                    <h1 class="clinic-name">
                        <?php echo htmlspecialchars($clinicSettings['clinic_name'] ?? 'Default Clinic'); ?>
                    </h1>
                    <p class="doctor-name">
                        Dr. <?php echo htmlspecialchars($clinicSettings['doctor_name'] ?? 'Default Doctor'); ?>
                    </p>
                    <p class="qualifications">
                        <?php echo htmlspecialchars($clinicSettings['qualifications'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($clinicSettings['specializations'] ?? ''); ?>
                    </p>
                    <p class="registration">
                        <?php if (!empty($clinicSettings['license_number'])): ?>
                            License No: <?php echo htmlspecialchars($clinicSettings['license_number']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($clinicSettings['registration_number'])): ?>
                            Reg No: <?php echo htmlspecialchars($clinicSettings['registration_number']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="contact-info">
                <?php if (!empty($clinicSettings['address'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($clinicSettings['address'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($clinicSettings['city'])): ?>
                    <p><?php echo htmlspecialchars($clinicSettings['city']); ?> 
                    <?php if (!empty($clinicSettings['postal_code'])): ?>
                        - <?php echo htmlspecialchars($clinicSettings['postal_code']); ?>
                    <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($clinicSettings['phone'])): ?>
                    <p>Phone: <?php echo htmlspecialchars($clinicSettings['phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($clinicSettings['email'])): ?>
                    <p>Email: <?php echo htmlspecialchars($clinicSettings['email']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="prescription-content">
            <!-- Patient Info & Date -->
            <div class="patient-info">
                <div class="patient-details">
                    <h2>Patient Details:</h2>
                    <p>Name: <?php echo htmlspecialchars($prescriptionData['patient_name']); ?></p>
                    <p>ID: <?php echo htmlspecialchars($prescriptionData['registration_number']); ?></p>
                </div>
                <div class="prescription-date">
                    <p>Date: <?php echo date('d M Y', strtotime($prescriptionData['prescription_date'])); ?></p>
                    <?php if ($prescriptionData['follow_up_date']): ?>
                        <p>Follow-up: <?php echo date('d M Y', strtotime($prescriptionData['follow_up_date'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Diagnosis -->
            <div class="diagnosis-section">
                <h3>Diagnosis:</h3>
                <p><?php echo nl2br(htmlspecialchars($prescriptionData['diagnosis'])); ?></p>
            </div>

            <!-- Medications -->
            <div class="medications-section">
                <h3>Medications:</h3>
                <table class="medications-table">
                    <thead>
                        <tr>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptionData['medications'] as $medication): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medication['medication_name']); ?></td>
                                <td><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                <td><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                <td><?php echo htmlspecialchars($medication['duration'] ?? 'As needed'); ?></td>
                                <td><?php echo htmlspecialchars($medication['instructions'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Notes -->
            <?php if (!empty($prescriptionData['notes'])): ?>
                <div class="notes-section">
                    <h3>Additional Notes:</h3>
                    <p><?php echo nl2br(htmlspecialchars($prescriptionData['notes'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="prescription-footer">
                <div class="footer-text">
                    <?php if (!empty($clinicSettings['prescription_footer'])): ?>
                        <?php echo nl2br(htmlspecialchars($clinicSettings['prescription_footer'])); ?>
                    <?php endif; ?>
                </div>
                <div class="doctor-signature">
                    <?php if (!empty($clinicSettings['digital_signature_path'])): ?>
                        <img src="<?php echo htmlspecialchars($clinicSettings['digital_signature_path']); ?>" 
                             alt="Doctor's Signature" class="signature-image">
                    <?php endif; ?>
                    <p class="doctor-name">Dr. <?php echo htmlspecialchars($clinicSettings['doctor_name'] ?? 'Default Doctor'); ?></p>
                    <?php if (!empty($clinicSettings['registration_number'])): ?>
                        <p class="registration-number">Reg. No: <?php echo htmlspecialchars($clinicSettings['registration_number']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Watermark -->
        <div class="watermark">
            <?php echo htmlspecialchars($clinicSettings['clinic_name'] ?? 'PRESCRIPTION'); ?>
        </div>
    </div>
</div>

<!-- Action Buttons (Non-printable) -->
<div class="non-printable mt-8 mb-6 flex justify-center space-x-4">
    <button onclick="window.print()" 
            class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print Prescription
    </button>
    
    <button onclick="generatePDF()" 
            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        Save as PDF
    </button>
    
    <a href="/pms/medical-records/views/list_prescriptions.php?patient_id=<?php echo $prescriptionData['patient_id']; ?>"
       class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
        </svg>
        Back to List
    </a>
</div>

<style>
/* Print and PDF Styles */
.prescription-container {
    max-width: 210mm;
    margin: 0 auto;
    padding: 20mm;
    background: white;
    font-family: 'Courier Prime', monospace;
    line-height: 1.6;
    color: #2c3e50;
    position: relative;
}

.prescription-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 2px solid #3498db;
    pointer-events: none;
    margin: 10px;
}

.header {
    display: flex;
    justify-content: space-between;
    border-bottom: 2px solid #3498db;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.clinic-info {
    display: flex;
    gap: 20px;
}

.clinic-logo {
    max-width: 120px;
    height: auto;
}

.clinic-name {
    font-size: 28px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
    letter-spacing: -0.5px;
}

.doctor-name {
    font-size: 20px;
    color: #34495e;
    margin-bottom: 5px;
}

.qualifications {
    color: #7f8c8d;
    font-size: 14px;
    line-height: 1.4;
}

.contact-info {
    text-align: right;
    font-size: 14px;
    color: #7f8c8d;
}

.patient-info {
    display: flex;
    justify-content: space-between;
    margin: 30px 0;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.medications-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 20px 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 0 1px #e2e8f0;
}

.medications-table th {
    background: #3498db;
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: bold;
}

.medications-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.medications-table tr:last-child td {
    border-bottom: none;
}

.medications-table tr:nth-child(even) {
    background: #f8fafc;
}

.prescription-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 2px solid #3498db;
    display: flex;
    justify-content: space-between;
}

.footer-text {
    color: #7f8c8d;
    font-size: 14px;
    max-width: 60%;
}

.doctor-signature {
    text-align: right;
    padding: 20px;
    border-top: 2px solid #3498db;
    margin-top: 40px;
}

.signature-image {
    max-width: 150px;
    height: auto;
    margin-bottom: 10px;
}

.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 100px;
    opacity: 0.03;
    pointer-events: none;
    color: #2c3e50;
    white-space: nowrap;
}

/* Print-specific styles */
@media print {
    @page {
        size: A4;
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        background: white;
    }

    .non-printable {
        display: none !important;
    }

    .prescription-container {
        width: 210mm;
        min-height: 297mm;
        padding: 20mm;
        margin: 0;
        box-shadow: none;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }

    .medications-table th {
        background-color: #3498db !important;
        color: white !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}

/* Action buttons */
.action-buttons {
    position: fixed;
    bottom: 30px;
    right: 30px;
    display: flex;
    gap: 15px;
    z-index: 1000;
}

.action-buttons button,
.action-buttons a {
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.print-btn {
    background: #4CAF50;
    color: white;
    border: none;
}

.print-btn:hover {
    background: #45a049;
    transform: translateY(-2px);
}

.pdf-btn {
    background: #f44336;
    color: white;
    border: none;
}

.pdf-btn:hover {
    background: #e53935;
    transform: translateY(-2px);
}

.back-btn {
    background: #2196F3;
    color: white;
    text-decoration: none;
}

.back-btn:hover {
    background: #1e88e5;
    transform: translateY(-2px);
}

/* Loading indicator style */
.loading-indicator {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Make sure the prescription container is visible when generating PDF */
.prescription-container {
    background: white !important;
    margin: 0 auto;
    width: 210mm;
    position: relative;
    z-index: 1;
}

/* Ensure all content is visible */
.prescription-container * {
    visibility: visible !important;
}

/* Ensure proper printing of background colors */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

/* PDF Generation styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    .prescription-container,
    .prescription-container * {
        visibility: visible;
    }
    
    .prescription-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 210mm;
        padding: 20mm;
        margin: 0;
    }

    .non-printable {
        display: none !important;
    }
}

/* Ensure the prescription container is properly styled for PDF */
.prescription-container {
    background-color: white;
    padding: 20mm;
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    box-sizing: border-box;
    position: relative;
}

/* Ensure all elements inside are visible */
.prescription-container * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
</style>

<script>
async function generatePDF() {
    try {
        // Show loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loadingDiv.innerHTML = '<div class="bg-white p-4 rounded-lg">Generating PDF...</div>';
        document.body.appendChild(loadingDiv);

        // Get the print view container
        const element = document.querySelector('.prescription-container');
        
        // Configure PDF options
        const opt = {
            margin: [10, 10, 10, 10],
            filename: `Prescription_${patientName}_${patientId}_${prescriptionDate}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                useCORS: true,
                scrollY: 0,
                windowWidth: element.scrollWidth,
                windowHeight: element.scrollHeight,
                logging: false,
                removeContainer: true
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait',
                hotfixes: ["px_scaling"]
            }
        };

        // Generate PDF
        const pdf = await html2pdf().set(opt).from(element).toPdf().get('pdf');
        
        // Save the PDF
        pdf.save(opt.filename);

    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF. Please try again.');
    } finally {
        // Remove loading indicator
        const loadingDiv = document.querySelector('.fixed.inset-0');
        if (loadingDiv) {
            loadingDiv.remove();
        }
    }
}

// Hide header and footer when printing
if (window.matchMedia) {
    const mediaQueryList = window.matchMedia('print');
    mediaQueryList.addEventListener('change', function(mql) {
        if (mql.matches) {
            const header = document.querySelector('header');
            const footer = document.querySelector('footer');
            if (header) header.style.display = 'none';
            if (footer) footer.style.display = 'none';
        }
    });
}
</script> 