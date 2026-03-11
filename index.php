<?php 
session_start();
include 'includes/header.php'; 
?>

<section class="hero" style="background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center;">
    <div class="hero-content">
        <h1 style="letter-spacing: -1px;">Modern Healthcare <br><span class="highlight">Redefined for Ghana.</span></h1>
        <p>Experience the next generation of clinical management. GoldByte CAMS integrates board-certified expertise with advanced digital accessibility to put your health first.</p>
        
        <div class="hero-buttons">
            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'Patient'): ?>
                <a href="book.php" class="btn btn-primary">Schedule Consultation</a>
                <a href="patient_dashboard.php" class="btn btn-secondary">My Health Dashboard</a>
            <?php elseif(isset($_SESSION['user_id']) && $_SESSION['role'] === 'Doctor'): ?>
                <a href="doctor_dashboard.php" class="btn btn-primary">Go to Clinical Portal</a>
            <?php else: ?>
                <a href="add.php" class="btn btn-primary">Schedule Consultation</a>
                <a href="#specialties" class="btn btn-secondary">View Departments</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="info-banner" style="background: #0f172a; color: white; padding: 2.5rem 5%;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-around; flex-wrap: wrap; gap: 30px;">
        <div style="text-align: left;">
            <h4 style="color: #60a5fa; font-size: 0.8rem; letter-spacing: 1.5px; margin-bottom: 8px; text-transform: uppercase;">Clinic Hours</h4>
            <p style="font-weight: 300;">Mon - Fri: 08:00 - 16:00 <br> Weekends: 12:00 - 16:00</p>
        </div>
        <div style="border-left: 1px solid #334155; padding-left: 30px; text-align: left;">
            <h4 style="color: #60a5fa; font-size: 0.8rem; letter-spacing: 1.5px; margin-bottom: 8px; text-transform: uppercase;">Location</h4>
            <p style="font-weight: 300;">Winneba, Central Region <br> GH-CR-0024</p>
        </div>
        <div style="border-left: 1px solid #334155; padding-left: 30px; text-align: left;">
            <h4 style="color: #f87171; font-size: 0.8rem; letter-spacing: 1.5px; margin-bottom: 8px; text-transform: uppercase;">24/7 Support</h4>
            <p style="font-weight: 300;">Emergency Priority Routing <br> Always Available Online</p>
        </div>
    </div>
</section>

<section id="specialties" class="services-section" style="background: #ffffff; padding: 6rem 5%;">
    <div class="section-title">
        <h2 style="font-weight: 800; color: #0f172a; font-size: 2.5rem;">Specialized Medical Services</h2>
        <p style="color: #64748b;">Our facility houses multiple departments managed by industry-leading practitioners.</p>
    </div>
    
    <div class="service-grid" style="max-width: 1200px; margin: 0 auto;">
        <div class="service-card" style="padding: 0; overflow: hidden; text-align: left; border-radius: 20px; border: 1px solid #f1f5f9;">
            <img src="https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=600&q=80" alt="Emergency Care" style="width: 100%; height: 220px; object-fit: cover;">
            <div style="padding: 2rem;">
                <h3 style="color: #2563eb; margin-bottom: 0.8rem; font-size: 1.3rem;">Critical Care Tier</h3>
                <p style="color: #64748b; font-size: 0.95rem;">24/7 priority booking for urgent medical needs. Direct notification to on-call specialists with zero queue time.</p>
            </div>
        </div>

        <div class="service-card" style="padding: 0; overflow: hidden; text-align: left; border-radius: 20px; border: 1px solid #f1f5f9;">
            <img src="https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?auto=format&fit=crop&w=600&q=80" alt="Home Visit" style="width: 100%; height: 220px; object-fit: cover;">
            <div style="padding: 2rem;">
                <h3 style="color: #2563eb; margin-bottom: 0.8rem; font-size: 1.3rem;">VVIP Home Health</h3>
                <p style="color: #64748b; font-size: 0.95rem;">Premium healthcare at your doorstep. We provide comprehensive consultations and laboratory sampling in your residence.</p>
            </div>
        </div>

        <div class="service-card" style="padding: 0; overflow: hidden; text-align: left; border-radius: 20px; border: 1px solid #f1f5f9;">
            <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=600&q=80" alt="Medical Specialists" style="width: 100%; height: 220px; object-fit: cover;">
            <div style="padding: 2rem;">
                <h3 style="color: #2563eb; margin-bottom: 0.8rem; font-size: 1.3rem;">Clinical Specialties</h3>
                <p style="color: #64748b; font-size: 0.95rem;">Integrated departments covering Dentistry, Optometry, Gynecology, and Pediatrics under one digital roof.</p>
            </div>
        </div>
    </div>
</section>

<section style="background: #f8fafc; padding: 7rem 5%;">
    <div style="max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 60px; align-items: center;">
        <div>
            <span style="color: #2563eb; font-weight: 800; letter-spacing: 2px; font-size: 0.85rem; text-transform: uppercase;">Excellence in Care</span>
            <h2 style="font-size: 3rem; color: #0f172a; margin: 1rem 0 1.5rem; line-height: 1.1; letter-spacing: -1px;">The GoldByte <span style="color: #2563eb;">Standard</span></h2>
            <p style="color: #64748b; line-height: 1.8; margin-bottom: 2rem; font-size: 1.1rem;">We are setting a new benchmark for medical clinics in West Africa. By combining traditional medical ethics with 21st-century technology, we ensure every patient receives world-class attention.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 12px; color: #1e293b; font-weight: 600;">
                    <div style="width: 10px; height: 10px; background: #2563eb; border-radius: 50%;"></div> Encrypted Health Records
                </div>
                <div style="display: flex; align-items: center; gap: 12px; color: #1e293b; font-weight: 600;">
                    <div style="width: 10px; height: 10px; background: #2563eb; border-radius: 50%;"></div> Real-time Notifications
                </div>
                <div style="display: flex; align-items: center; gap: 12px; color: #1e293b; font-weight: 600;">
                    <div style="width: 10px; height: 10px; background: #2563eb; border-radius: 50%;"></div> Verified Practitioners
                </div>
                <div style="display: flex; align-items: center; gap: 12px; color: #1e293b; font-weight: 600;">
                    <div style="width: 10px; height: 10px; background: #2563eb; border-radius: 50%;"></div> Transparent Billing
                </div>
            </div>
        </div>
        
        <div style="background: #ffffff; padding: 4rem 3rem; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08); text-align: center; border: 1px solid #e2e8f0;">
            <?php if(isset($_SESSION['user_id'])): ?>
                <h3 style="color: #0f172a; margin-bottom: 1rem; font-size: 1.6rem; font-weight: 700;">Account Active</h3>
                <p style="color: #64748b; margin-bottom: 2.5rem; line-height: 1.6;">You are logged in as <strong><?php echo $_SESSION['name']; ?></strong>.</p>
                <a href="patient_dashboard.php" class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 1rem;">Go to Dashboard</a>
            <?php else: ?>
                <h3 style="color: #0f172a; margin-bottom: 1rem; font-size: 1.6rem; font-weight: 700;">Join Our Community</h3>
                <p style="color: #64748b; margin-bottom: 2.5rem; line-height: 1.6;">Secure your health with Winneba's most advanced clinical system.</p>
                <a href="add.php" class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 1rem;">Register as Patient</a>
                <a href="add_doctor.php" style="display: block; margin-top: 2rem; color: #2563eb; text-decoration: none; font-size: 0.95rem; font-weight: 700;">Medical Staff Application &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>