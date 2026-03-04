<?php
/**
 * API Error Viewer - Debug Appointment Booking Issues
 */
$log_dir = __DIR__ . '/logs';
$debug_log = $log_dir . '/booking_debug.log';
$api_debug_log = $log_dir . '/api_debug.log';

// Create logs directory if it doesn't exist
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Debug Viewer</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #e0e0e0;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #00aced8;
            border-bottom: 2px solid #00aced8;
            padding-bottom: 10px;
        }
        .log-section {
            background: #2d2d2d;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #00aced8;
        }
        .log-content {
            background: #1e1e1e;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #569cd6; }
        .warning { color: #dcdcaa; }
        button {
            background: #00aced8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-family: 'Courier New', monospace;
        }
        button:hover {
            background: #009dd6;
        }
        .button-group {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Booking System Debug Viewer</h1>
        
        <div class="button-group">
            <button onclick="location.reload()">🔄 Refresh</button>
            <button onclick="clearLogs()">🗑️ Clear Logs</button>
            <button onclick="window.location='index.php'">← Back to Website</button>
        </div>

        <div class="log-section">
            <h2>📋 Booking Debug Log</h2>
            <div class="log-content">
                <?php
                if (file_exists($debug_log)) {
                    $content = file_get_contents($debug_log);
                    if (!empty($content)) {
                        echo htmlspecialchars($content);
                    } else {
                        echo '<span class="info">No logs yet. Try booking an appointment to generate logs.</span>';
                    }
                } else {
                    echo '<span class="warning">Debug log not created yet.</span>';
                }
                ?>
            </div>
        </div>

        <div class="log-section">
            <h2>🌐 API Request Log</h2>
            <div class="log-content">
                <?php
                if (file_exists($api_debug_log)) {
                    $content = file_get_contents($api_debug_log);
                    if (!empty($content)) {
                        echo htmlspecialchars($content);
                    } else {
                        echo '<span class="info">No API requests logged yet.</span>';
                    }
                } else {
                    echo '<span class="warning">API log not created yet.</span>';
                }
                ?>
            </div>
        </div>

        <div class="log-section">
            <h2>⚙️ Database Connection Test</h2>
            <div class="log-content">
                <?php
                require_once './config/database.php';
                
                echo "Testing database connection...\n\n";
                
                // Test 1: Check if connection exists
                echo "✓ Connection Object: " . (isset($conn) ? "OK\n" : "FAILED\n");
                
                // Test 2: Check database selection
                $test = executeQuery("SELECT 1");
                echo "✓ Database Query: " . ($test !== false ? "OK\n" : "FAILED\n");
                
                // Test 3: Check tables
                $tables = executeQuery("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", ['skin_perfect_clinic']);
                if ($tables) {
                    echo "✓ Database Tables Found: " . count($tables) . "\n";
                    foreach ($tables as $table) {
                        echo "  - " . $table['TABLE_NAME'] . "\n";
                    }
                } else {
                    echo "✗ No tables found\n";
                }
                
                // Test 4: Check functions
                echo "\n✓ getOrCreatePatient: " . (function_exists('getOrCreatePatient') ? "Available\n" : "Missing\n");
                echo "✓ sendEmailViaSMTP: " . (function_exists('sendEmailViaSMTP') ? "Available\n" : "Missing\n");
                echo "✓ checkExistingBooking: " . (function_exists('checkExistingBooking') ? "Available\n" : "Missing\n");
                
                // Test 5: Check clinic info
                echo "\n--- Clinic Info ---\n";
                $clinic = getResult("SELECT clinic_name, email FROM clinic_info LIMIT 1");
                if ($clinic) {
                    echo "Clinic Name: " . $clinic['clinic_name'] . "\n";
                    echo "Clinic Email: " . $clinic['email'] . "\n";
                } else {
                    echo "No clinic info found\n";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function clearLogs() {
            if (confirm('Clear all logs?')) {
                fetch('api/clear_debug_logs.php')
                    .then(r => r.json())
                    .then(d => {
                        alert(d.message);
                        location.reload();
                    });
            }
        }
    </script>
</body>
</html>
