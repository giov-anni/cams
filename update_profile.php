<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// SECURITY: Ensure only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// 1. Handle the Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $profile_pic = $_POST['current_pic']; 

    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "uploads/profiles/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_name = $_FILES['profile_pic']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = array("jpg", "jpeg", "png");

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = "profile_" . $user_id . "_" . time() . "." . $file_ext;
            $target_path = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
                $profile_pic = $new_file_name;
            } else {
                $error_msg = "Failed to upload image.";
            }
        } else {
            $error_msg = "Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($error_msg)) {
        $sql = "UPDATE users SET 
                first_name = '$first_name', 
                surname = '$surname', 
                email = '$email', 
                phone_number = '$phone', 
                profile_pic = '$profile_pic' 
                WHERE id = '$user_id'";
        
        if ($conn->query($sql)) {
            $success_msg = "Profile updated successfully! Redirecting to dashboard...";
            // Meta refresh back to dashboard after 2 seconds
            header("refresh:2;url=patient_dashboard.php");
        } else {
            $error_msg = "Database Error: " . $conn->error;
        }
    }
}

// 2. Fetch the latest user data
$res = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user = $res->fetch_assoc();
?>

<div class="container">
    <div class="form-wrapper" style="max-width: 650px;">
        <div class="form-header">
            <h2 style="color: #0f172a;">Personal Details Vault</h2>
            <p>Update your contact information and profile identity.</p>
        </div>

        <?php if($success_msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: center; font-weight: 600; border: 1px solid #bbf7d0;">
                ✅ <?php echo $success_msg; ?>
                <div style="margin-top: 10px; font-weight: 400; font-size: 0.8rem;">Taking you back in <span id="timer">1</span>s...</div>
            </div>
            <script>
                let timeLeft = 2;
                let timerElement = document.getElementById('timer');
                setInterval(() => {
                    if(timeLeft > 0) {
                        timeLeft--;
                        timerElement.innerHTML = timeLeft;
                    }
                }, 1000);
            </script>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-weight: 500;">
                ❌ <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="current_pic" value="<?php echo $user['profile_pic']; ?>">

            <div style="text-align: center; margin-bottom: 2.5rem;">
                <div style="position: relative; display: inline-block;">
                    <img src="<?php echo (!empty($user['profile_pic']) && file_exists("uploads/profiles/".$user['profile_pic'])) ? "uploads/profiles/".$user['profile_pic'] : "https://ui-avatars.com/api/?name=".urlencode($user['first_name']."+".$user['surname'])."&background=2563eb&color=fff"; ?>" 
                         id="preview"
                         style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #eff6ff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                    
                    <label for="file_upload" style="position: absolute; bottom: 5px; right: 5px; background: #2563eb; color: white; padding: 8px; border-radius: 50%; cursor: pointer; border: 2px solid white; display: flex; align-items: center; justify-content: center;">
                        📸
                    </label>
                    <input type="file" id="file_upload" name="profile_pic" accept="image/*" style="display: none;" onchange="previewImage(this)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Surname</label>
                    <input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary" style="flex: 2;">Save All Changes</button>
                <a href="patient_dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>