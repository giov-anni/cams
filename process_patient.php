<?php
/**
 * GoldByte CAMS - Patient Registration & Booking Process
 * Handled with Transactional Integrity and Arkesel SMS Integration.
 */

// 1. DYNAMIC PATH RESOLUTION
// This ensures the script finds your includes regardless of XAMPP's slash direction.
$include_path = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

if (file_exists($include_path . 'db_connect.php')) {
    include $include_path . 'db_connect.php';
} else {
    die("❌ Error: 'db_connect.php' is missing from the includes folder.");
}

if (file_exists($include_path . 'sms_helper.php')) {
    include $include_path . 'sms_helper.php';
} else {
    die("❌ Error: 'sms_helper.php' is missing from the includes folder. Please check the spelling!");
}

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

    // 4. DUPLICATE EMAIL CHECK (Prevents database crashes)
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check_email->num_rows > 0) {
        die("Error: This email is already registered. Please <a href='login.php'>Login</a>.");
    }
    
    // Hash the password
    $password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 5. Catch Appointment Data
    $specialty_id = $_POST['specialty_id'];
    $appointment_date = $_POST['appointment_date'];
    $service_type = $_POST['service_type'];
    
    // Fetch specialty name for the SMS context
    $spec_query = $conn->query("SELECT name FROM specialties WHERE id = '$specialty_id'");
    $spec_data = $spec_query->fetch_assoc();
    $specialty_name = $spec_data['name'] ?? "General Consultation";

    // 6. SECURE FEE CALCULATION
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

    // 7. Start a Database Transaction 
    $conn->begin_transaction();

    try {
        // Step A: Insert User as 'Active' so they can login immediately
        $sql_user = "INSERT INTO users (first_name, surname, email, password, gender, phone_number, role, status) 
                     VALUES ('$first_name', '$surname', '$email', '$password_hashed', '$gender', '$phone', 'Patient', 'Active')";
        
        if (!$conn->query($sql_user)) {
            throw new Exception("Error creating user account: " . $conn->error);
        }

        $new_patient_id = $conn->insert_id;

        // Step B: Insert the Appointment linked to the new Patient ID
        $sql_appt = "INSERT INTO appointments (patient_id, specialty_id, appointment_date, service_type, home_address, is_emergency, total_fee) 
                     VALUES ('$new_patient_id', '$specialty_id', '$appointment_date', '$service_type', '$home_address', '$is_emergency', '$total_fee')";
        
        if (!$conn->query($sql_appt)) {
            throw new Exception("Error booking appointment: " . $conn->error);
        }

        // Commit all changes if both queries worked
        $conn->commit();

        // 8. SEND SMS NOTIFICATION via Arkesel
        $fullName = $first_name . " " . $surname;
        $formatted_date = date("M j, Y @ g:i A", strtotime($appointment_date));
        
        $sms_msg = "Hello $fullName, welcome to GoldByte CAMS! Your account is now ACTIVE. Your appointment for $specialty_name on $formatted_date is received and pending doctor review. - GB-CLINIC";
        
        if ($is_emergency == 1) {
            $sms_msg = "🚨 EMERGENCY: Hello $fullName, your account is ACTIVE and your URGENT request for $specialty_name has been received. Proceed to the clinic now. - GB-CLINIC";
        }

        // Trigger SMS
        sendGoldByteSMS($phone, $sms_msg);
        
        // Success View
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<div style='font-size: 4rem; margin-bottom: 1rem;'>🎉</div>";
        echo "<h2>Account Created Successfully!</h2>";
        echo "<p>Welcome to GoldByte CAMS. A confirmation SMS has been sent to <strong>$phone</strong>.</p>";
        echo "<p style='background: #f1f5f9; display: inline-block; padding: 10px 20px; border-radius: 10px; border: 1px solid #e2e8f0;'>Total Fee Due: <strong>$total_fee GH₵</strong></p>";
        echo "<p style='margin-top: 20px;'><a href='login.php' style='background:#2563eb; color:white; padding:12px 25px; text-decoration:none; border-radius:8px; font-weight:bold;'>Click here to Login & View Dashboard</a></p>";
        echo "</div>";

    } catch (Exception $e) {
        // If anything fails, undo everything to keep DB clean
        $conn->rollback();
        echo "<div style='color: #ef4444; padding: 20px; font-family: sans-serif;'>";
        echo "<h3>⚠️ System Error</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<p><a href='add.php'>Go back and try again</a></p>";
        echo "</div>";
    }
    
    $conn->close();
} else {
    // Redirect if they try to access this file directly via URL
    header("Location: add.php");
    exit();
}
?>