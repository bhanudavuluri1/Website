<?php
/**
 * Appointment Email Log Viewer
 * View all appointment email attempts
 */

$log_file = __DIR__ . '/../logs/email_log.txt';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Email Log</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        h1 {
            color: #0EA5E9;
            margin-bottom: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert-info {
            background: #E0F2FE;
            color: #0284C7;
            border: 1px solid #0EA5E9;
        }
        
        .alert-warning {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #F59E0B;
        }
        
        .log-container {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 1.5rem;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 0.9rem;
            color: #334155;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section h2 {
            color: #0284C7;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        button {
            background: #0EA5E9;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        
        button:hover {
            background: #0284C7;
        }
        
        .empty {
            text-align: center;
            padding: 2rem;
            color: #94A3B8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa-solid fa-envelope"></i> Email Log Viewer</h1>
        
        <div class="alert alert-warning">
            <strong>⚠️ Email Issue Detected:</strong> Emails are being logged but may not be sending due to XAMPP configuration. See EMAIL_SETUP.txt for solutions.
        </div>
        
        <div class="section">
            <h2><i class="fa-solid fa-circle-info"></i> Email Configuration Status</h2>
            <div class="log-container">
Current XAMPP Email Settings:
- SMTP Server: <?php echo ini_get('SMTP'); ?>
- SMTP Port: <?php echo ini_get('smtp_port'); ?>
- From Address: <?php echo ini_get('sendmail_from') ?: '(Not configured)'; ?>
- Mail Function: <?php echo function_exists('mail') ? '✅ Available' : '❌ Not Available'; ?>

ISSUE: Default XAMPP config points to localhost:25 which has no mail server.

SOLUTION: Configure SMTP to use:
1. Gmail (smtp.gmail.com:587)
2. Mailtrap.io (free testing)
3. SendGrid (free tier)

See EMAIL_SETUP.txt for detailed instructions.
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fa-solid fa-history"></i> Email Log History</h2>
            <div class="log-container">
<?php
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    if (!empty(trim($log_content))) {
        echo htmlspecialchars($log_content);
    } else {
        echo "Log file exists but is empty. No emails attempted yet.";
    }
} else {
    echo "No log file yet. Book an appointment to generate logs.";
}
?>
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fa-solid fa-cogs"></i> Quick Actions</h2>
            <div class="action-buttons">
                <button onclick="location.href='../my_appointments.php'">View Patient Portal</button>
                <button onclick="location.href='../admin_login.php'">Admin Panel</button>
                <button onclick="location.reload()">Refresh Log</button>
            </div>
        </div>
        
        <div class="alert alert-info">
            <strong>📧 Next Steps:</strong>
            <ol style="margin-left: 1rem; margin-top: 0.5rem;">
                <li>Edit C:\xampp\php\php.ini with email provider settings</li>
                <li>Restart Apache server</li>
                <li>Test by booking a new appointment</li>
                <li>Check this page for email logs</li>
            </ol>
        </div>
    </div>
</body>
</html>
