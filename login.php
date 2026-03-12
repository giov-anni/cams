<?php
// 1. Start the Session FIRST
session_start();
include 'includes/db_connect.php';

$error_message = "";

// 2. Process the Login Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Added 'status' to the query
    $sql = "SELECT id, first_name, surname, password, role, status FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // A. Check for Suspension
        if ($user['status'] == 'Suspended') {
            $error_message = "This account has been suspended. Please contact the administrator.";
        } 
        // B. Verify Password
        elseif (password_verify($password, $user['password'])) {
            
            // C. Check for Pending (Under Review) status
            if ($user['status'] == 'Pending') {
                // Store minimal info in session just for the review page
                $_SESSION['name'] = $user['first_name'];
                header("Location: under_review.php");
                exit();
            }

            // D. Standard Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['surname'];

            // Role-based Redirection
            if ($user['role'] == 'Admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] == 'Doctor') {
                header("Location: doctor_dashboard.php");
            } elseif ($user['role'] == 'Patient') {
                header("Location: patient_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "No account found with that email address.";
    }
}

include 'includes/header.php'; 
?>

<div class="container">
    <div class="form-wrapper" style="max-width: 500px; margin: 5rem auto; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        <div class="form-header" style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: #0f172a;">Welcome Back</h2>
            <p style="color: #64748b;">Log in to access your GoldByte CAMS dashboard.</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; font-weight: 500;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 8px;">Email Address</label>
                <input type="email" name="email" required placeholder="Enter your registered email" style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1; outline-color: #2563eb;">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 8px;">Password</label>
                <input type="password" name="password" required placeholder="Enter your password" style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1; outline-color: #2563eb;">
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 12px; font-weight: 700; font-size: 1rem;">Login to Dashboard</button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 0.5rem;">Don't have an account?</p>
            <div style="display: flex; justify-content: center; gap: 15px; font-size: 0.85rem;">
                <a href="add.php" style="color: #2563eb; font-weight: 600; text-decoration: none;">Register Patient</a>
                <span style="color: #cbd5e1;">|</span>
                <a href="add_doctor.php" style="color: #2563eb; font-weight: 600; text-decoration: none;">Apply as Doctor</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>