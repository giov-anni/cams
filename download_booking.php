<?php
require 'vendor/autoload.php';
include 'includes/db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die("Appointment ID missing.");
}

$appt_id = $conn->real_escape_string($_GET['id']);

// Fetch detailed appointment info
$query = "SELECT a.*, u.first_name, u.surname, u.phone_number, s.name as specialty_name 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          JOIN specialties s ON a.specialty_id = s.id 
          WHERE a.id = '$appt_id'";
$res = $conn->query($query);
$data = $res->fetch_assoc();

if (!$data) {
    die("Appointment not found.");
}

// Setup Dompdf Options
$options = new Options();
$options->set('isRemoteEnabled', true); // Allows CSS/Images
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

// Design the HTML Template
$html = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 15px; }
        .clinic-name { color: #2563eb; font-size: 28px; font-weight: bold; margin: 0; }
        .receipt-label { text-transform: uppercase; letter-spacing: 2px; font-size: 12px; color: #64748b; margin-top: 5px; }
        
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; color: #2563eb; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }
        
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 8px 0; vertical-align: top; }
        .label { color: #64748b; width: 150px; font-size: 13px; }
        .value { color: #0f172a; font-weight: bold; font-size: 14px; }
        
        .status-badge { background: #eff6ff; color: #2563eb; padding: 5px 10px; border-radius: 4px; font-size: 12px; }
        .urgent { color: #ef4444; font-weight: bold; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    </style>
</head>
<body>

    <div class='header'>
        <div class='clinic-name'>GOLDBYTE CAMS</div>
        <div class='receipt-label'>Appointment Confirmation Slip</div>
    </div>

    <div class='section'>
        <div class='section-title'>Patient Information</div>
        <table class='info-grid'>
            <tr>
                <td class='label'>Patient Name:</td>
                <td class='value'>{$data['first_name']} {$data['surname']}</td>
            </tr>
            <tr>
                <td class='label'>Phone Number:</td>
                <td class='value'>{$data['phone_number']}</td>
            </tr>
            <tr>
                <td class='label'>Medical ID:</td>
                <td class='value'>#GB-P-" . str_pad($data['patient_id'], 4, '0', STR_PAD_LEFT) . "</td>
            </tr>
        </table>
    </div>

    <div class='section'>
        <div class='section-title'>Visit Details</div>
        <table class='info-grid'>
            <tr>
                <td class='label'>Appointment Ref:</td>
                <td class='value'>#GB-A-{$appt_id}</td>
            </tr>
            <tr>
                <td class='label'>Department:</td>
                <td class='value'>{$data['specialty_name']}</td>
            </tr>
            <tr>
                <td class='label'>Date & Time:</td>
                <td class='value'>" . date('F j, Y @ g:i A', strtotime($data['appointment_date'])) . "</td>
            </tr>
            <tr>
                <td class='label'>Service Type:</td>
                <td class='value'>{$data['service_type']}</td>
            </tr>
            <tr>
                <td class='label'>Emergency Level:</td>
                <td class='value'>" . ($data['is_emergency'] ? "<span class='urgent'>CRITICAL / URGENT</span>" : "Standard Consultation") . "</td>
            </tr>
        </table>
    </div>

    <div class='section'>
        <div class='section-title'>Symptoms Reported</div>
        <div style='background: #f8fafc; padding: 15px; border-radius: 8px; font-size: 13px; font-style: italic; color: #475569;'>
            \"" . (empty($data['symptoms']) ? 'No specific symptoms recorded.' : htmlspecialchars($data['symptoms'])) . "\"
        </div>
    </div>

    <div class='footer'>
        <p>This is a computer-generated document from GoldByte Clinical Systems.</p>
        <p>Location: Winneba, Central Region, Ghana | Contact: +233 (0) XX XXX XXXX</p>
        <p>Please present this slip at the front desk upon arrival.</p>
    </div>

</body>
</html>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the PDF to Browser
$dompdf->stream("GoldByte_Booking_{$appt_id}.pdf", array("Attachment" => 1));