<?php
session_start();
include 'includes/db_connect.php';
include 'includes/sms_helper.php'; // 1. Include SMS Helper

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape inputs for security
    $patient_id = $_SESSION['user_id'];
    $specialty_id = $conn->real_escape_string($_POST['specialty_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;
    $home_address = isset($_POST['home_address']) ? $conn->real_escape_string($_POST['home_address']) : NULL;

    // 2. Fetch Patient & Specialty Info for the SMS
    $info_query = "SELECT u.first_name, u.surname, u.phone_number, s.name as specialty_name 
                   FROM users u, specialties s 
                   WHERE u.id = '$patient_id' AND s.id = '$specialty_id'";
    $info_res = $conn->query($info_query);
    $data = $info_res->fetch_assoc();
    
    $fullName = $data['first_name'] . " " . $data['surname'];
    $patientPhone = $data['phone_number'];
    $specialtyName = $data['specialty_name'];

    // Logic: Calculate Fee on server side
    $total_fee = 100.00;
    if ($service_type === 'Home-Service') $total_fee += 100.00;
    if ($is_emergency) $total_fee += 200.00;

    $sql = "INSERT INTO appointments (patient_id, specialty_id, appointment_date, service_type, home_address, is_emergency, total_fee, status) 
            VALUES ('$patient_id', '$specialty_id', '$appointment_date', '$service_type', '$home_address', '$is_emergency', '$total_fee', 'Pending')";

    if ($conn->query($sql)) {
        
        // 3. Prepare SMS Messages
        $formatted_date = date("M j, g:i A", strtotime($appointment_date));
        
        // Patient Message
        $patient_msg = "Hi $fullName, your booking for $specialtyName on $formatted_date has been received and is PENDING approval. - GB-CLINIC";
        
        if ($is_emergency) {
            $patient_msg = "🚨 URGENT: $fullName, your Emergency request for $specialtyName has been flagged. Please proceed to the clinic immediately. - GB-CLINIC";
        }

        // 4. Send SMS to Patient
        sendGoldByteSMS($patientPhone, $patient_msg);

        // 5. Notify Doctors of the Specialty (Alert)
        $doc_query = "SELECT u.phone_number FROM users u JOIN doctors d ON u.id = d.user_id WHERE d.specialty_id = '$specialty_id'";
        $doc_res = $conn->query($doc_query);
        
        $alert_msg = "GB-CLINIC: A new patient ($fullName) has been added to your queue for $formatted_date.";
        if($is_emergency) {
            $alert_msg = "🚨 EMERGENCY ALERT: An urgent case ($fullName) has been assigned to your department queue!";
        }

        while($doc = $doc_res->fetch_assoc()) {
            sendGoldByteSMS($doc['phone_number'], $alert_msg);
        }

        // Redirect back to dashboard
        header("Location: patient_dashboard.php?msg=booked");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
} else {
    header("Location: book.php");
    exit();
}
?>