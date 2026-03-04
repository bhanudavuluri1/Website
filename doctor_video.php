<?php
/**
 * Doctor Video Consultation Room (Host View)
 */
session_start();
require_once 'config/database.php';

// Check if doctor or admin is logged in
$is_doctor = isset($_SESSION['doctor_logged_in']);
$is_admin = isset($_SESSION['admin_logged_in']);

if (!$is_doctor && !$is_admin) {
    die("Unauthorized access. Please log in.");
}

if (!isset($_GET['room']) || empty($_GET['room'])) {
    die("Invalid room link.");
}

$room_id = trim($_GET['room']);

// Validate room ID format
if (!preg_match('/^SPC-[a-z0-9]+-\d{4}$/i', $room_id)) {
    die("Invalid room format.");
}

// Fetch appointment details
$apt = executeQuery("SELECT * FROM appointments WHERE video_room_id = ?", [$room_id]);

if (empty($apt)) {
    die("Consultation room not found.");
}

$appointment = $apt[0];
$patient_name = $appointment['patient_name'];
$host_name = $is_doctor ? $_SESSION['doctor_username'] : 'Admin';

// Update database status to 'active' as soon as the doctor visits the page
// This will tell the patient's waiting room that the doctor is ready.
if ($appointment['video_status'] === 'waiting') {
    executeQuery("UPDATE appointments SET video_status = 'active' WHERE video_room_id = ?", [$room_id]);
    $appointment['video_status'] = 'active'; // update local variable
}

// Handle AJAX Request to end meeting
if (isset($_GET['action']) && $_GET['action'] === 'end_meeting') {
    executeQuery("UPDATE appointments SET video_status = 'ended', status = 'completed' WHERE video_room_id = ?", [$room_id]);
    // Also mark appointment completed in the primary status column
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Meeting Room - Skin Perfect Clinic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0F172A; color: white; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .header { background: #059669; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #047857; }
        .brand { font-size: 1.2rem; font-weight: bold; color: white; display: flex; align-items: center; gap: 10px; }
        
        /* Video Container Styles */
        #jitsi-container { flex: 1; width: 100%; background: #000; }
        
        .controls { padding: 10px; background: #1E293B; display: flex; justify-content: center; gap: 15px; border-top: 1px solid #334155; }
        .btn { padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
        .btn-danger { background: #EF4444; color: white; }
        .btn-danger:hover { background: #DC2626; }
        .btn-primary { background: #3B82F6; color: white; text-decoration: none; }
        .btn-primary:hover { background: #2563EB; }
        
        /* Ended Screen */
        .ended-screen { flex: 1; display: none; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px; }
        .ended-icon { font-size: 80px; color: #10B981; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="header" id="topHeader">
        <div class="brand">
            <i class="fa-solid fa-user-doctor"></i> Host: <?php echo htmlspecialchars($host_name); ?>
        </div>
        <div>
            <span style="color: #A7F3D0; font-size: 0.9rem;">Consultation with:</span> 
            <strong><?php echo htmlspecialchars($patient_name); ?></strong>
        </div>
    </div>

    <!-- 1. Jitsi Video Container -->
    <div id="jitsi-container"></div>
    
    <!-- 2. Controls Panel -->
    <div class="controls" id="btnControls">
        <button id="endMeetingBtn" class="btn btn-danger" onclick="endMeetingSession()">
            <i class="fa-solid fa-phone-slash"></i> End Consultation for Everyone
        </button>
        <a href="admin/medical_record_form.php?appointment_id=<?php echo $appointment['id']; ?>" target="_blank" class="btn btn-primary">
            <i class="fa-solid fa-notes-medical"></i> Write Prescription
        </a>
    </div>

    <!-- 3. Ended Screen -->
    <div id="endedScreen" class="ended-screen">
        <i class="fa-solid fa-check-double ended-icon"></i>
        <h1>Consultation Successfully Ended</h1>
        <p style="color: #94A3B8; margin-bottom: 20px;">The session has been terminated and the patient has been disconnected.</p>
        <div style="display: flex; gap: 15px;">
            <a href="admin/medical_record_form.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-primary">Write Record</a>
            <a href="<?php echo $is_doctor ? 'admin/doctor_appointments.php' : 'admin/appointments.php'; ?>" class="btn" style="background: #475569; color: white; text-decoration: none;">Return to Dashboard</a>
        </div>
    </div>

    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        const roomId = "<?php echo htmlspecialchars($room_id); ?>";
        const hostName = "<?php echo htmlspecialchars($host_name); ?>";
        
        // Setup Jitsi Host
        const domain = 'meet.jit.si';
        const options = {
            roomName: roomId,
            height: '100%',
            parentNode: document.getElementById('jitsi-container'),
            userInfo: {
                displayName: "Dr. " + hostName
            },
            configOverwrite: {
                prejoinPageEnabled: false,          // Skip prejoin
                startWithAudioMuted: false,         // Audio on
                startWithVideoMuted: false,         // Video on
                disableDeepLinking: true            // Don't ask to open app
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_PROMOTIONAL_CLOSE_PAGE: false
            }
        };
        
        const api = new JitsiMeetExternalAPI(domain, options);
        
        // Automatically hide iframe and show "ended" if doctor leaves via Jitsi UI
        api.addEventListener('videoConferenceLeft', () => {
            endMeetingSession(); // Call our backend to end it for patient too
        });

        function endMeetingSession() {
            if (!confirm("Are you sure you want to end this consultation? This will disconnect the patient and mark the appointment as completed.")) return;

            // 1. Tell Jitsi to hangup
            try {
                api.executeCommand('hangup');
                api.dispose();
            } catch(e) {}
            
            // 2. Hide Video + Header/Controls, Show Ended Screen
            document.getElementById('jitsi-container').style.display = 'none';
            document.getElementById('topHeader').style.display = 'none';
            document.getElementById('btnControls').style.display = 'none';
            document.getElementById('endedScreen').style.display = 'flex';

            // 3. Ping Backend to update `video_status` = 'ended'
            fetch(`doctor_video.php?room=${encodeURIComponent(roomId)}&action=end_meeting`)
                .then(res => res.json())
                .then(data => console.log('Backend sync successful.'))
                .catch(err => console.error(err));
        }
    </script>
</body>
</html>
