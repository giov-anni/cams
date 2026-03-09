<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php'; 

// 1. SECURITY CHECK: Ensure the user is a Doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}

$doctor_user_id = $_SESSION['user_id'];

// 2. FETCH DOCTOR'S DETAILS (Including their specialty ID)
$doc_info_query = "SELECT d.*, s.name as specialty_name 
                   FROM doctors d 
                   JOIN specialties s ON d.specialty_id = s.id 
                   WHERE d.user_id = '$doctor_user_id'";
$doc_info_result = $conn->query($doc_info_query);
$doctor_data = $doc_info_result->fetch_assoc();
$my_specialty_id = $doctor_data['specialty_id'];

// 3. FETCH APPOINTMENTS matching this doctor's specialty
// We JOIN with the 'users' table to get the patient's actual name
$appt_query = "SELECT a.*, u.first_name, u.surname, u.phone_number 
               FROM appointments a 
               JOIN users u ON a.patient_id = u.id 
               WHERE a.specialty_id = '$my_specialty_id' 
               ORDER BY a.appointment_date ASC";
$appt_result = $conn->query($appt_query);
?>

<div class="container">
    <div class="form-header" style="text-align: left; margin-bottom: 2rem;">
        <h2>👨‍⚕️ Doctor Dashboard: <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
        <p>Specialty: <strong><?php echo htmlspecialchars($doctor_data['specialty_name']); ?></strong> | License: <?php echo htmlspecialchars($doctor_data['license_number']); ?></p>
    </div>

    <div class="service-grid" style="margin-bottom: 3rem;">
        <div class="service-card" style="padding: 1.5rem; text-align: left; border-left: 5px solid #2563eb;">
            <h3 style="font-size: 0.9rem; color: #64748b;">Pending Patients</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #0f172a;"><?php echo $appt_result->num_rows; ?></p>
        </div>
        <div class="service-card" style="padding: 1.5rem; text-align: left;">
            <h3 style="font-size: 0.9rem; color: #64748b;">Verified Status</h3>
            <p style="color: #166534; font-weight: 600;">✅ Active Medical Practitioner</p>
        </div>
    </div>

    <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9;">
        <h3 style="margin-bottom: 1.5rem; color: #0f172a;">Upcoming Appointments</h3>
        
        <?php if ($appt_result->num_rows > 0): ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 1rem;">Patient Name</th>
                        <th style="padding: 1rem;">Date/Time</th>
                        <th style="padding: 1rem;">Service</th>
                        <th style="padding: 1rem;">Contact</th>
                        <th style="padding: 1rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $appt_result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; font-weight: 600; color: #0f172a;">
                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <?php echo date("M d, H:i", strtotime($row['appointment_date'])); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <?php 
                                    echo htmlspecialchars($row['service_type']); 
                                    if($row['is_emergency']) echo " <br><span style='background:#fef2f2; color:#b91c1c; font-size:0.7rem; padding:2px 5px; border-radius:4px; font-weight:bold;'>URGENT</span>";
                                ?>
                            </td>
                            <td style="padding: 1rem; color: #475569;">
                                <?php echo htmlspecialchars($row['phone_number']); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: #eff6ff; color: #1e40af; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #64748b;">
                <p>No appointments found for your specialty yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>