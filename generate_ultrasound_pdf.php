<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error.log');
ini_set('memory_limit', '256M');

ob_start();

session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Midwife'], true)) {
    header("Location: ../login.php");
    exit();
}

require '../connections/connections.php';
require 'pdf_utilities.php';

$pdo = connection();

if (!isset($_GET['case_id']) || !isset($_GET['transaction_id'])) {
    $_SESSION['error'] = "Invalid ultrasound report.";
    header("Location: manage_health_records.php");
    exit();
}

$case_id = $_GET['case_id'];
$transaction_id = (int)$_GET['transaction_id'];

$query = "SELECT 
            tv.*,
            CONCAT(p.first_name, ' ', COALESCE(p.middle_name, ''), ' ', p.last_name) AS patient_name,
            p.address,
            p.date_of_birth,
            p.contact_number,
            p.case_id,
            TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age
          FROM tv_ultrasound tv
          JOIN patients p ON tv.case_id = p.case_id
          WHERE tv.case_id = :case_id AND tv.transaction_id = :transaction_id";

$stmt = $pdo->prepare($query);
$stmt->execute([':case_id' => $case_id, ':transaction_id' => $transaction_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "Ultrasound report not found.";
    header("Location: manage_health_records.php");
    exit();
}

// Load logo
$logo_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'psc_greenbanner.png';
$logo_base64 = loadLogoAsBase64($logo_path);

// Create PDF
$pdf = new PaanakanPDF($logo_base64);
$pdf->SetTitle('Transvaginal Ultrasound Report');
$pdf->AddPage();

// Build PDF content
ob_end_clean();

$pdf->SetFont('dejavusans', '', 10);

// Patient header
addPatientHeader($pdf, $record, 'TRANSVAGINAL ULTRASOUND REPORT');

// Exam Details
addFormTable($pdf, [
    'Report Date' => date('F j, Y', strtotime($record['date'])),
    'Referred By' => $record['referred_by'] ?? 'N/A',
    'LMP' => $record['lmp'] ? date('F j, Y', strtotime($record['lmp'])) : 'N/A',
    'Gravidity (G)' => $record['g'] ?? 'N/A',
    'Parity (P)' => $record['p'] ?? 'N/A',
], 2, 'EXAM DETAILS');

// Uterus
addFormTable($pdf, [
    'Measurements (cms)' => $record['uterus_measurement'] ?? 'N/A',
    'Position' => $record['uterus_position'] ?? 'N/A',
], 2, 'I. UTERUS');
addTextSection($pdf, 'Abnormalities Noted', $record['uterus_abnormalities'] ?? 'None');

// Endometrium
addFormTable($pdf, [
    'Thickness' => $record['endometrium_thickness'] ?? 'N/A',
    'Type' => $record['endometrium_type'] ?? 'N/A',
    'Menstrual Phase' => $record['menstrual_phase'] ?? 'N/A',
], 2, 'II. ENDOMETRIUM');
addTextSection($pdf, 'Abnormalities Noted', $record['endometrium_abnormalities'] ?? 'None');

// Adnexae - Right Ovary
addSectionHeader($pdf, 'III. ADNEXAE - RIGHT OVARY');
addFormTable($pdf, [
    'Measurements (cm)' => $record['right_ovary_measurements'] ?? 'N/A',
    'Location' => $record['right_ovary_location'] ?? 'N/A',
    'Dominant Follicle (cm)' => $record['right_ovary_follicle'] ?? 'N/A',
], 2);
addTextSection($pdf, 'Abnormalities', $record['right_ovary_abnormalities'] ?? 'None');

// Adnexae - Left Ovary
addSectionHeader($pdf, 'III. ADNEXAE - LEFT OVARY');
addFormTable($pdf, [
    'Measurements (cm)' => $record['left_ovary_measurements'] ?? 'N/A',
    'Location' => $record['left_ovary_location'] ?? 'N/A',
    'Dominant Follicle (cm)' => $record['left_ovary_follicle'] ?? 'N/A',
], 2);
addTextSection($pdf, 'Abnormalities', $record['left_ovary_abnormalities'] ?? 'None');

// Cervix
addFormTable($pdf, [
    'Measurements (cms)' => $record['cervix_measurements'] ?? 'N/A',
    'Nabothian Cyst' => $record['nabothian_cyst'] ?? 'None',
], 2, 'IV. CERVIX');

// Others
addTextSection($pdf, 'V. OTHERS', $record['others'] ?? '');

// Diagnosis
addTextSection($pdf, 'DIAGNOSIS', $record['diagnosis'] ?? '');

// Signature Section
addSignatureSection($pdf, '', 'Prepared By (Radiologist/Sonographer)');

// Generate filename
$filename = 'Ultrasound_Report_' . $record['case_id'] . '_' . str_replace(' ', '_', $record['patient_name']) . '_' . date('Ymd', strtotime($record['date'])) . '.pdf';

$pdf->Output($filename, 'I');
?>
