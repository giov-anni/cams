<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$appt_id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// 1. Fetch Appointment Data
$query = "SELECT a.*, s.name as specialty_name FROM appointments a 
          LEFT JOIN specialties s ON a.specialty_id = s.id 
          WHERE a.id = '$appt_id' AND a.patient_id = '$user_id'";
$res = $conn->query($query);
$appt = $res->fetch_assoc();

if (!$appt) {
    echo "<div class='container'><p>Record not found.</p></div>";
    include 'includes/footer.php';
    exit();
}

// 2. Handle Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- INDEPENDENT CANCEL LOGIC ---
    if (isset($_POST['action_cancel'])) {
        if ($conn->query("DELETE FROM appointments WHERE id = '$appt_id' AND patient_id = '$user_id'")) {
            $msg = "Appointment #GB-A-$appt_id has been successfully removed.";
            header("refresh:2;url=patient_dashboard.php?msg=cancelled");
        } else {
            $error = "System Error: Could not process cancellation.";
        }
    }
    
    // --- RESCHEDULE LOGIC ---
    if (isset($_POST['action_reschedule'])) {
        $new_date_str = $_POST['new_date'];
        
        try {
            $new_date = new DateTime($new_date_str);
            $now = new DateTime();
            $hour = (int)$new_date->format('H');
            $day = (int)$new_date->format('w'); 

            if ($new_date < $now) {
                $error = "Error: You cannot reschedule to a past date.";
            } elseif ($appt['is_emergency'] == 0 && $appt['service_type'] !== 'Home-Service') {
                $is_weekday = ($day >= 1 && $day <= 5);
                $is_weekend = ($day == 0 || $day == 6);

                if ($is_weekday && ($hour < 8 || $hour >= 16)) {
                    $error = "Clinic Closed. Weekday hours: 08:00 AM - 04:00 PM.";
                } elseif ($is_weekend && ($hour < 12 || $hour >= 16)) {
                    $error = "Clinic Closed. Weekend hours: 12:00 PM - 04:00 PM.";
                }
            }

            if (empty($error)) {
                $conn->query("UPDATE appointments SET appointment_date = '$new_date_str' WHERE id = '$appt_id'");
                $msg = "Success! Your visit has been rescheduled.";
                header("refresh:2;url=patient_dashboard.php");
            }
        } catch (Exception $e) {
            $error = "Date Parsing Error: Please ensure you enter a valid date and time.";
        }
    }
}
?>

<div class="container">
    <div class="form-wrapper" style="max-width: 600px; margin: 3rem auto; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid #f1f5f9;">
        
        <?php if($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1.5rem; border-radius: 12px; text-align: center; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
                <span style="font-size: 1.5rem;">✅</span><br><?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 2rem; border: 1px solid #fecaca;">
                <span style="font-size: 1.2rem;">❌</span><br><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(!$msg): ?>
        <div class="form-header" style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: #0f172a; font-size: 1.8rem;">Manage <span style="color: #2563eb;">Appointment</span></h2>
            <p style="color: #64748b;">Ref: #GB-A-<?php echo $appt_id; ?> | <strong><?php echo htmlspecialchars($appt['specialty_name']); ?></strong></p>
        </div>

        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #e2e8f0; text-align: center;">
            <p style="margin-bottom: 5px; color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Scheduled For</p>
            <p style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">
                <?php echo date("F j, Y @ g:i A", strtotime($appt['appointment_date'])); ?>
            </p>
        </div>

        <form method="POST" onsubmit="return validateReschedule()">
            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Select New Date & Time</label>
                <input type="datetime-local" id="new_date_field" name="new_date" 
                       min="<?php echo date('Y-m-d\TH:i'); ?>" 
                       style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;" required>
            </div>

            <input type="hidden" id="is_emergency" value="<?php echo $appt['is_emergency']; ?>">
            <input type="hidden" id="service_type" value="<?php echo $appt['service_type']; ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <button type="submit" name="action_reschedule" class="btn btn-primary" style="padding: 1rem; border-radius: 10px;">Update Date</button>
                
                <button type="submit" name="action_cancel" formnovalidate 
                        onclick="return confirm('Are you sure you want to cancel this visit? This action cannot be undone.')"
                        class="btn-secondary" style="padding: 1rem; color: #ef4444; border-color: #fecaca; border-radius: 10px; font-weight: 600;">
                        Cancel Visit
                </button>
            </div>
        </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
            <a href="patient_dashboard.php" style="color: #64748b; text-decoration: none; font-size: 0.9rem; font-weight: 600;">&larr; Return to Dashboard</a>
        </div>
    </div>
</div>

<script>
function validateReschedule() {
    // Only check validation if the 'Reschedule' button was the one clicked
    if (document.activeElement.name !== "action_reschedule") return true;

    const dateInput = document.getElementById('new_date_field').value;
    const isEmergency = document.getElementById('is_emergency').value == "1";
    const serviceType = document.getElementById('service_type').value;
    
    const selectedDate = new Date(dateInput);
    const now = new Date();
    const hour = selectedDate.getHours();
    const day = selectedDate.getDay();

    if (selectedDate < now) {
        alert("Selection Error: You cannot reschedule to a past date.");
        return false;
    }

    if (!isEmergency && serviceType !== "Home-Service") {
        if (day >= 1 && day <= 5) {
            if (hour < 8 || hour >= 16) {
                alert("Clinic Hours (Mon-Fri): 08:00 AM - 04:00 PM.");
                return false;
            }
        } else {
            if (hour < 12 || hour >= 16) {
                alert("Clinic Hours (Weekend): 12:00 PM - 04:00 PM.");
                return false;
            }
        }
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>