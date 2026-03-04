<?php
/**
 * Patient Appointment History Portal
 */
require_once 'config/database.php';

$patient_info = null;
$appointments = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($phone)) {
        $error = 'Please enter your phone number.';
    } else if (!preg_match('/^\d{10}$/', preg_replace('/[^\d]/', '', $phone))) {
        $error = 'Invalid phone number format.';
    } else {
        // Get patient info
        $patient_info = getResult("SELECT * FROM patients WHERE phone = ?", [$phone]);
        
        if (!$patient_info) {
            $error = 'No appointments found for this phone number.';
        } else {
            // Get patient appointments
            $appointments = executeQuery(
                "SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC, appointment_time DESC",
                [$patient_info['id']]
            );
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Skin Perfect Clinic</title>
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
            text-align: center;
        }
        
        .search-form {
            background: #F8FAFC;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        input[type="tel"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        input[type="tel"]:focus {
            outline: none;
            border-color: #0EA5E9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        button {
            background: #0EA5E9;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            width: 100%;
        }
        
        button:hover {
            background: #0284C7;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #F87171;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #6EE7B7;
        }
        
        .patient-card {
            background: #E0F2FE;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 5px solid #0EA5E9;
        }
        
        .patient-card h2 {
            color: #0284C7;
            margin-bottom: 0.5rem;
        }
        
        .patient-card p {
            color: #334155;
            margin-bottom: 0.3rem;
        }
        
        .appointments-grid {
            display: grid;
            gap: 1rem;
        }
        
        .appointment-card {
            background: #F8FAFC;
            border-left: 4px solid #0EA5E9;
            padding: 1rem;
            border-radius: 5px;
        }
        
        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .appointment-date {
            font-weight: 600;
            color: #0EA5E9;
            font-size: 1.1rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }
        
        .status-confirmed {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .status-completed {
            background: #DDD6FE;
            color: #4C1D95;
        }
        
        .status-cancelled {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .appointment-details {
            color: #64748B;
            font-size: 0.9rem;
        }
        
        .appointment-details p {
            margin-bottom: 0.3rem;
        }
        
        .no-appointments {
            text-align: center;
            padding: 2rem;
            color: #64748B;
        }
        
        .no-appointments i {
            font-size: 3rem;
            color: #CBD5E1;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa-solid fa-calendar-check"></i> My Appointments</h1>
        
        <div class="search-form">
            <h3 style="margin-bottom: 1.5rem; color: #333;">View Your Appointment History</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>
                <button type="submit"><i class="fa-solid fa-search"></i> Search Appointments</button>
            </form>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($patient_info): ?>
            <div class="patient-card">
                <h2><?php echo htmlspecialchars($patient_info['patient_name']); ?></h2>
                <p><strong>Patient ID:</strong> #<?php echo htmlspecialchars($patient_info['id']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient_info['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient_info['email'] ?? 'Not provided'); ?></p>
                <p><strong>Registered Since:</strong> <?php echo date('M d, Y', strtotime($patient_info['created_at'])); ?></p>
            </div>
            
            <?php if (!empty($appointments)): ?>
                <h3 style="color: #333; margin-bottom: 1rem;">Your Appointments (<?php echo count($appointments); ?>)</h3>
                <div class="appointments-grid">
                    <?php foreach ($appointments as $apt): ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <div>
                                    <div class="appointment-date">
                                        <i class="fa-solid fa-calendar"></i> 
                                        <?php echo $apt['appointment_date'] ? date('M d, Y', strtotime($apt['appointment_date'])) : 'No date scheduled'; ?>
                                        <?php if ($apt['appointment_time']): ?>
                                            @ <?php echo htmlspecialchars($apt['appointment_time']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo htmlspecialchars($apt['status']); ?>">
                                    <?php echo ucfirst($apt['status']); ?>
                                </span>
                            </div>
                            <div class="appointment-details">
                                <p><strong>Booked:</strong> <?php echo date('M d, Y g:i A', strtotime($apt['created_at'])); ?></p>
                                <?php if ($apt['message']): ?>
                                    <p><strong>Message:</strong> <?php echo htmlspecialchars($apt['message']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-appointments">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <p>No appointments found.</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Visit the clinic to book an appointment.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
