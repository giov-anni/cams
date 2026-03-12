<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// 1. SECURITY: Only Doctors can access this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// 2. FETCH CURRENT PROFILE DATA
$query = "SELECT u.profile_pic, d.bio, d.license_number, s.name as specialty_name 
          FROM users u 
          JOIN doctors d ON u.id = d.user_id 
          JOIN specialties s ON d.specialty_id = s.id
          WHERE u.id = '$user_id'";
$res = $conn->query($query);
$data = $res->fetch_assoc();

// 3. PROCESS UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bio = $conn->real_escape_string($_POST['bio']);
    
    // Start Transaction
    $conn->begin_transaction();

    try {
        // Handle Profile Picture Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "uploads/profiles/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = "doc_" . $user_id . "_" . time() . "." . $file_ext;
                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    // Update user table with new pic path
                    $conn->query("UPDATE users SET profile_pic = '$target_file' WHERE id = '$user_id'");
                }
            } else {
                throw new Exception("Only JPG, JPEG, and PNG files are allowed.");
            }
        }

        // Update Bio
        $conn->query("UPDATE doctors SET bio = '$bio' WHERE user_id = '$user_id'");
        
        $conn->commit();
        $success_msg = "Your professional profile has been updated successfully!";
        // Refresh data for display
        $res = $conn->query($query);
        $data = $res->fetch_assoc();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = $e->getMessage();
    }
}
?>

<div class="container" style="max-width: 900px; margin-top: 3rem; margin-bottom: 5rem;">
    <div style="background: white; padding: 3rem; border-radius: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem;">
            <div>
                <h2 style="color: #0f172a; margin: 0;">Doctor Profile</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 5px;">This information is visible to Patients during the booking process.</p>
            </div>
            <a href="doctor_dashboard.php" style="text-decoration: none; color: #64748b; font-weight: 600; font-size: 0.9rem;">&larr; Back to Dashboard</a>
        </div>

        <?php if($success_msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #bbf7d0; text-align: center; font-weight: 500;">
                ✅ <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div style="background: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fecaca; text-align: center; font-weight: 500;">
                ❌ <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 250px 1fr; gap: 40px;">
                
                <div style="text-align: center;">
                    <div style="position: relative; display: inline-block;">
                        <img src="<?php echo !empty($data['profile_pic']) ? $data['profile_pic'] : 'assets/default_avatar.png'; ?>" 
                             style="width: 200px; height: 200px; border-radius: 30px; object-fit: cover; border: 6px solid #f8fafc; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 10px;">Change Profile Photo</label>
                        <input type="file" name="profile_image" accept="image/*" style="font-size: 0.8rem; width: 100%; color: #64748b;">
                        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 10px; line-height: 1.4;">Allowed: JPG, PNG.<br>Max size: 2MB.</p>
                    </div>
                </div>

                <div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 2rem;">
                        <div class="form-group">
                            <label style="display: block; font-weight: 700; color: #475569; font-size: 0.85rem; margin-bottom: 8px;">Specialty</label>
                            <input type="text" value="<?php echo $data['specialty_name']; ?>" disabled style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; color: #94a3b8; font-weight: 600;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-weight: 700; color: #475569; font-size: 0.85rem; margin-bottom: 8px;">MDC License</label>
                            <input type="text" value="<?php echo $data['license_number']; ?>" disabled style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; color: #94a3b8; font-weight: 600;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 700; color: #475569; font-size: 0.85rem; margin-bottom: 10px;">Professional Biography</label>
                        <textarea name="bio" placeholder="Describe your experience, expertise, and patient approach..." 
                                  style="width: 100%; height: 220px; padding: 1rem; border-radius: 15px; border: 1px solid #cbd5e1; font-family: inherit; line-height: 1.6; outline-color: #2563eb;"><?php echo htmlspecialchars($data['bio']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 12px; font-weight: 700; background: #0f172a; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.3);">
                        Save Profile Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>