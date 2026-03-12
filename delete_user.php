<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY: Only Admins can delete
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id'])) {
    $user_id = $conn->real_escape_string($_GET['id']);
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'doctors';

    // Start Transaction
    $conn->begin_transaction();

    try {
        // Step A: If Doctor, delete from doctors table first
        $conn->query("DELETE FROM doctors WHERE user_id = '$user_id'");

        // Step B: Optional - Delete appointments related to this user 
        // (Uncomment if you want to wipe their medical history too)
        // $conn->query("DELETE FROM appointments WHERE patient_id = '$user_id' OR specialty_id IN (SELECT specialty_id FROM doctors WHERE user_id = '$user_id')");

        // Step C: Delete the user from the users table
        $conn->query("DELETE FROM users WHERE id = '$user_id'");

        $conn->commit();
        header("Location: admin_dashboard.php?tab=$tab&msg=deleted");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error deleting user: " . $e->getMessage());
    }
}
?>