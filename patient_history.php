<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$history_result = null;

if (!empty($search)) {
    // UPDATED QUERY: Added home_address and service_type
    $query = "SELECT a.*, u.first_name, u.surname, u.phone_number, s.name as specialty_name 
              FROM appointments a 
              JOIN users u ON a.patient_id = u.id 
              JOIN specialties s ON a.specialty_id = s.id
              WHERE (u.first_name LIKE '%$search%' OR u.surname LIKE '%$search%' OR u.phone_number LIKE '%$search%')
              AND a.status = 'Completed'
              ORDER BY a.appointment_date DESC";
    $history_result = $conn->query($query);
}
?>

<div class="container" style="margin-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h2 style="color: #0f172a;">Patient Medical <span style="color: #2563eb;">Archive</span></h2>
        <p style="color: #64748b;">Search for patients to review past prescriptions, locations, and internal observations.</p>
    </div>

    <div style="background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 3rem;">
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search by name or phone number..." 
                   style="flex-grow: 1; padding: 1rem; border-radius: 12px; border: 1px solid #cbd5e1; outline: none; font-size: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0 2rem; border-radius: 12px; font-weight: 600;">Search Archive</button>
        </form>
    </div>

    <?php if ($history_result && $history_result->num_rows > 0): ?>
        <div style="display: grid; gap: 25px;">
            <?php while ($row = $history_result->fetch_assoc()): ?>
                <div style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);">
                    
                    <div style="background: #f8fafc; padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="margin: 0; color: #0f172a; font-size: 1.2rem;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></h4>
                            <small style="color: #64748b; font-weight: 500;">
                                📅 <?php echo date("M j, Y", strtotime($row['appointment_date'])); ?> | 
                                🩺 <?php echo $row['specialty_name']; ?> | 
                                <span style="color: #2563eb;"><?php echo $row['service_type']; ?></span>
                            </small>
                        </div>
                        <a href="download_prescription.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn-secondary" style="font-size: 0.8rem; padding: 8px 15px; border-radius: 8px; text-decoration: none; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 600; background: white;">
                            📄 Re-download RX
                        </a>
                    </div>

                    <div style="padding: 2rem;">
                        
                        <?php if($row['service_type'] === 'Home-Service' && !empty($row['home_address'])): ?>
                            <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 10px;">
                                <span style="font-size: 1.2rem;">📍</span>
                                <div>
                                    <strong style="color: #9a3412; font-size: 0.8rem; text-transform: uppercase;">Service Location:</strong>
                                    <p style="margin: 0; color: #7c2d12; font-size: 0.95rem;"><?php echo htmlspecialchars($row['home_address']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                            <div>
                                <label style="font-size: 0.7rem; font-weight: 800; color: #2563eb; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 10px;">📜 Prescribed Medication</label>
                                <div style="font-size: 1rem; color: #334155; line-height: 1.6; background: #f1f5f9; padding: 1.2rem; border-radius: 12px;">
                                    <?php echo nl2br(htmlspecialchars($row['prescriptions'])); ?>
                                </div>
                            </div>

                            <div style="border-left: 2px dashed #f1f5f9; padding-left: 40px;">
                                <label style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 10px;">🔒 Private Clinical Notes</label>
                                <div style="font-size: 0.95rem; color: #475569; line-height: 1.6; font-style: italic;">
                                    <?php echo !empty($row['doctor_notes']) ? nl2br(htmlspecialchars($row['doctor_notes'])) : 'No internal notes recorded for this visit.'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php elseif (!empty($search)): ?>
        <div style="text-align: center; padding: 5rem 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
            <h3 style="color: #0f172a;">Patient Not Found</h3>
            <p style="color: #64748b;">No completed medical records found for "<?php echo htmlspecialchars($search); ?>".</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>