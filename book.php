<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// Security: Must be logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$patient_name = $_SESSION['name'];
?>

<div class="container">
    <div class="form-wrapper" style="max-width: 600px; margin-top: 3rem;">
        <div class="form-header">
            <h2 style="color: #0f172a;">New Consultation</h2>
            <p>Welcome back, <strong><?php echo htmlspecialchars($patient_name); ?></strong>. Select your specialist below.</p>
        </div>

        <form action="process_booking.php" method="POST" onsubmit="return validateBooking()">
            
            <div class="form-group">
                <label>Select Specialist Department</label>
                <select name="specialty_id" required>
                    <option value="">-- Choose Specialty --</option>
                    <option value="1">General Purpose</option>
                    <option value="2">Dentist</option>
                    <option value="3">Optometrist</option>
                    <option value="4">Gynecologist</option>
                    <option value="5">Pediatrician</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Appointment Date & Time</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required>
                </div>
                <div class="form-group">
                    <label>Service Type</label>
                    <div class="radio-group" style="padding-top: 10px;">
                        <label class="radio-item"><input type="radio" name="service_type" value="In-Clinic" checked onchange="calculateFee()"> In-Clinic</label>
                        <label class="radio-item"><input type="radio" name="service_type" value="Home-Service" onchange="calculateFee()"> Home-Service</label>
                    </div>
                </div>
            </div>

            <div class="checkbox-group" style="background: #fff1f2; border: 1px solid #fecaca; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="emergencyCheck" name="is_emergency" value="1" onchange="calculateFee()">
                <label for="emergencyCheck" style="color: #b91c1c; font-weight: 700; cursor: pointer; margin: 0;">🚨 EMERGENCY PRIORITY (+200 GH₵)</label>
            </div>

            <div class="fee-display" style="background: #f0f7ff; border: 2px dashed #3b82f6; padding: 1.5rem; border-radius: 12px; text-align: center; margin-bottom: 2rem;">
                <h3 style="font-size: 0.8rem; color: #1e40af; text-transform: uppercase;">Booking Total</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: #2563eb;">GH₵ <span id="totalAmount">100.00</span></div>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Confirm & Book Session</button>
                <p style="margin-top: 1.5rem;"><a href="patient_dashboard.php" style="color: #64748b; text-decoration: none;">&larr; Back to Dashboard</a></p>
            </div>
        </form>
    </div>
</div>

<script>
function calculateFee() {
    let total = 100;
    const serviceType = document.querySelector('input[name="service_type"]:checked').value;
    if(serviceType === 'Home-Service') total += 100;
    if(document.getElementById('emergencyCheck').checked) total += 200;
    document.getElementById('totalAmount').innerText = total.toFixed(2);
}

function validateBooking() {
    const dateInput = document.getElementById('appointment_date').value;
    const isEmergency = document.getElementById('emergencyCheck').checked;
    const serviceType = document.querySelector('input[name="service_type"]:checked').value;
    const selectedDate = new Date(dateInput);
    const now = new Date();

    if (selectedDate < now) { alert("You cannot book in the past."); return false; }

    if (!isEmergency && serviceType !== "Home-Service") {
        const day = selectedDate.getDay(); 
        const hour = selectedDate.getHours();
        if (day >= 1 && day <= 5) {
            if (hour < 8 || hour >= 16) { alert("Clinic Hours (Mon-Fri): 08:00am - 04:00pm."); return false; }
        } else {
            if (hour < 12 || hour >= 16) { alert("Clinic Hours (Weekend): 12:00pm - 04:00pm."); return false; }
        }
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>