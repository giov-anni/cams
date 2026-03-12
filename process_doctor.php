<?php
// 1. Connect to the database and SMS helper
include 'includes/db_connect.php';
include 'includes/sms_helper.php'; // Added SMS helper

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Catch and sanitize User Data
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $gender = $conn->real_escape_string($_POST['gender']);
    
    // Passwords must match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Error: Passwords do not match. <a href='add_doctor.php'>Go back</a>");
    }
    
    // Hash the password securely
    $password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 3. Catch Doctor-Specific Data
    $specialty_id = $_POST['specialty_id'];
    $license_number = $conn->real_escape_string($_POST['license_number']);
    $bio = $conn->real_escape_string($_POST['bio']);

    // 4. Handle the CV PDF Upload Securely
    $cv_path = "";
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $allowed_ext = array("pdf" => "application/pdf");
        $file_name = $_FILES["cv_file"]["name"];
        $file_type = $_FILES["cv_file"]["type"];
        $file_size = $_FILES["cv_file"]["size"];
    
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!array_key_exists(strtolower($ext), $allowed_ext)) {
            die("Error: Please select a valid PDF file format.");
        }
    
        $maxsize = 5 * 1024 * 1024;
        if ($file_size > $maxsize) {
            die("Error: File size is larger than the allowed limit (5MB).");
        }
    
        $new_file_name = uniqid() . "_" . basename($file_name);
        $upload_dir = "uploads/cvs/";
        
        $cv_path = $upload_dir . $new_file_name;
        
        if (!move_uploaded_file($_FILES["cv_file"]["tmp_name"], $cv_path)) {
            die("Error: There was a problem uploading your CV. Please make sure the 'uploads/cvs/' folder exists.");
        }
    } else {
        die("Error: CV File is required.");
    }

    // 5. Start a Database Transaction
    $conn->begin_transaction();

    try {
        // Step A: Insert into 'users' table 
        // NOTE: status is explicitly set to 'Pending'
        $sql_user = "INSERT INTO users (first_name, surname, email, password, gender, phone_number, role, status) 
                     VALUES ('$first_name', '$surname', '$email', '$password_hashed', '$gender', '$phone', 'Doctor', 'Pending')";
        
        if (!$conn->query($sql_user)) {
            throw new Exception("Error creating user account: " . $conn->error);
        }

        // Get the ID of the user we just created
        $new_doctor_user_id = $conn->insert_id;

        // Step B: Insert professional details into 'doctors' table
        $sql_doc = "INSERT INTO doctors (user_id, specialty_id, license_number, cv_path, bio) 
                    VALUES ('$new_doctor_user_id', '$specialty_id', '$license_number', '$cv_path', '$bio')";
        
        if (!$conn->query($sql_doc)) {
            throw new Exception("Error saving professional details: " . $conn->error);
        }

        // Everything worked!
        $conn->commit();

        // 6. SEND SMS NOTIFICATION
        $fullName = $first_name . " " . $surname;
        $sms_msg = "Hello Dr. $fullName, thank you for joining GoldByte CAMS. Your license ($license_number) is currently under review by our Admin team. We will notify you once verified. - GB-CLINIC";
        
        sendGoldByteSMS($phone, $sms_msg);
        
        // Success Message (Custom Styled)
        echo "<div style='text-align:center; margin-top:100px; font-family: sans-serif; padding: 20px;'>";
        echo "<div style='font-size: 5rem; margin-bottom: 20px;'>⏳</div>";
        echo "<h2 style='color: #0f172a;'>Application Submitted Successfully!</h2>";
        echo "<p style='color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 20px auto; line-height: 1.6;'>";
        echo "Thank you, Dr. $surname. Your credentials and medical license ($license_number) are now being reviewed by the GoldByte Administration team. ";
        echo "An SMS confirmation has been sent to <strong>$phone</strong>. You will be able to log in once your account has been verified.";
        echo "</p>";
        echo "<a href='index.php' style='display: inline-block; background: #0f172a; color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: 600;'>Return to Homepage</a>";
        echo "</div>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
    
    $conn->close();
} else {
    header("Location: add_doctor.php");
    exit();
}
?>