<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape inputs for security
    $patient_id = $_SESSION['user_id'];
    $specialty_id = $conn->real_escape_string($_POST['specialty_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;

    // Logic: Calculate Fee on server side to prevent tampering
    $total_fee = 100.00;
    if ($service_type === 'Home-Service') $total_fee += 100.00;
    if ($is_emergency) $total_fee += 200.00;

    $sql = "INSERT INTO appointments (patient_id, specialty_id, appointment_date, service_type, is_emergency, total_fee, status) 
            VALUES ('$patient_id', '$specialty_id', '$appointment_date', '$service_type', '$is_emergency', '$total_fee', 'Pending')";

    if ($conn->query($sql)) {
        // Redirect back to dashboard with a success message
        header("Location: patient_dashboard.php?msg=booked");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
} else {
    // If someone tries to access this file without submitting the form
    header("Location: book.php");
    exit();
}
?>