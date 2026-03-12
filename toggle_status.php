<?php
session_start();
include 'includes/db_connect.php';
include 'includes/sms_helper.php'; // 1. Include the SMS helper

// Security: Ensure only Admins can trigger this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id']) && isset($_GET['current'])) {
    $user_id = $conn->real_escape_string($_GET['id']);
    $current_status = $_GET['current'];
    
    // 2. Fetch User Details for the SMS (Name, Phone, Role)
    $user_query = "SELECT first_name, surname, phone_number, role FROM users WHERE id = '$user_id'";
    $user_result = $conn->query($user_query);
    $user_data = $user_result->fetch_assoc();
    
    $fullName = $user_data['first_name'] . " " . $user_data['surname'];
    $phone = $user_data['phone_number'];
    $role = $user_data['role'];

    // 3. Smart Toggle Logic
    if ($current_status == 'Pending') {
        $new_status = 'Active';
        $sms_msg = "Hello $fullName, your $role account at GoldByte CAMS has been VERIFIED and APPROVED. You can now log in to the system. - GB-CLINIC";
    } elseif ($current_status == 'Active') {
        $new_status = 'Suspended';
        $sms_msg = "Hello $fullName, your $role account at GoldByte CAMS has been SUSPENDED. Please contact clinical administration for details. - GB-CLINIC";
    } else {
        $new_status = 'Active';
        $sms_msg = "Hello $fullName, your $role account at GoldByte CAMS has been RE-ACTIVATED. You can now log in. - GB-CLINIC";
    }
    
    // 4. Update the Database
    $sql = "UPDATE users SET status = '$new_status' WHERE id = '$user_id'";
    
    if ($conn->query($sql)) {
        // 5. Trigger the SMS notification
        if (!empty($phone)) {
            sendGoldByteSMS($phone, $sms_msg);
        }

        // Redirect back to the dashboard tab they were on
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'doctors';
        header("Location: admin_dashboard.php?tab=$tab");
        exit();
    } else {
        echo "Error updating status: " . $conn->error;
    }
}
?>