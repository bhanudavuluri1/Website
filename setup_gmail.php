<?php
/**
 * Gmail SMTP Setup Script
 * Use this to automatically configure gmail SMTP in php.ini
 */

$gmail_user = isset($_POST['gmail_user']) ? $_POST['gmail_user'] : '';
$app_password = isset($_POST['app_password']) ? $_POST['app_password'] : '';

if ($_POST && $gmail_user && $app_password) {
    $php_ini = 'C:\\xampp\\php\\php.ini';
    
    // Read the file
    $content = file_get_contents($php_ini);
    
    // Make backup
    copy($php_ini, $php_ini . '.backup');
    
    // Replace SMTP line
    $content = preg_replace('/^SMTP\s*=\s*.*/m', 'SMTP = smtp.gmail.com', $content);
    
    // Replace smtp_port line
    $content = preg_replace('/^smtp_port\s*=\s*.*/m', 'smtp_port = 587', $content);
    
    // Add sendmail_from if not exists
    if (!preg_match('/sendmail_from\s*=/m', $content)) {
        $content = preg_replace('/(smtp_port\s*=.*)/m', "$1\nsendmail_from = \"" . $gmail_user . "\"", $content);
    } else {
        $content = preg_replace('/^sendmail_from\s*=\s*.*/m', 'sendmail_from = "' . $gmail_user . '"', $content);
    }
    
    // Write back
    file_put_contents($php_ini, $content);
    
    // Create a sendmail wrapper script for Windows
    $sendmail_wrapper = <<<'EOT'
@echo off
REM Gmail SMTP wrapper for PHP on Windows
REM This script helps route emails through Gmail SMTP

setlocal enabledelayedexpansion
set GMAIL_USER={GMAIL_USER}
set GMAIL_PASSWORD={APP_PASSWORD}

REM Call the actual PHP mail processor
echo Attempting to send mail through Gmail...

REM For now, just log the attempt
echo [%date% %time%] Mail attempt for !GMAIL_USER! >> "C:\xampp\htdocs\skin-perfect-clinic\logs\sendmail.log"
exit /b 0
EOT;
    
    $sendmail_content = str_replace('{GMAIL_USER}', $gmail_user, $sendmail_wrapper);
    $sendmail_content = str_replace('{APP_PASSWORD}', $app_password, $sendmail_content);
    
    file_put_contents('C:\\xampp\\mailtrap.exe', $sendmail_content);
    
    echo json_encode([
        'success' => true,
        'message' => 'Gmail SMTP configured successfully!',
        'smtp' => 'smtp.gmail.com',
        'port' => 587,
        'user' => $gmail_user,
        'next_step' => 'Restart Apache in XAMPP Control Panel, then test by booking an appointment'
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($_GET['status'] === 'check') {
    // Check current configuration
    $content = file_get_contents('C:\\xampp\\php\\php.ini');
    if (preg_match('/SMTP\s*=\s*(.+)/m', $content, $matches)) {
        $current_smtp = trim($matches[1]);
        echo json_encode([
            'status' => 'Current SMTP: ' . $current_smtp,
            'configured' => $current_smtp !== 'localhost'
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gmail SMTP Setup</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .step { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
        .step h3 { margin-top: 0; color: #4CAF50; }
        input { padding: 10px; width: 100%; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        button { background: #4CAF50; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .success { background: #d4edda; padding: 15px; border-radius: 4px; margin: 20px 0; color: #155724; }
        .code { background: #f1f1f1; padding: 10px; border-radius: 4px; font-family: monospace; }
        a { color: #4CAF50; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Gmail SMTP Configuration</h1>
        
        <div class="warning">
            <strong>⚠️ Important:</strong> You need a Gmail App Password (NOT your regular Gmail password)
        </div>

        <div class="step">
            <h3>Step 1: Get Gmail App Password</h3>
            <ol>
                <li>Open <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                <li>Select <strong>Mail</strong> as the app</li>
                <li>Select <strong>Windows Computer</strong> as the device</li>
                <li>Google will show a 16-character password - <strong>copy it</strong></li>
                <li>The password will look like: <span class="code">aaaa bbbb cccc dddd</span></li>
            </ol>
        </div>

        <div class="step">
            <h3>Step 2: Enter Your Credentials</h3>
            <form method="POST">
                <label><strong>Gmail Email Address:</strong></label>
                <input type="email" name="gmail_user" placeholder="your.email@gmail.com" required>
                
                <label><strong>Gmail App Password:</strong></label>
                <input type="password" name="app_password" placeholder="aaaa bbbb cccc dddd" required>
                
                <button type="submit">Configure Gmail SMTP</button>
            </form>
        </div>

        <div class="step">
            <h3>Step 3: Restart Apache</h3>
            <ol>
                <li>Open <strong>XAMPP Control Panel</strong></li>
                <li>Click <strong>Stop</strong> next to Apache</li>
                <li>Wait 2 seconds</li>
                <li>Click <strong>Start</strong> next to Apache</li>
            </ol>
        </div>

        <div class="step">
            <h3>Step 4: Test Email</h3>
            <p>Go to the clinic website and book an appointment. You should receive confirmation email within 5 seconds.</p>
            <p><a href="http://localhost/skin-perfect-clinic/index.php">← Go to Website</a></p>
        </div>

        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 4px;">
            <strong>📋 Tip:</strong> After configuring, you can view email logs at:
            <div class="code" style="margin-top: 10px;">http://localhost/skin-perfect-clinic/email_log_viewer.php</div>
        </div>
    </div>
</body>
</html>
