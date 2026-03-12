<?php include 'includes/header.php'; ?>

<div class="form-wrapper">
    <div class="form-header">
        <h2>Doctor Onboarding</h2>
        <p>Apply to join the GoldByte CAMS medical network in Winneba.</p>
    </div>

    <form id="doctorForm" action="process_doctor.php" method="POST" enctype="multipart/form-data">
        
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
                <label>Phone Number</label>
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
            </div>
        </div>

        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #cbd5e1;">

        <div class="form-row">
            <div class="form-group">
                <label>Your Specialty</label>
                <select name="specialty_id" required>
                    <option value="">-- Select Specialty --</option>
                    <option value="1">General Purpose</option>
                    <option value="2">Dentist</option>
                    <option value="3">Optometrist</option>
                    <option value="4">Gynecologist</option>
                    <option value="5">Pediatrician</option>
                </select>
            </div>
            <div class="form-group">
                <label>Medical License Number</label>
                <input type="text" name="license_number" placeholder="e.g. MDC/RN/XXXX" required>
            </div>
        </div>

        <div class="form-group">
            <label>Upload CV (PDF format only)</label>
            <input type="file" name="cv_file" accept=".pdf" required style="padding: 0.5rem; background: white;">
        </div>

        <div class="form-group">
            <label>Professional Bio</label>
            <textarea name="bio" placeholder="Briefly describe your experience, qualifications, and previous practice..." required></textarea>
        </div>

        <div style="display: flex; justify-content: center; margin-top: 2rem; padding-bottom: 1rem;">
            <button type="submit" class="btn btn-primary" style="
                background: #0f172a; 
                box-shadow: 0 4px 14px 0 rgba(15, 23, 42, 0.39); 
                padding: 1rem 4rem; 
                border-radius: 12px; 
                width: auto; 
                cursor: pointer; 
                border: none; 
                color: white; 
                font-weight: 600; 
                transition: transform 0.2s ease;
            ">
                Submit Application
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>