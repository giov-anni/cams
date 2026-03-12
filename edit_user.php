<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$user_id = $conn->real_escape_string($_GET['id']);
$success = false;

// Fetch User Data
$query = "SELECT * FROM users WHERE id = '$user_id'";
$res = $conn->query($query);
$user = $res->fetch_assoc();

// If Doctor, fetch additional doctor info
$doc_info = null;
if ($user['role'] == 'Doctor') {
    $doc_res = $conn->query("SELECT * FROM doctors WHERE user_id = '$user_id'");
    $doc_info = $doc_res->fetch_assoc();
}

// Process Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $conn->real_escape_string($_POST['first_name']);
    $sname = $conn->real_escape_string($_POST['surname']);
    $phone = $conn->real_escape_string($_POST['phone_number']);
    
    $update_user = "UPDATE users SET first_name='$fname', surname='$sname', phone_number='$phone' WHERE id='$user_id'";
    $conn->query($update_user);

    if ($user['role'] == 'Doctor') {
        $license = $conn->real_escape_string($_POST['license_number']);
        $conn->query("UPDATE doctors SET license_number='$license' WHERE user_id='$user_id'");
    }
    
    $success = true;
    header("refresh:2;url=admin_dashboard.php");
}
?>

<div class="container" style="margin-top: 3rem;">
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        
        <?php if($success): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 2rem;">
                ✅ User details updated successfully! Redirecting...
            </div>
        <?php endif; ?>

        <h2 style="color: #0f172a; margin-bottom: 1.5rem;">Edit <?php echo $user['role']; ?></h2>
        
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #475569;">First Name</label>
                    <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #475569;">Surname</label>
                    <input type="text" name="surname" value="<?php echo $user['surname']; ?>" required style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; font-size: 0.9rem; color: #475569;">Phone Number</label>
                <input type="text" name="phone_number" value="<?php echo $user['phone_number']; ?>" required style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
            </div>

            <?php if ($user['role'] == 'Doctor'): ?>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #475569;">Medical License</label>
                    <input type="text" name="license_number" value="<?php echo $doc_info['license_number']; ?>" required style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #cbd5e1;">
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 15px; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 1rem; border-radius: 12px;">Save Changes</button>
                <a href="admin_dashboard.php" style="flex: 1; text-align: center; padding: 1rem; background: #f1f5f9; color: #64748b; text-decoration: none; border-radius: 12px; font-weight: 600;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>