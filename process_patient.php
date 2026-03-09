<?php
// 1. Connect to the database
include 'includes/db_connect.php';

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
    
    // 5. SECURE FEE CALCULATION (Backend logic for Winneba operations)
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
    // (If the appointment fails, we don't want a "ghost" user without an appointment)
    $conn->begin_transaction();

    try {
        // Step A: Insert the User into the 'users' table
        $sql_user = "INSERT INTO users (first_name, surname, email, password, gender, phone_number, role) 
                     VALUES ('$first_name', '$surname', '$email', '$password_hashed', '$gender', '$phone', 'Patient')";
        
        if (!$conn->query($sql_user)) {
            throw new Exception("Error creating user account: " . $conn->error);
        }

        // Get the ID of the user we just created!
        $new_patient_id = $conn->insert_id;

        // Step B: Insert the Appointment into the 'appointments' table
        $sql_appt = "INSERT INTO appointments (patient_id, specialty_id, appointment_date, service_type, home_address, is_emergency, total_fee) 
                     VALUES ('$new_patient_id', '$specialty_id', '$appointment_date', '$service_type', '$home_address', '$is_emergency', '$total_fee')";
        
        if (!$conn->query($sql_appt)) {
            throw new Exception("Error booking appointment: " . $conn->error);
        }

        // Everything worked! Commit to the database.
        $conn->commit();
        
        // Success Message & Redirect
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2>🎉 Success! Welcome to GoldByte CAMS!</h2>";
        echo "<p>Your appointment has been booked. Your total fee is <strong>$total_fee GH₵</strong>.</p>";
        echo "<p><a href='view.php' style='color:#2563eb; text-decoration:none;'>Click here to view your appointments</a></p>";
        echo "</div>";

    } catch (Exception $e) {
        // Something failed! Roll back the database so no partial data is saved.
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
    
    // Close the connection
    $conn->close();
} else {
    // If someone tries to access this file directly without clicking submit
    header("Location: add.php");
    exit();
}
?>