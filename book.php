<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $specialty_id = $conn->real_escape_string($_POST['specialty_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $home_address = isset($_POST['home_address']) ? $conn->real_escape_string($_POST['home_address']) : NULL;
    $symptoms = $conn->real_escape_string($_POST['symptoms']);
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;

    // Basic Validation
    if ($service_type === 'Home-Service' && empty($home_address)) {
        $error = "Please provide your home address for the Home Service visit.";
    } else {
        $sql = "INSERT INTO appointments (patient_id, specialty_id, appointment_date, service_type, home_address, symptoms, is_emergency, status) 
                VALUES ('$patient_id', '$specialty_id', '$appointment_date', '$service_type', '$home_address', '$symptoms', '$is_emergency', 'Pending')";

        if ($conn->query($sql)) {
            $msg = "Appointment booked successfully!";
            header("refresh:2;url=patient_dashboard.php");
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Fetch Specialties for Dropdown
$specialties = $conn->query("SELECT * FROM specialties");
?>

<div class="container" style="margin-top: 3rem;">
    <div class="form-wrapper" style="max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: #0f172a;">Book a <span style="color: #2563eb;">Consultation</span></h2>
            <p style="color: #64748b;">Fill in the details below to secure your medical session.</p>
        </div>

        <?php if($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
                ✅ <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="book.php" id="bookingForm">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Medical Specialty</label>
                <select name="specialty_id" class="form-control" required style="width:100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
                    <option value="">Select Department...</option>
                    <?php while($s = $specialties->fetch_assoc()): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Preferred Date & Time</label>
                <input type="datetime-local" name="appointment_date" required style="width:100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Service Type</label>
                <select name="service_type" id="serviceType" class="form-control" required onchange="toggleAddressField()" style="width:100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
                    <option value="In-Clinic">In-Clinic Visit</option>
                    <option value="Home-Service">Home-Service (VVIP)</option>
                </select>
            </div>

            <div id="addressSection" style="display: none; margin-bottom: 1.5rem; background: #eff6ff; padding: 1.5rem; border-radius: 12px; border: 1px solid #dbeafe;">
                <label style="font-weight: 700; color: #1e40af; display: block; margin-bottom: 8px;">🏠 Residential Address</label>
                <textarea name="home_address" id="homeAddress" placeholder="Enter your full house address or digital address..." style="width:100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #bfdbfe;"></textarea>
                <small style="color: #1e40af;">Our medical officer will visit this exact location.</small>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Describe Symptoms</label>
                <textarea name="symptoms" rows="3" placeholder="Briefly explain your health concern..." style="width:100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;"></textarea>
            </div>

            <div style="background: #fdf2f2; padding: 1rem; border-radius: 12px; border: 1px solid #fee2e2; margin-bottom: 2rem; display: flex; align-items: center; gap: 12px;">
                <input type="checkbox" name="is_emergency" id="emergencyCheck" style="width: 18px; height: 18px; cursor: pointer;">
                <label for="emergencyCheck" style="color: #991b1b; font-weight: 700; font-size: 0.9rem; cursor: pointer;">Mark as Urgent Emergency Visit</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; border-radius: 12px; font-weight: 700; font-size: 1rem;">Confirm Appointment</button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="patient_dashboard.php" style="color: #64748b; text-decoration: none; font-size: 0.85rem;">&larr; Cancel and return</a>
        </div>
    </div>
</div>

<script>
// The Superpower Trigger
function toggleAddressField() {
    const serviceType = document.getElementById('serviceType').value;
    const addressSection = document.getElementById('addressSection');
    const addressInput = document.getElementById('homeAddress');

    if (serviceType === 'Home-Service') {
        addressSection.style.display = 'block';
        addressInput.setAttribute('required', 'true');
    } else {
        addressSection.style.display = 'none';
        addressInput.removeAttribute('required');
        addressInput.value = ''; // Clear if they switch back
    }
}
</script>

<?php include 'includes/footer.php'; ?>