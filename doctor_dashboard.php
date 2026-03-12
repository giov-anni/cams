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

// 2. FETCH DOCTOR'S DETAILS
$doc_info_query = "SELECT d.*, s.name as specialty_name 
                   FROM doctors d 
                   JOIN specialties s ON d.specialty_id = s.id 
                   WHERE d.user_id = '$doctor_user_id'";
$doc_info_result = $conn->query($doc_info_query);
$doctor_data = $doc_info_result->fetch_assoc();
$my_specialty_id = $doctor_data['specialty_id'];

// 3. FETCH APPOINTMENTS (Including home_address)
$appt_query = "SELECT a.*, u.first_name, u.surname, u.phone_number 
               FROM appointments a 
               JOIN users u ON a.patient_id = u.id 
               WHERE a.specialty_id = '$my_specialty_id' 
               ORDER BY a.is_emergency DESC, a.appointment_date ASC";
$appt_result = $conn->query($appt_query);

// 4. STATS CALCULATION
$pending_count = 0;
$emergency_count = 0;
$temp_res = $conn->query("SELECT is_emergency, status FROM appointments WHERE specialty_id = '$my_specialty_id'");
while($stat = $temp_res->fetch_assoc()) {
    if($stat['status'] == 'Pending') $pending_count++;
    if($stat['is_emergency'] == 1 && $stat['status'] == 'Pending') $emergency_count++;
}
?>

<div class="container" style="margin-top: 2rem;">
    <div style="background: #ffffff; padding: 2rem; border-radius: 24px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 30px; margin-bottom: 2rem;">
        <div style="width: 100px; height: 100px; border-radius: 20px; background: #eff6ff; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
            👨‍⚕️
        </div>
        <div style="flex-grow: 1;">
            <span style="background: #f0fdf4; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Verified Practitioner</span>
            <h2 style="color: #0f172a; font-size: 1.8rem; margin: 5px 0;">Dr. <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p style="color: #64748b; font-size: 0.95rem;">
                Specialty: <strong><?php echo htmlspecialchars($doctor_data['specialty_name']); ?></strong> | 
                License: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?php echo htmlspecialchars($doctor_data['license_number']); ?></code>
            </p>
        </div>
        <div style="text-align: right;">
            <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 5px;">Clinical Status</p>
            <span style="background: #10b981; color: white; padding: 8px 16px; border-radius: 12px; font-weight: 700; font-size: 0.9rem;">● ON DUTY</span>
        </div>
    </div>

    <div class="service-grid" style="margin-bottom: 3rem;">
        <div class="service-card" style="text-align: left; padding: 1.5rem; background: #ffffff; border-left: 5px solid #2563eb;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Pending Consultation</h4>
            <p style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin: 10px 0;"><?php echo $pending_count; ?></p>
        </div>
        <div class="service-card" style="text-align: left; padding: 1.5rem; background: #ffffff; border-left: 5px solid #ef4444;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Urgent Emergencies</h4>
            <p style="font-size: 2.2rem; font-weight: 800; color: #ef4444; margin: 10px 0;"><?php echo $emergency_count; ?></p>
        </div>
        <div class="service-card" style="text-align: left; padding: 1.5rem; background: #ffffff; border-left: 5px solid #10b981;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Department</h4>
            <p style="font-size: 1.2rem; font-weight: 700; color: #0f172a; margin: 10px 0;"><?php echo htmlspecialchars($doctor_data['specialty_name']); ?></p>
        </div>
    </div>

    <div style="background: white; border-radius: 24px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; overflow-x: auto;">
        <h3 style="color: #0f172a; margin-bottom: 1.5rem;">Live Patient Queue</h3>
        
        <?php if ($appt_result->num_rows > 0): ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                <thead>
                    <tr style="color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #f1f5f9;">
                        <th style="padding: 1rem;">Patient</th>
                        <th style="padding: 1rem;">Time / Tier</th>
                        <th style="padding: 1rem;">Service & Location</th>
                        <th style="padding: 1rem;">Status</th>
                        <th style="padding: 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $appt_result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f8fafc; background: <?php echo ($row['is_emergency'] && $row['status'] == 'Pending') ? '#fff5f5' : 'transparent'; ?>;">
                            <td style="padding: 1.5rem 1rem;">
                                <div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></div>
                                <div style="color: #64748b; font-size: 0.85rem;">📞 <?php echo htmlspecialchars($row['phone_number']); ?></div>
                            </td>
                            <td style="padding: 1.5rem 1rem;">
                                <div style="font-weight: 600;"><?php echo date("M j, g:i A", strtotime($row['appointment_date'])); ?></div>
                                <?php if ($row['is_emergency']): ?>
                                    <span style="color: #ef4444; font-size: 0.7rem; font-weight: 900; letter-spacing: 1px;">🚨 EMERGENCY</span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="padding: 1.5rem 1rem;">
                                <span style="background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; color: #475569; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['service_type']); ?>
                                </span>
                                <?php if($row['service_type'] == 'Home-Service' && !empty($row['home_address'])): ?>
                                    <div style="margin-top: 8px; font-size: 0.8rem; color: #2563eb; display: flex; align-items: flex-start; gap: 4px;">
                                        <span>📍</span>
                                        <span style="font-style: italic;"><?php echo htmlspecialchars($row['home_address']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 1.5rem 1rem;">
                                <span style="font-weight: 700; font-size: 0.85rem; color: <?php echo ($row['status'] == 'Pending') ? '#f59e0b' : '#10b981'; ?>;">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1.5rem 1rem; text-align: center;">
                                <?php if($row['status'] == 'Pending'): ?>
                                    <a href="prescribe.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 8px; text-decoration: none;">
                                        Treat Patient &rarr;
                                    </a>
                                <?php else: ?>
                                    <a href="prescribe.php?id=<?php echo $row['id']; ?>" style="color: #2563eb; font-size: 0.85rem; text-decoration: none; font-weight: 600;">View Summary</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem;">
                <p style="color: #94a3b8; font-size: 1.1rem;">No patients in your queue today.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>