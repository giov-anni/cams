<?php
// Start the session at the very top so we can check user status on every page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldByte CAMS | Clinic Management</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="index.php" style="text-decoration: none; color: inherit;">
                <h2>GoldByte <span>CAMS</span></h2>
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if ($_SESSION['role'] === 'Patient'): ?>
                    <li><a href="patient_dashboard.php">My Appointments</a></li>
                
                <?php elseif ($_SESSION['role'] === 'Doctor'): ?>
                    <li><a href="doctor_dashboard.php">Staff Dashboard</a></li>
                
                <?php elseif ($_SESSION['role'] === 'Admin'): ?>
                    <li><a href="admin_dashboard.php">Management</a></li>
                    <li><a href="bulk_sms.php" style="color: #2563eb; font-weight: 600;">🚀 SMS Marketing</a></li>
                <?php endif; ?>
                
                <li><a href="logout.php" style="color: #ef4444; font-weight: 600;">Logout</a></li>

            <?php else: ?>
                <li><a href="add.php">Book Appointment</a></li>
                <li><a href="add_doctor.php">Join as Doctor</a></li>
                <li><a href="login.php" class="btn-secondary" style="padding: 0.4rem 1rem; border-radius: 6px; border: 1px solid #2563eb; color: #2563eb;">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">