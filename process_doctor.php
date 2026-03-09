<?php
// 1. Connect to the database
include 'includes/db_connect.php';

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
    
        // Verify file extension
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!array_key_exists(strtolower($ext), $allowed_ext)) {
            die("Error: Please select a valid PDF file format.");
        }
    
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($file_size > $maxsize) {
            die("Error: File size is larger than the allowed limit (5MB).");
        }
    
        // Create a unique file name so two doctors named John don't overwrite each other's CVs
        $new_file_name = uniqid() . "_" . basename($file_name);
        $upload_dir = "uploads/cvs/";
        
        // Final path to save in the database
        $cv_path = $upload_dir . $new_file_name;
        
        // Move the file from the temporary server space to your specific folder
        if (!move_uploaded_file($_FILES["cv_file"]["tmp_name"], $cv_path)) {
            die("Error: There was a problem uploading your CV. Please make sure the 'uploads/cvs/' folder exists.");
        }
    } else {
        die("Error: CV File is required.");
    }

    // 5. Start a Database Transaction
    $conn->begin_transaction();

    try {
        // Step A: Insert the Doctor into the 'users' table with the 'Doctor' role
        $sql_user = "INSERT INTO users (first_name, surname, email, password, gender, phone_number, role) 
                     VALUES ('$first_name', '$surname', '$email', '$password_hashed', '$gender', '$phone', 'Doctor')";
        
        if (!$conn->query($sql_user)) {
            throw new Exception("Error creating user account: " . $conn->error);
        }

        // Get the ID of the user we just created
        $new_doctor_user_id = $conn->insert_id;

        // Step B: Insert the Professional details into the 'doctors' table
        $sql_doc = "INSERT INTO doctors (user_id, specialty_id, license_number, cv_path, bio) 
                    VALUES ('$new_doctor_user_id', '$specialty_id', '$license_number', '$cv_path', '$bio')";
        
        if (!$conn->query($sql_doc)) {
            throw new Exception("Error saving professional details: " . $conn->error);
        }

        // Everything worked! Commit to the database.
        $conn->commit();
        
        // Success Message & Redirect
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2>👨‍⚕️ Application Received!</h2>";
        echo "<p>Thank you for applying to join the GoldByte CAMS medical network in Winneba.</p>";
        echo "<p>Your CV has been successfully securely uploaded. Our administration team will review your MDC License ($license_number) shortly.</p>";
        echo "<p><a href='index.php' style='color:#2563eb; text-decoration:none;'>Return to Home Page</a></p>";
        echo "</div>";

    } catch (Exception $e) {
        // Something failed! Roll back the database.
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
    
    // Close the connection
    $conn->close();
} else {
    // If someone tries to access this file directly without clicking submit
    header("Location: add_doctor.php");
    exit();
}
?>