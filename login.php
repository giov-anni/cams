<?php
// 1. Start the Session FIRST (Crucial for keeping users logged in)
session_start();
include 'includes/db_connect.php';

$error_message = "";

// 2. Process the Login Form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Search for the user by email
    $sql = "SELECT id, first_name, surname, password, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // 3. Verify the hashed password
        if (password_verify($password, $user['password'])) {
            
            // Success! Create the Session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['surname'];

            // 4. Smart Redirect based on Role
            if ($user['role'] == 'Patient') {
                header("Location: patient_dashboard.php");
            } elseif ($user['role'] == 'Doctor') {
                header("Location: doctor_dashboard.php"); // We will build this next!
            } else {
                header("Location: index.php"); // Fallback for Admin (for now)
            }
            exit();
            
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "No account found with that email address.";
    }
}

// Include the header AFTER the PHP logic so redirects work properly
include 'includes/header.php'; 
?>

<div class="form-wrapper" style="max-width: 500px; margin-top: 5rem;">
    <div class="form-header">
        <h2>Welcome Back</h2>
        <p>Log in to access your GoldByte CAMS dashboard.</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 1.5rem; text-align: center; font-weight: 500;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter your registered email">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Secure Login</button>
    </form>

    <div style="text-align: center; margin-top: 2rem;">
        <p style="color: #64748b;">Don't have an account?</p>
        <a href="add.php" style="color: #2563eb; font-weight: 600; text-decoration: none;">Register as a Patient</a> | 
        <a href="add_doctor.php" style="color: #2563eb; font-weight: 600; text-decoration: none;">Apply as a Doctor</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>