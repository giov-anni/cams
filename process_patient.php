<?php
// 1. Connect to the database and SMS helper
include 'includes/db_connect.php';
include 'includes/sms_helper.php'; // Added SMS helper

// 2. Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Catch and sanitize User Data
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $gender = $conn->real_escape_string($_POST['gender']);
    
    // Passwords must match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Error: Passwords do not match. <a href='add.php'>Go back</a>");
    }
    
    // Hash the password securely!
    $password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 4. Catch Appointment Data
    $specialty_id = $_POST['specialty_id'];
    $appointment_date = $_POST['appointment_date'];
    $service_type = $_POST['service_type'];
    
    // Fetch specialty name for the SMS message
    $spec_query = $conn->query("SELECT name FROM specialties WHERE id = '$specialty_id'");
    $spec_data = $spec_query->fetch_assoc();
    $specialty_name = $spec_data['name'] ?? "General Consultation";

    // 5. SECURE FEE CALCULATION
    $total_fee = 100.00; // Base fee
    $home_address = NULL;
    
    if ($service_type === 'Home-Service') {
        $total_fee += 100.00;
        $home_address = $conn->real_escape_string($_POST['home_address']);
    }
    
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;
    if ($is_emergency == 1) {
        $total_fee += 200.00;
    }

    // 6. Start a Database Transaction 
    $conn->begin_transaction();

    try {
        // Step A: Insert the User into the 'users' table
        $sql_user = "INSERT INTO users (first_name, surname, email, password, gender, phone_number, role) 
                     VALUES ('$first_name', '$surname', '$email', '$password_hashed', '$gender', '$phone', 'Patient')";
        
        if (!$conn->query($sql_user)) {
            throw new Exception("Error creating user account: " . $conn->error);
        }

        $new_patient_id = $conn->insert_id;

        // Step B: Insert the Appointment into the 'appointments' table
        $sql_appt = "INSERT INTO appointments (patient_id, specialty_id, appointment_date, service_type, home_address, is_emergency, total_fee) 
                     VALUES ('$new_patient_id', '$specialty_id', '$appointment_date', '$service_type', '$home_address', '$is_emergency', '$total_fee')";
        
        if (!$conn->query($sql_appt)) {
            throw new Exception("Error booking appointment: " . $conn->error);
        }

        // Everything worked! Commit to the database.
        $conn->commit();

        // 7. SEND SMS NOTIFICATION
        $fullName = $first_name . " " . $surname;
        $formatted_date = date("M j, Y @ g:i A", strtotime($appointment_date));
        
        $sms_msg = "Hello $fullName, welcome to GoldByte CAMS! Your account is active and your appointment for $specialty_name on $formatted_date is PENDING approval. - GB-CLINIC";
        
        if ($is_emergency == 1) {
            $sms_msg = "🚨 EMERGENCY ALERT: Hello $fullName, your URGENT request for $specialty_name has been received. Please arrive at the clinic immediately. - GB-CLINIC";
        }

        sendGoldByteSMS($phone, $sms_msg);
        
        // Success Message & Redirect
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2>🎉 Success! Welcome to GoldByte CAMS!</h2>";
        echo "<p>An SMS confirmation has been sent to <strong>$phone</strong>.</p>";
        echo "<p>Your total fee is <strong>$total_fee GH₵</strong>.</p>";
        echo "<p><a href='login.php' style='color:#2563eb; text-decoration:none; font-weight:bold;'>Click here to Login & View Dashboard</a></p>";
        echo "</div>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
    
    $conn->close();
} else {
    header("Location: add.php");
    exit();
}
?>