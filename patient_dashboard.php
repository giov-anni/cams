<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php'; 

// SECURITY CHECK: Ensure the user is logged in AND is a Patient
// If not logged in, it will try to use ID 1 for your current testing
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Patient') {
    $patient_id = $_SESSION['user_id'];
} else {
    // For development testing only: if not logged in, default to ID 1
    $patient_id = 1; 
}

// 1. Fetch the Patient's Name
$user_query = "SELECT first_name, surname FROM users WHERE id = '$patient_id' AND role = 'Patient'";
$user_result = $conn->query($user_query);

// If the patient doesn't exist, stop the page.
if (!$user_result || $user_result->num_rows == 0) {
    echo "<div class='container'><h2>Error: Patient record not found. Please register or login first.</h2></div>";
    include 'includes/footer.php';
    exit();
}
$patient = $user_result->fetch_assoc();

// 2. Fetch all Appointments for this specific patient
// FIX: Using 's.name' (standard column name) and aliasing it to 'specialty_display'
$appt_query = "SELECT a.*, s.name AS specialty_display 
               FROM appointments a 
               LEFT JOIN specialties s ON a.specialty_id = s.id 
               WHERE a.patient_id = '$patient_id' 
               ORDER BY a.appointment_date DESC";

$appt_result = $conn->query($appt_query);

// If the query failed, show the error (useful for debugging)
if (!$appt_result) {
    die("Database Query Failed: " . $conn->error);
}
?>

<div class="container">
    <div class="form-header" style="text-align: left; margin-bottom: 2rem;">
        <h2>👋 Welcome back, <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['surname']); ?>!</h2>
        <p>Manage your GoldByte CAMS healthcare appointments and VVIP services in Winneba.</p>
    </div>

    <div class="service-grid" style="margin-bottom: 3rem;">
        <div class="service-card" style="padding: 1.5rem; text-align: left;">
            <h3 style="font-size: 1rem; color: #64748b;">Total Appointments</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #2563eb;"><?php echo $appt_result->num_rows; ?></p>
        </div>
        <div class="service-card" style="padding: 1.5rem; text-align: left; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 1rem; color: #64748b;">Quick Actions</h3>
            <div style="margin-top: 0.5rem;">
                <a href="add.php" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">Book New Appointment</a>
            </div>
        </div>
    </div>

    <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9; overflow-x: auto;">
        <h3 style="margin-bottom: 1.5rem; color: #0f172a;">Your Appointment History</h3>
        
        <?php if ($appt_result->num_rows > 0): ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 600px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 1rem; color: #475569;">Date & Time</th>
                        <th style="padding: 1rem; color: #475569;">Specialist</th>
                        <th style="padding: 1rem; color: #475569;">Service Type</th>
                        <th style="padding: 1rem; color: #475569;">Fee (GH₵)</th>
                        <th style="padding: 1rem; color: #475569;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $appt_result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 1rem; color: #0f172a; font-weight: 500;">
                                <?php echo date("M j, Y - g:i a", strtotime($row['appointment_date'])); ?>
                            </td>
                            <td style="padding: 1rem; color: #475569;">
                                <?php echo htmlspecialchars($row['specialty_display'] ?? 'General Practitioner'); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <?php 
                                    echo htmlspecialchars($row['service_type']); 
                                    if ($row['is_emergency'] == 1) {
                                        echo " <span style='color: white; background: #ef4444; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; margin-left: 5px; vertical-align: middle;'>EMERGENCY</span>";
                                    }
                                ?>
                            </td>
                            <td style="padding: 1rem; color: #0f172a; font-weight: 600;">
                                <?php echo number_format($row['total_fee'], 2); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: #dcfce7; color: #166534; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #64748b;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <p>You haven't scheduled any appointments yet.</p>
                <a href="add.php" style="color: #2563eb; font-weight: 600; text-decoration: none; margin-top: 10px; display: inline-block;">Book your first visit today &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>