<?php
session_start();
// Using absolute paths for reliability
$base_path = __DIR__ . DIRECTORY_SEPARATOR;
include $base_path . 'includes/db_connect.php';
include $base_path . 'includes/header.php';
include $base_path . 'includes/sms_helper.php';

if ($_SESSION['role'] !== 'Admin') { die("Unauthorized."); }

$report_html = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $raw_message = $_POST['message'];
    $target_type = $_POST['target_type'];
    $custom_sender = !empty($_POST['sender_id']) ? $_POST['sender_id'] : "GB-CLINIC";
    $report = [];

    // Helper to process and log responses
    function processSmsResponse($number, $name, $response) {
        $resData = json_decode($response, true);
        // Check for JSON success OR the string "Sent" which Arkesel often returns
        if ((isset($resData['status']) && $resData['status'] == 'success') || stripos($response, 'Sent') !== false) {
            return "✅ <strong>Sent to $number</strong> ($name)";
        } else {
            $reason = $resData['message'] ?? $response ?? 'Connection Timeout';
            return "❌ <strong>Failed for $number</strong> ($name): <span style='color:#ef4444;'>$reason</span>";
        }
    }

    // 1. Target: All Patients in Database
    if ($target_type == 'all_patients') {
        $users = $conn->query("SELECT first_name, phone_number FROM users WHERE role = 'Patient'");
        while ($row = $users->fetch_assoc()) {
            $name = !empty($row['first_name']) ? $row['first_name'] : "Client";
            $personalized_msg = str_replace("{name}", $name, $raw_message);
            $response = sendGoldByteSMS($row['phone_number'], $personalized_msg, $custom_sender);
            $report[] = processSmsResponse($row['phone_number'], $name, $response);
        }
    } 
    
    // 2. Target: CSV File Upload
    elseif ($target_type == 'csv_upload' && isset($_FILES['contact_file'])) {
        $file = $_FILES['contact_file']['tmp_name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            fgetcsv($handle); // Skip header row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $name = !empty($data[0]) ? trim($data[0]) : "Client";
                $number = trim($data[1]);
                if (!empty($number)) {
                    $personalized_msg = str_replace("{name}", $name, $raw_message);
                    $response = sendGoldByteSMS($number, $personalized_msg, $custom_sender);
                    $report[] = processSmsResponse($number, $name, $response);
                }
            }
            fclose($handle);
        } else {
            $error_msg = "Could not open the uploaded CSV file.";
        }
    }

    // 3. Target: Manual Entry (Personalized Format Supported)
    elseif ($target_type == 'manual') {
        $entries = explode(',', $_POST['manual_numbers']);
        foreach ($entries as $entry) {
            $parts = explode(':', $entry);
            $name = (count($parts) == 2) ? trim($parts[0]) : "Client";
            $number = (count($parts) == 2) ? trim($parts[1]) : trim($parts[0]);

            if (!empty($number)) {
                $personalized_msg = str_replace("{name}", $name, $raw_message);
                $response = sendGoldByteSMS($number, $personalized_msg, $custom_sender);
                $report[] = processSmsResponse($number, $name, $response);
            }
        }
    }

    // Format the report for display
    if (!empty($report)) {
        $report_html = "<div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem; max-height: 250px; overflow-y: auto; font-size: 0.85rem; line-height: 1.6;'>";
        $report_html .= "<h4 style='margin-top:0; color: #0f172a;'>Campaign Transmission Report:</h4>";
        $report_html .= implode('<br>', $report);
        $report_html .= "</div>";
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <div style="max-width: 900px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="color: #0f172a; margin: 0;">🚀 GoldByte SMS Hub</h2>
                <p style="color: #64748b; margin-top: 5px;">Broadcast personalized campaigns via Arkesel.</p>
            </div>
            <div style="text-align: right;">
                <span style="background: #eff6ff; color: #2563eb; padding: 8px 15px; border-radius: 10px; font-size: 0.8rem; font-weight: 700;">Admin Mode</span>
            </div>
        </div>

        <?php if($error_msg): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fecaca;">❌ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php echo $report_html; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 1.5rem;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 10px;">Sender ID</label>
                    <input type="text" name="sender_id" placeholder="e.g. GOLDBYTE" maxlength="11" style="width: 100%; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 10px;">Select Audience</label>
                    <select name="target_type" id="target_type" onchange="toggleInputs()" style="width: 100%; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <option value="all_patients">Registered Patients (Database)</option>
                        <option value="csv_upload">Upload CSV File</option>
                        <option value="manual">Manual Entry (Personalized)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="font-weight: 700; display: block; margin-bottom: 10px;">Message Template</label>
                <textarea name="message" rows="5" style="width: 100%; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;" placeholder="Hello {name}, ..."></textarea>
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 5px;">Use <b>{name}</b> to insert names dynamically.</p>
            </div>

            <div id="csv_section" style="display: none; margin-bottom: 1.5rem; background: #f8fafc; padding: 1.5rem; border-radius: 15px; border: 2px dashed #e2e8f0;">
                <label style="font-weight: 700; display: block; margin-bottom: 10px;">Upload Contact CSV</label>
                <input type="file" name="contact_file" accept=".csv">
                <p style="font-size: 0.75rem; color: #64748b; margin-top: 10px;">Format: Column A: Name | Column B: Number</p>
            </div>

            <div id="manual_section" style="display: none; margin-bottom: 1.5rem;">
                <label style="font-weight: 700; display: block; margin-bottom: 10px;">Manual Entries</label>
                <textarea name="manual_numbers" rows="4" style="width: 100%; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;" placeholder="Format: Name:Number, Name:Number (e.g. Albert:0507021555)"></textarea>
                <p style="font-size: 0.75rem; color: #64748b; margin-top: 5px;">Separate different contacts with a comma.</p>
            </div>

            <button type="submit" style="width: 100%; background: #0f172a; color: white; border: none; padding: 1.2rem; border-radius: 12px; font-weight: 700; cursor: pointer;">
                🚀 Blast Campaign
            </button>
        </form>
    </div>
</div>

<script>
function toggleInputs() {
    var type = document.getElementById('target_type').value;
    document.getElementById('csv_section').style.display = (type === 'csv_upload') ? 'block' : 'none';
    document.getElementById('manual_section').style.display = (type === 'manual') ? 'block' : 'none';
}
</script>

<?php include $base_path . 'includes/footer.php'; ?>