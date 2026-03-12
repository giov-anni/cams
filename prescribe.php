<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// 1. SECURITY: Only Doctors can prescribe
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}

$appt_id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : 0;
$doctor_id = $_SESSION['user_id'];
$success_msg = false;
$error_msg = "";

// 2. FETCH APPOINTMENT & PATIENT DETAILS
$query = "SELECT a.*, u.first_name, u.surname, u.phone_number, s.name as specialty_name 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          JOIN specialties s ON a.specialty_id = s.id
          WHERE a.id = '$appt_id'";
$res = $conn->query($query);
$appt = $res->fetch_assoc();

if (!$appt) {
    echo "<div class='container'><p>Appointment record not found.</p></div>";
    include 'includes/footer.php';
    exit();
}

// 3. PROCESS TREATMENT SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_session'])) {
    $prescriptions = $conn->real_escape_string($_POST['prescriptions']);
    $doctor_notes = $conn->real_escape_string($_POST['doctor_notes']);
    $medical_file_name = $appt['medical_file']; // Keep old file if new one isn't uploaded

    // Handle File Upload
    if (!empty($_FILES['lab_report']['name'])) {
        $target_dir = "uploads/medical/";
        $file_ext = strtolower(pathinfo($_FILES["lab_report"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . "_lab_" . $appt_id . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        // Validate file type (PDF, JPG, PNG)
        $allowed = array('pdf', 'jpg', 'jpeg', 'png');
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES["lab_report"]["tmp_name"], $target_file)) {
                $medical_file_name = $new_filename;
            } else {
                $error_msg = "Critical: Could not upload the medical file. Check folder permissions.";
            }
        } else {
            $error_msg = "Invalid file type. Please upload PDF or Images only.";
        }
    }

    if (empty($error_msg)) {
        // Update the record: Save RX, internal notes, and the filename
        $update_sql = "UPDATE appointments SET 
                       prescriptions = '$prescriptions', 
                       doctor_notes = '$doctor_notes', 
                       medical_file = '$medical_file_name',
                       status = 'Completed' 
                       WHERE id = '$appt_id'";

        if ($conn->query($update_sql)) {
            $success_msg = true;
            header("refresh:2;url=doctor_dashboard.php");
        } else {
            $error_msg = "Database Error: " . $conn->error;
        }
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <div class="form-wrapper" style="max-width: 800px; margin: 0 auto;">
        
        <?php if($success_msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 2.5rem; border-radius: 20px; text-align: center; border: 1px solid #bbf7d0; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">✅</div>
                <h2 style="margin-bottom: 10px;">Consultation Finalized</h2>
                <p>The prescription has been issued and medical documents have been uploaded.</p>
            </div>
        <?php else: ?>

            <?php if($error_msg): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border: 1px solid #fecaca;">
                    ❌ <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div style="background: #0f172a; color: white; padding: 2rem; border-radius: 24px 24px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="color: #60a5fa; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Clinical Workspace</span>
                    <h2 style="margin: 5px 0; font-size: 1.8rem;"><?php echo htmlspecialchars($appt['first_name'] . ' ' . $appt['surname']); ?></h2>
                    <p style="font-size: 0.9rem; opacity: 0.8;">Ref: #GB-A-<?php echo $appt_id; ?> | Contact: <?php echo htmlspecialchars($appt['phone_number']); ?></p>
                </div>
                <div style="text-align: right;">
                    <span style="background: rgba(255,255,255,0.1); padding: 10px 20px; border-radius: 12px; font-size: 0.85rem;">
                        Dept: <strong><?php echo htmlspecialchars($appt['specialty_name']); ?></strong>
                    </span>
                </div>
            </div>

            <div style="background: white; padding: 2.5rem; border-radius: 0 0 24px 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: none;">
                
                <form method="POST" enctype="multipart/form-data">
                    
                    <div style="margin-bottom: 2.5rem;">
                        <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 12px; font-size: 0.9rem; text-transform: uppercase;">Patient's Complaint</label>
                        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 15px; border: 1px solid #e2e8f0; color: #1e293b; font-style: italic; line-height: 1.6;">
                            "<?php echo !empty($appt['symptoms']) ? htmlspecialchars($appt['symptoms']) : 'No specific symptoms reported by patient.'; ?>"
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label style="font-weight: 700; color: #2563eb; display: block; margin-bottom: 12px;">📜 Digital Prescription (Visible to Patient)</label>
                        <textarea name="prescriptions" rows="5" style="width: 100%; padding: 1.2rem; border-radius: 15px; border: 2px solid #dbeafe; font-family: 'Poppins', sans-serif; font-size: 1rem;" placeholder="Medications and dosage..." required><?php echo htmlspecialchars($appt['prescriptions'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem; background: #f0f9ff; padding: 1.5rem; border-radius: 15px; border: 1px solid #bae6fd;">
                        <label style="font-weight: 700; color: #0369a1; display: block; margin-bottom: 8px;">🔬 Upload Lab Report / Results</label>
                        <p style="font-size: 0.8rem; color: #0c4a6e; margin-bottom: 12px;">Attach test results, scans, or lab documents (PDF or Images).</p>
                        <input type="file" name="lab_report" style="font-size: 0.9rem; color: #0369a1;">
                        <?php if($appt['medical_file']): ?>
                            <p style="margin-top: 10px; font-size: 0.8rem; color: #10b981;">✅ Current file: <?php echo $appt['medical_file']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group" style="margin-bottom: 2.5rem;">
                        <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 12px;">🔒 Clinical Observations (Doctor Eyes Only)</label>
                        <textarea name="doctor_notes" rows="3" style="width: 100%; padding: 1.2rem; border-radius: 15px; border: 1px dashed #cbd5e1; background: #f9fafb; font-family: 'Poppins', sans-serif; font-size: 0.95rem;" placeholder="Internal notes..."><?php echo htmlspecialchars($appt['doctor_notes'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 20px; align-items: center; margin-top: 2rem;">
                        <button type="submit" name="complete_session" class="btn btn-primary" style="flex: 2; padding: 1.2rem; font-weight: 700; border-radius: 16px;">
                            Finalize & Complete Visit
                        </button>
                        <a href="doctor_dashboard.php" style="flex: 1; text-align: center; padding: 1.2rem; color: #64748b; text-decoration: none; font-weight: 600; background: #f1f5f9; border-radius: 16px;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>