<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// 1. SECURITY: Only Admins can enter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// 2. GLOBAL SEARCH & AUTO-TAB LOGIC
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$requested_tab = isset($_GET['tab']) ? $_GET['tab'] : 'doctors';

// If searching, auto-switch tab to find the user regardless of current view
if (!empty($search)) {
    $find_role = $conn->query("SELECT role FROM users WHERE (first_name LIKE '%$search%' OR surname LIKE '%$search%' OR email LIKE '%$search%') LIMIT 1");
    if ($find_role->num_rows > 0) {
        $found_user = $find_role->fetch_assoc();
        $tab = ($found_user['role'] == 'Doctor') ? 'doctors' : 'patients';
    } else {
        $tab = $requested_tab; 
    }
} else {
    $tab = $requested_tab;
}

$search_filter = "";
if (!empty($search)) {
    $search_filter = " AND (u.first_name LIKE '%$search%' OR u.surname LIKE '%$search%' OR u.email LIKE '%$search%')";
}

// 3. FETCH USERS BASED ON RESOLVED TAB
if ($tab == 'patients') {
    $user_query = "SELECT u.* FROM users u WHERE u.role = 'Patient' $search_filter ORDER BY u.id DESC";
} else {
    $user_query = "SELECT u.*, d.specialty_id, d.license_number, d.cv_path, d.bio, s.name as specialty_name 
                   FROM users u 
                   JOIN doctors d ON u.id = d.user_id 
                   JOIN specialties s ON d.specialty_id = s.id 
                   WHERE u.role = 'Doctor' $search_filter ORDER BY u.id DESC";
}
$user_result = $conn->query($user_query);

// 4. FETCH ANALYTICS
$total_patients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Patient'")->fetch_assoc()['count'];
$total_doctors = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Doctor'")->fetch_assoc()['count'];
$pending_appts = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_fee) as total FROM appointments WHERE status = 'Completed'")->fetch_assoc()['total'] ?? 0;
?>

<div class="container" style="margin-top: 2rem;">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #2563eb;">
            <p style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Total Revenue</p>
            <h3 style="margin: 5px 0; color: #0f172a;">GHS <?php echo number_format($total_revenue, 2); ?></h3>
        </div>
        <div style="background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #10b981;">
            <p style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Active Doctors</p>
            <h3 style="margin: 5px 0; color: #0f172a;"><?php echo $total_doctors; ?></h3>
        </div>
        <div style="background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #f59e0b;">
            <p style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Total Patients</p>
            <h3 style="margin: 5px 0; color: #0f172a;"><?php echo $total_patients; ?></h3>
        </div>
        <div style="background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #ef4444;">
            <p style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Pending Visits</p>
            <h3 style="margin: 5px 0; color: #0f172a;"><?php echo $pending_appts; ?></h3>
        </div>
    </div>

    <div style="background: white; border-radius: 24px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 20px;">
            <h3 style="color: #0f172a; margin: 0;">User Management</h3>
            
            <div style="display: flex; gap: 15px; align-items: center;">
                <form method="GET" style="display: flex; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 5px 10px;">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name or email..." style="border: none; background: transparent; padding: 8px; outline: none; font-size: 0.85rem; width: 200px;">
                    <button type="submit" style="background: #0f172a; color: white; border: none; border-radius: 8px; padding: 5px 15px; cursor: pointer; font-size: 0.8rem;">Search All</button>
                </form>

                <div style="background: #f1f5f9; padding: 5px; border-radius: 12px; display: flex; gap: 5px;">
                    <a href="?tab=doctors" style="text-decoration: none; padding: 8px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; <?php echo ($tab == 'doctors') ? 'background: white; color: #2563eb; box-shadow: 0 2px 5px rgba(0,0,0,0.1);' : 'color: #64748b;'; ?>">Doctors</a>
                    <a href="?tab=patients" style="text-decoration: none; padding: 8px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; <?php echo ($tab == 'patients') ? 'background: white; color: #2563eb; box-shadow: 0 2px 5px rgba(0,0,0,0.1);' : 'color: #64748b;'; ?>">Patients</a>
                </div>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid #f1f5f9;">
                    <th style="padding: 1rem;">User Info</th>
                    <th style="padding: 1rem;"><?php echo ($tab == 'doctors') ? 'Specialty / Credentials' : 'Contact'; ?></th>
                    <th style="padding: 1rem;">Status</th>
                    <th style="padding: 1rem; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($user_result->num_rows > 0): ?>
                    <?php while ($row = $user_result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 1.2rem 1rem;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    
                                    <div style="width: 45px; height: 45px; border-radius: 12px; background: <?php echo ($tab == 'doctors') ? '#eff6ff' : '#f1f5f9'; ?>; display: flex; align-items: center; justify-content: center; overflow: hidden; font-weight: 800; color: #2563eb; border: 1px solid #e2e8f0;">
                                        <?php if(!empty($row['profile_pic']) && file_exists($row['profile_pic'])): ?>
                                            <img src="<?php echo $row['profile_pic']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <span style="font-size: 0.9rem;"><?php echo strtoupper($row['first_name'][0]); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></div>
                                        <div style="color: #64748b; font-size: 0.8rem;"><?php echo $row['email']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1.2rem 1rem;">
                                <?php if ($tab == 'doctors'): ?>
                                    <span style="display: block; font-weight: 600; color: #2563eb;"><?php echo $row['specialty_name']; ?></span>
                                    <div style="display: flex; gap: 10px; margin-top: 4px;">
                                        <a href="<?php echo $row['cv_path']; ?>" target="_blank" style="font-size: 0.7rem; color: #64748b; text-decoration: underline;">📄 CV</a>
                                        <span style="color: #e2e8f0;">|</span>
                                        <a href="javascript:void(0)" onclick="showBio('<?php echo addslashes($row['bio']); ?>', '<?php echo $row['first_name']; ?>')" style="font-size: 0.7rem; color: #64748b; text-decoration: underline;">📝 Bio</a>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size: 0.85rem; color: #475569;"><?php echo $row['phone_number']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1.2rem 1rem;">
                                <?php 
                                    $status_color = '#166534'; $bg_color = '#f0fdf4';
                                    if($row['status'] == 'Suspended') { $status_color = '#991b1b'; $bg_color = '#fee2e2'; }
                                    if($row['status'] == 'Pending') { $status_color = '#92400e'; $bg_color = '#fef3c7'; }
                                ?>
                                <span style="background: <?php echo $bg_color; ?>; color: <?php echo $status_color; ?>; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1.2rem 1rem;">
                                <div style="display: flex; gap: 12px; justify-content: center; align-items: center; font-size: 0.75rem; font-weight: 800;">
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" style="color: #2563eb; text-decoration: none;">EDIT</a>
                                    <span style="color: #e2e8f0;">|</span>
                                    <a href="toggle_status.php?id=<?php echo $row['id']; ?>&current=<?php echo $row['status']; ?>&tab=<?php echo $tab; ?>" 
                                       style="text-decoration: none; color: <?php echo ($row['status'] == 'Active') ? '#ef4444' : '#10b981'; ?>;">
                                       <?php 
                                            if($row['status'] == 'Pending') echo "APPROVE";
                                            else echo ($row['status'] == 'Active') ? "SUSPEND" : "ACTIVATE";
                                       ?>
                                    </a>
                                    <span style="color: #e2e8f0;">|</span>
                                    <a href="delete_user.php?id=<?php echo $row['id']; ?>&tab=<?php echo $tab; ?>" 
                                       onclick="return confirm('🚨 DANGER: Permanently delete user?')" 
                                       style="color: #94a3b8; text-decoration: none;">DELETE</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 4rem; color: #94a3b8;">
                            No users found matching your search.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="bioModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2.5rem; border-radius:24px; max-width:550px; width:90%; position:relative; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
        <h4 id="bioName" style="margin-top:0; color:#0f172a; font-size: 1.2rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Doctor Bio</h4>
        <p id="bioText" style="color:#475569; font-size:0.95rem; line-height:1.7; white-space:pre-wrap; margin: 20px 0; max-height: 300px; overflow-y: auto;"></p>
        <button onclick="closeBio()" style="width:100%; background:#0f172a; color:white; border:none; padding:12px; border-radius:12px; cursor:pointer; font-weight: 700;">Close Preview</button>
    </div>
</div>

<script>
function showBio(text, name) {
    document.getElementById('bioText').innerText = text;
    document.getElementById('bioName').innerText = "About Dr. " + name;
    document.getElementById('bioModal').style.display = "flex";
}
function closeBio() {
    document.getElementById('bioModal').style.display = "none";
}
window.onclick = function(event) {
    let modal = document.getElementById('bioModal');
    if (event.target == modal) {
        closeBio();
    }
}
</script>

<?php include 'includes/footer.php'; ?>