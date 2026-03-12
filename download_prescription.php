<?php
require 'vendor/autoload.php';
include 'includes/db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die("Access Denied.");
}

$appt_id = $conn->real_escape_string($_GET['id']);

// Fetch Consultation & Doctor Details (Linking Doctors to Users to get the Name)
$query = "SELECT a.*, 
                 u_p.first_name, u_p.surname, u_p.phone_number, 
                 s.name as specialty_name, 
                 d.license_number,
                 u_d.first_name as doc_first, u_d.surname as doc_sur
          FROM appointments a 
          JOIN users u_p ON a.patient_id = u_p.id 
          JOIN specialties s ON a.specialty_id = s.id 
          JOIN doctors d ON a.specialty_id = d.specialty_id
          JOIN users u_d ON d.user_id = u_d.id
          WHERE a.id = '$appt_id' AND a.status = 'Completed'";

$res = $conn->query($query);
$data = $res->fetch_assoc();

if (!$data) {
    die("Prescription not found or consultation not yet finalized.");
}

// Construct Doctor Name
$doctor_full_name = "DR. " . strtoupper($data['doc_first'] . " " . $data['doc_sur']);

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; padding: 10px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0f172a; padding-bottom: 15px; }
        .clinic-name { color: #0f172a; font-size: 28px; font-weight: bold; margin: 0; }
        .receipt-label { text-transform: uppercase; letter-spacing: 2px; font-size: 12px; color: #64748b; margin-top: 5px; }
        
        .section { margin-bottom: 25px; }
        .section-title { font-size: 13px; color: #0f172a; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }
        
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 6px 0; vertical-align: top; }
        .label { color: #64748b; width: 140px; font-size: 12px; }
        .value { color: #0f172a; font-weight: bold; font-size: 13px; }
        
        .rx-container { position: relative; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; min-height: 350px; background: #fff; }
        .rx-symbol { font-size: 45px; font-weight: bold; color: #0f172a; font-family: 'Times New Roman', serif; margin-bottom: 10px; }
        .rx-content { font-size: 15px; line-height: 1.8; white-space: pre-wrap; color: #1e293b; padding-left: 10px; }

        .footer-table { width: 100%; margin-top: 40px; }
        .signature-area { text-align: center; width: 220px; }
        .signature-line { border-top: 2px solid #0f172a; margin-top: 45px; padding-top: 5px; font-size: 11px; font-weight: bold; }
        .stamp-box { border: 2px dashed #cbd5e1; width: 110px; height: 110px; text-align: center; color: #cbd5e1; line-height: 110px; font-size: 9px; float: right; }
        
        .footer-note { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    </style>
</head>
<body>

    <div class='header'>
        <div class='clinic-name'>GOLDBYTE CLINICAL CENTER</div>
        <div class='receipt-label'>OFFICIAL MEDICAL PRESCRIPTION</div>
    </div>

    <table style='width: 100%; margin-bottom: 20px;'>
        <tr>
            <td style='width: 50%;'>
                <div class='section-title'>Patient Details</div>
                <table class='info-grid'>
                    <tr><td class='label'>Name:</td><td class='value'>{$data['first_name']} {$data['surname']}</td></tr>
                    <tr><td class='label'>ID:</td><td class='value'>#GB-P-" . str_pad($data['patient_id'], 4, '0', STR_PAD_LEFT) . "</td></tr>
                </table>
            </td>
            <td style='width: 50%; padding-left: 40px;'>
                <div class='section-title'>Consultation Info</div>
                <table class='info-grid'>
                    <tr><td class='label'>Date:</td><td class='value'>" . date('F j, Y', strtotime($data['appointment_date'])) . "</td></tr>
                    <tr><td class='label'>Ref No:</td><td class='value'>#GB-RX-{$appt_id}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class='section'>
        <div class='section-title'>Prescription (℞)</div>
        <div class='rx-container'>
            <div class='rx-symbol'>℞</div>
            <div class='rx-content'>" . nl2br(htmlspecialchars($data['prescriptions'])) . "</div>
        </div>
    </div>

    <table class='footer-table'>
        <tr>
            <td class='signature-area'>
                <div class='signature-line'>
                    $doctor_full_name <br>
                    <span style='font-weight:normal; font-size:9px;'>License: {$data['license_number']}</span>
                </div>
            </td>
            <td>
                <div class='stamp-box'>CLINIC STAMP</div>
            </td>
        </tr>
    </table>

    <div class='footer-note'>
        <p>This prescription is valid for 30 days from the date of issue. Please consult a pharmacist for medication guidance.</p>
        <p>Winneba, Central Region, Ghana | Secure Digital Document ID: " . md5($appt_id . $data['patient_id']) . "</p>
    </div>

</body>
</html>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("GoldByte_Prescription_{$appt_id}.pdf", array("Attachment" => 1));