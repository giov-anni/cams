<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// 1. Fetch Profile Details
$user_query = "SELECT * FROM users WHERE id = '$patient_id' AND role = 'Patient'";
$user_result = $conn->query($user_query);
$patient = $user_result->fetch_assoc();

// 2. Fetch Detailed Appointment History
$appt_query = "SELECT a.*, s.name AS specialty_display 
                FROM appointments a 
                LEFT JOIN specialties s ON a.specialty_id = s.id 
                WHERE a.patient_id = '$patient_id' 
                ORDER BY a.appointment_date DESC";
$appt_result = $conn->query($appt_query);

// 3. Stats Calculation
$pending_count = 0;
$completed_count = 0;
$temp_res = $conn->query("SELECT status FROM appointments WHERE patient_id = '$patient_id'");
while($stat = $temp_res->fetch_assoc()) {
    if($stat['status'] == 'Pending') $pending_count++;
    if($stat['status'] == 'Completed') $completed_count++;
}
?>

<div class="container" style="margin-top: 2rem;">
    <div style="background: #ffffff; padding: 2rem; border-radius: 24px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 30px; margin-bottom: 2rem;">
        
        <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 4px solid #f0f7ff; box-shadow: 0 8px 20px rgba(0,0,0,0.08); background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
            <?php if(!empty($patient['profile_pic']) && file_exists($patient['profile_pic'])): ?>
                <img src="<?php echo $patient['profile_pic']; ?>" alt="Profile" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <div style="font-size: 3.5rem;">👤</div>
            <?php endif; ?>
        </div>

        <div style="flex-grow: 1;">
            <span style="background: #eff6ff; color: #2563eb; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Verified Patient</span>
            <h2 style="color: #0f172a; font-size: 2rem; margin: 5px 0;">Welcome, <?php echo htmlspecialchars($patient['first_name']); ?>!</h2>
            <p style="color: #64748b; font-size: 0.95rem;">ID: #GB-P-<?php echo str_pad($patient['id'], 4, '0', STR_PAD_LEFT); ?> | <a href="update_profile.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">Edit Profile Details &rarr;</a></p>
        </div>
        <div>
            <a href="book.php" class="btn btn-primary" style="padding: 1rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 700;">+ New Appointment</a>
        </div>
    </div>

    <div class="service-grid" style="margin-bottom: 3rem;">
        <div class="service-card" style="text-align: left; padding: 1.5rem; background: #ffffff; border: 1px solid #f1f5f9;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Lifetime Visits</h4>
            <p style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin: 10px 0;"><?php echo $appt_result->num_rows; ?></p>
        </div>
        <div class="service-card" style="text-align: left; padding: 1.5rem; background: #ffffff; border: 1px solid #f1f5f9;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Sessions Completed</h4>
            <p style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin: 10px 0;"><?php echo $completed_count; ?></p>
        </div>
        <div class="service-card" style="text-align: left; padding: 1.5rem; background: #ffffff; border: 1px solid #f1f5f9;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Active Requests</h4>
            <p style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin: 10px 0;"><?php echo $pending_count; ?></p>
        </div>
    </div>

    <div style="background: white; border-radius: 24px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; overflow-x: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem;">
            <h3 style="color: #0f172a; font-size: 1.4rem;">Clinical History & Secure Vault</h3>
            <span style="font-size: 0.85rem; color: #94a3b8;">● Data Encrypted & Secure</span>
        </div>
        
        <?php if ($appt_result->num_rows > 0): ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                <thead>
                    <tr style="color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                        <th style="padding: 1rem;">Schedule</th>
                        <th style="padding: 1rem;">Service & Location</th>
                        <th style="padding: 1rem;">Status</th>
                        <th style="padding: 1rem;">Medical Documents</th>
                        <th style="padding: 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $appt_result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f8fafc; vertical-align: middle;">
                            <td style="padding: 1.5rem 1rem;">
                                <div style="font-weight: 700; color: #0f172a;"><?php echo date("M j, Y", strtotime($row['appointment_date'])); ?></div>
                                <div style="color: #64748b; font-size: 0.85rem;"><?php echo date("g:i A", strtotime($row['appointment_date'])); ?></div>
                                <?php if ($row['is_emergency']): ?><span style="color: #ef4444; font-size: 0.65rem; font-weight: 900;">🚨 URGENT</span><?php endif; ?>
                            </td>
                            
                            <td style="padding: 1.5rem 1rem;">
                                <div style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 5px; border: 1px solid #dbeafe;">
                                    <?php echo htmlspecialchars($row['specialty_display'] ?? 'General'); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #475569;">
                                    Type: <strong><?php echo $row['service_type']; ?></strong>
                                    <?php if($row['service_type'] == 'Home-Service' && !empty($row['home_address'])): ?>
                                        <br><span style="color: #0369a1; font-style: italic;">📍 <?php echo htmlspecialchars($row['home_address']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td style="padding: 1.5rem 1rem;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: <?php echo ($row['status'] == 'Pending') ? '#f59e0b' : '#10b981'; ?>;">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1.5rem 1rem;">
                                <?php if($row['status'] == 'Completed'): ?>
                                    <a href="download_prescription.php?id=<?php echo $row['id']; ?>" style="color: #10b981; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                        📥 Download Prescription
                                    </a>
                                <?php else: ?>
                                    <a href="download_booking.php?id=<?php echo $row['id']; ?>" style="color: #2563eb; font-weight: 600; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                        📄 Booking Slip
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($row['medical_file']): ?>
                                    <div style="margin-top: 5px;">
                                        <a href="uploads/medical/<?php echo $row['medical_file']; ?>" target="_blank" style="color: #64748b; font-size: 0.75rem; text-decoration: underline;">🔬 View Lab Report</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1.5rem 1rem; text-align: center;">
                                <?php if($row['status'] == 'Pending'): ?>
                                    <a href="view_appointment.php?id=<?php echo $row['id']; ?>" class="btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.8rem; border-radius: 8px; text-decoration: none; border: 1px solid #2563eb; color: #2563eb;">Manage Visit</a>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.75rem;">Session Finalized</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem;">
                <p style="color: #94a3b8; font-size: 1.1rem;">Your medical vault is currently empty.</p>
                <a href="book.php" class="btn btn-primary" style="margin-top: 1.5rem;">Start Your Health Journey</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>