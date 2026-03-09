<?php include 'includes/header.php'; ?>

<div class="form-wrapper">
    <div class="form-header">
        <h2>Book an Appointment</h2>
        <p>Register as a patient to schedule your visit or request home service in Winneba.</p>
    </div>

    <form id="patientForm" action="process_patient.php" method="POST">
        
        <div class="form-row">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Surname</label>
                <input type="text" name="surname" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone Number (For SMS Alerts)</label>
                <input type="text" name="phone" placeholder="e.g. 024XXXXXXX" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <div class="radio-group">
                <label class="radio-item"><input type="radio" name="gender" value="Male" required> Male</label>
                <label class="radio-item"><input type="radio" name="gender" value="Female"> Female</label>
                <label class="radio-item"><input type="radio" name="gender" value="Other"> Other</label>
            </div>
        </div>

        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #cbd5e1;">

        <div class="form-row">
            <div class="form-group">
                <label>Required Specialty</label>
                <select name="specialty_id" required>
                    <option value="">-- Select Specialist --</option>
                    <option value="1">General Purpose</option>
                    <option value="2">Dentist</option>
                    <option value="3">Optometrist</option>
                    <option value="4">Gynecologist</option>
                    <option value="5">Pediatrician</option>
                </select>
            </div>
            <div class="form-group">
                <label>Preferred Date & Time</label>
                <input type="datetime-local" name="appointment_date" required>
            </div>
        </div>

        <div class="form-group">
            <label>Service Type</label>
            <div class="radio-group">
                <label class="radio-item">
                    <input type="radio" name="service_type" value="In-Clinic" checked onchange="calculateFee()"> In-Clinic
                </label>
                <label class="radio-item">
                    <input type="radio" name="service_type" value="Home-Service" onchange="calculateFee()"> VVIP Home-Service (+100 GH₵)
                </label>
            </div>
        </div>

        <div class="form-group hidden" id="addressField">
            <label>Home Address (Required for Home-Service)</label>
            <textarea name="home_address" placeholder="Enter your full address..."></textarea>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="emergencyCheck" name="is_emergency" value="1" onchange="calculateFee()">
            <label for="emergencyCheck">EMERGENCY: Priority Booking (+200 GH₵)</label>
        </div>

        <div class="fee-display">
            <h3>Total Estimated Fee</h3>
            <div class="amount">GH₵ <span id="totalAmount">100.00</span></div>
        </div>

        <button type="submit" class="btn btn-primary">Register & Book Appointment</button>
    </form>
</div>

<script>
    function calculateFee() {
        let baseFee = 100;
        let total = baseFee;

        // Check Home Service
        const serviceType = document.querySelector('input[name="service_type"]:checked').value;
        const addressField = document.getElementById('addressField');
        
        if(serviceType === 'Home-Service') {
            total += 100;
            addressField.classList.remove('hidden');
            addressField.querySelector('textarea').required = true;
        } else {
            addressField.classList.add('hidden');
            addressField.querySelector('textarea').required = false;
        }

        // Check Emergency
        const isEmergency = document.getElementById('emergencyCheck').checked;
        if(isEmergency) {
            total += 200;
        }

        // Update Display on screen
        document.getElementById('totalAmount').innerText = total.toFixed(2);
    }
</script>

<?php include 'includes/footer.php'; ?>