<?php
/**
 * Doctor Login Page
 */
session_start();
require_once 'config/database.php';

// Default doctor credentials (Change these in production!)
define('DOCTOR_USERNAME', 'doctor');
define('DOCTOR_PASSWORD', 'doctor@123');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === DOCTOR_USERNAME && $password === DOCTOR_PASSWORD) {
        $_SESSION['doctor_logged_in'] = true;
        $_SESSION['doctor_username'] = $username;
        header('Location: admin/doctor_appointments.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login - Skin Perfect Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #10B981;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-header p {
            color: #64748B;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #CBD5E1;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #10B981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #10B981;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background: #059669;
        }
        
        .error-message {
            background: #FEE2E2;
            border: 1px solid #FCA5A5;
            color: #7F1D1D;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fa-solid fa-user-doctor"></i> Skin Perfect</h1>
            <p>Doctor Panel Login</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #64748B; font-size: 14px;">
            Default credentials: doctor / doctor@123
        </p>
    </div>
</body>
</html>
