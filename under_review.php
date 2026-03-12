<?php
session_start();
// We don't include header.php if it contains navigation links the pending user shouldn't see yet.
// Instead, let's give them a clean, focused page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Review | GoldByte CAMS</title>
    <link rel="stylesheet" href="assets/style.css"> </head>
<body style="background: #f8fafc; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">

    <div style="max-width: 550px; width: 90%; background: white; padding: 3.5rem 2.5rem; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); text-align: center; border: 1px solid #e2e8f0;">
        
        <div style="background: #fff7ed; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 2px solid #ffedd5;">
            <span style="font-size: 3rem;">⏳</span>
        </div>

        <h2 style="color: #0f172a; font-size: 1.8rem; margin-bottom: 1rem;">Application Under Review</h2>
        
        <p style="color: #64748b; line-height: 1.7; margin-bottom: 2rem; font-size: 1rem;">
            Hello, <strong>Dr. <?php echo htmlspecialchars($_SESSION['name'] ?? 'Practioner'); ?></strong>. 
            Your application to join the GoldByte medical network has been received. 
            Our administrators are currently verifying your <strong>Medical License</strong> and <strong>CV</strong>.
        </p>

        <div style="background: #f1f5f9; padding: 1.2rem; border-radius: 15px; margin-bottom: 2.5rem;">
            <p style="margin: 0; color: #475569; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                Verification Status: <span style="color: #f59e0b;">Pending</span>
            </p>
        </div>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 2rem;">
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 1.5rem;">You will receive full dashboard access once approved.</p>
            <a href="logout.php" style="color: #2563eb; text-decoration: none; font-weight: 700; font-size: 0.9rem; border: 2px solid #2563eb; padding: 10px 25px; border-radius: 10px; transition: 0.3s;">
                Logout & Exit
            </a>
        </div>
    </div>

</body>
</html>