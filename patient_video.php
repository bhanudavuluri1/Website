<?php
/**
 * Patient Video Consultation Room
 */
require_once 'config/database.php';

if (!isset($_GET['room']) || empty($_GET['room'])) {
    die("Invalid room link.");
}

$room_id = trim($_GET['room']);

// Validate room ID format (e.g. SPC-12345678-1234)
if (!preg_match('/^SPC-[a-z0-9]+-\d{4}$/i', $room_id)) {
    die("Invalid room format.");
}

// Fetch appointment details securely
$apt = executeQuery("SELECT * FROM appointments WHERE video_room_id = ?", [$room_id]);

if (empty($apt)) {
    die("Consultation room not found.");
}

$appointment = $apt[0];
$status = $appointment['video_status']; // 'waiting', 'active', 'ended'
$patient_name = $appointment['patient_name'];

// AJAX Endpoint for Checking Status (Polling)
if (isset($_GET['check_status'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Consultation - Skin Perfect Clinic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0F172A; color: white; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .header { background: #1E293B; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        .brand { font-size: 1.2rem; font-weight: bold; color: #38BDF8; display: flex; align-items: center; gap: 10px; }
        
        /* Waiting Room Styles */
        .waiting-room { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px; }
        .spinner-container { position: relative; width: 120px; height: 120px; margin-bottom: 30px; }
        .spinner { width: 100%; height: 100%; border: 4px solid #334155; border-top-color: #38BDF8; border-radius: 50%; animation: spin 1s linear infinite; }
        .user-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: #94A3B8; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        h1 { margin: 0 0 10px 0; font-size: 2rem; }
        p.subtitle { color: #94A3B8; font-size: 1.1rem; max-width: 500px; line-height: 1.5; margin: 0 auto 30px auto; }
        
        .info-card { background: #1E293B; border: 1px solid #334155; padding: 20px; border-radius: 10px; text-align: left; width: 100%; max-width: 400px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #334155; }
        .info-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .info-label { color: #94A3B8; font-size: 0.9rem; }
        .info-value { font-weight: 600; color: #F8FAFC; }
        
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-badge.waiting { background: rgba(245, 158, 11, 0.2); color: #FCD34D; }
        
        /* Video Container Styles */
        #jitsi-container { flex: 1; width: 100%; display: none; background: #000; }
        
        /* Ended Screen */
        .ended-screen { flex: 1; display: none; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px; }
        .ended-icon { font-size: 80px; color: #10B981; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">
            <i class="fa-solid fa-leaf"></i> Skin Perfect Clinic
        </div>
        <div>
            <span style="color: #94A3B8; font-size: 0.9rem;">Patient:</span> 
            <strong><?php echo htmlspecialchars($patient_name); ?></strong>
        </div>
    </div>

    <!-- 1. Waiting Room Overlay -->
    <div id="waitingRoom" class="waiting-room" style="<?php echo $status === 'waiting' ? 'display: flex;' : 'display: none;'; ?>">
        <div class="spinner-container">
            <div class="spinner"></div>
            <i class="fa-solid fa-user-doctor user-icon"></i>
        </div>
        <h1>Waiting for Doctor</h1>
        <p class="subtitle">Please wait here. The doctor will admit you to the consultation shortly.</p>
        
        <div class="info-card">
            <div class="info-row">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge waiting">
                        <i class="fa-solid fa-clock"></i> In Waiting Room
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Appointment Time</div>
                <div class="info-value"><?php echo htmlspecialchars($appointment['appointment_time']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Scheduled Date</div>
                <div class="info-value"><?php echo htmlspecialchars($appointment['appointment_date']); ?></div>
            </div>
        </div>
    </div>

    <!-- 2. Jitsi Video Container -->
    <div id="jitsi-container" style="<?php echo $status === 'active' ? 'display: block;' : 'display: none;'; ?>"></div>

    <!-- 3. Ended Screen -->
    <div id="endedScreen" class="ended-screen" style="<?php echo $status === 'ended' ? 'display: flex;' : 'display: none;'; ?>">
        <i class="fa-solid fa-circle-check ended-icon"></i>
        <h1>Consultation Ended</h1>
        <p class="subtitle" style="margin-bottom: 10px;">The doctor has ended this video consultation.</p>
        <p class="subtitle">Thank you for choosing Skin Perfect Clinic.</p>
        <a href="index.php" style="background: #38BDF8; color: #0F172A; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; margin-top: 20px;">Return to Home</a>
    </div>

    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        const roomId = "<?php echo htmlspecialchars($room_id); ?>";
        const patientName = "<?php echo htmlspecialchars($patient_name); ?>";
        let currentStatus = "<?php echo $status; ?>";
        let jitsiApi = null;
        let pollInterval = null;

        function initializeJitsi() {
            if (jitsiApi) return; // Prevent double initialization
            
            const domain = 'meet.jit.si';
            const options = {
                roomName: roomId,
                height: '100%',
                parentNode: document.getElementById('jitsi-container'),
                userInfo: {
                    displayName: patientName
                },
                configOverwrite: {
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    disableDeepLinking: true
                },
                interfaceConfigOverwrite: {
                    // Hide settings/invite buttons from patient
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'hangup', 'profile', 'chat', 'settings', 'videoquality'
                    ],
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    SHOW_PROMOTIONAL_CLOSE_PAGE: false
                }
            };
            
            jitsiApi = new JitsiMeetExternalAPI(domain, options);
            
            jitsiApi.addEventListener('videoConferenceLeft', () => {
                // Patient hung up manually
                document.getElementById('jitsi-container').style.display = 'none';
                document.getElementById('endedScreen').style.display = 'flex';
                // Note: We don't forcefully mark the DB as ended if patient leaves early, 
                // in case they need to refresh and rejoin.
            });
        }

        function checkStatus() {
            // Poll the backend to check if doctor started or ended the call
            fetch(`patient_video.php?room=${encodeURIComponent(roomId)}&check_status=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.status !== currentStatus) {
                        currentStatus = data.status;
                        handleStatusChange(currentStatus);
                    }
                })
                .catch(err => console.error("Error polling status:", err));
        }

        function handleStatusChange(status) {
            document.getElementById('waitingRoom').style.display = 'none';
            document.getElementById('jitsi-container').style.display = 'none';
            document.getElementById('endedScreen').style.display = 'none';

            if (status === 'waiting') {
                document.getElementById('waitingRoom').style.display = 'flex';
            } else if (status === 'active') {
                document.getElementById('jitsi-container').style.display = 'block';
                initializeJitsi();
            } else if (status === 'ended') {
                document.getElementById('endedScreen').style.display = 'flex';
                if (jitsiApi) jitsiApi.dispose(); // Kill iframe
                if (pollInterval) clearInterval(pollInterval);
            }
        }

        // Initialize state based on server render
        if (currentStatus === 'active') {
            initializeJitsi();
        }

        // Poll every 3 seconds if waiting or active (to catch if doctor ends it)
        if (currentStatus !== 'ended') {
            pollInterval = setInterval(checkStatus, 3000);
        }
    </script>
</body>
</html>
