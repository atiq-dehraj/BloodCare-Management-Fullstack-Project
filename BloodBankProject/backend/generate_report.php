<?php
session_start();
require_once 'db_connect.php';
require_once 'mailer.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    die("Unauthorized Access.");
}

$user_id = $_SESSION['user_id'];

// 1. Fetch Donor Information
$stmt = $conn->prepare("
    SELECT d.donor_id, d.name, d.blood_type, u.email 
    FROM donors d 
    JOIN users u ON d.user_id = u.user_id 
    WHERE d.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();
$stmt->close();

$date_printed = date("d-M-Y h:i A");
$acc_no = "BC-" . str_pad($donor['donor_id'], 6, "0", STR_PAD_LEFT);

// 2. Build the HTML Template
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 10px; }
        .logo-text { color: #e74c3c; font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .title { font-weight: bold; font-size: 14px; color: #000; }
        .divider { border-bottom: 2px solid #000; margin: 10px 0; }
        .meta-table { width: 100%; font-size: 10px; margin-bottom: 20px; }
        .meta-table td { padding: 3px; }
        .section-title { font-weight: bold; color: #556B2F; text-transform: uppercase; margin: 15px 0 5px 0; font-size: 11px; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .data-table th { border-bottom: 1px solid #000; text-align: left; padding: 5px; color: #556B2F; font-weight: bold;}
        .data-table td { padding: 5px; border-bottom: 1px dashed #ccc; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-text">BloodCare Donation</div>
        <div class="title">DEPARTMENT OF HAEMATOLOGY</div>
        <div>123 Health Avenue, Central District<br>TEL: 555-0198 FAX: 555-0199</div>
    </div>
    
    <div class="divider"></div>
    <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">HAEMATOLOGY REPORT</div>

    <table class="meta-table">
        <tr>
            <td>ACC NO: ' . $acc_no . '</td>
            <td>REPORT PRINTED ON: ' . $date_printed . '</td>
        </tr>
        <tr>
            <td>PATIENT NAME: <strong>' . strtoupper($donor['name']) . '</strong></td>
            <td>DONOR ID: #' . $donor['donor_id'] . '</td>
        </tr>
        <tr>
            <td>BLOOD GROUP: <strong style="color:red; font-size:12px;">' . $donor['blood_type'] . '</strong></td>
            <td>STATUS: ACTIVE DONOR</td>
        </tr>
    </table>

    <div class="section-title">DONATION SCREENING PROFILE</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Test</th>
                <th>Result</th>
                <th>Units</th>
                <th>Status</th>
                <th>Normal Range</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>BLOOD TYPING</strong></td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td>ABO Group</td>
                <td>' . substr($donor['blood_type'], 0, -1) . '</td>
                <td>-</td>
                <td>N</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Rh(D) Factor</td>
                <td>' . (strpos($donor['blood_type'], '+') !== false ? 'Positive' : 'Negative') . '</td>
                <td>-</td>
                <td>N</td>
                <td>-</td>
            </tr>
            <tr><td colspan="5" style="border:none;">&nbsp;</td></tr>
            <tr>
                <td><strong>HAEMOGLOBIN (Hb)</strong></td>
                <td>14.20</td>
                <td>g/dl</td>
                <td>N</td>
                <td>13-18</td>
            </tr>
            <tr>
                <td><strong>INFECTIOUS DISEASE SCREEN</strong></td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td>HIV 1 & 2 Antibodies</td>
                <td>Negative</td>
                <td>Index</td>
                <td>N</td>
                <td>Negative</td>
            </tr>
            <tr>
                <td>Hepatitis B Surface Antigen</td>
                <td>Negative</td>
                <td>Index</td>
                <td>N</td>
                <td>Negative</td>
            </tr>
            <tr>
                <td>Syphilis (VDRL)</td>
                <td>Non-Reactive</td>
                <td>-</td>
                <td>N</td>
                <td>Non-Reactive</td>
            </tr>
        </tbody>
    </table>
    
    <br><br>
    <div class="divider"></div>
    <p style="text-align: center; font-size: 9px; font-style: italic;">This is an electronically generated official BloodCare report. Thank you for saving lives.</p>

</body>
</html>';

// 3. Configure and Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 4. Generate the PDF into memory
$pdf_output = $dompdf->output();

// 5. Email the PDF directly from memory!
$subject = "Your BloodCare Medical Report";
$body = "<h2>Hello " . $donor['name'] . ",</h2><p>Thank you for your life-saving donation. Please find your official Haematology and Blood Typing report attached to this email.</p>";

// Notice we pass $pdf_output directly to the mailer!
sendBloodCareEmail($donor['email'], $donor['name'], $subject, $body, $pdf_output, 'BloodCare_Report.pdf');

// 6. Force browser to download the PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="BloodCare_Report.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
echo $pdf_output;
exit();
?>