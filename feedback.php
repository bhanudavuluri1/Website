<?php
/**
 * Patient Feedback Form
 */
require_once 'config/database.php';

$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$patient_name = '';
$valid_appointment = false;
$message = '';
$error = '';

if ($appointment_id > 0) {
    // Check if appointment exists and is completed
    $apt = executeQuery("SELECT * FROM appointments WHERE id = ?", [$appointment_id]);
    
    if (!empty($apt)) {
        $apt = $apt[0];
        $patient_name = $apt['patient_name'];
        if ($apt['status'] === 'completed') {
            $valid_appointment = true;
            
            // Check if feedback already exists for this appointment
            $existing_review = executeQuery("SELECT * FROM reviews WHERE appointment_id = ?", [$appointment_id]);
            if (!empty($existing_review)) {
                $valid_appointment = false;
                $message = "You have already submitted feedback for this visit. Thank you!";
            }
        } else {
            $error = "This appointment is not marked as completed yet.";
        }
    } else {
        $error = "Invalid appointment link.";
    }
} else {
    $error = "No appointment specified.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_appointment) {
    $rating = intval($_POST['rating'] ?? 5);
    $review_text = trim($_POST['review_text'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a valid rating between 1 and 5.";
    } elseif (empty($review_text)) {
        $error = "Please provide a written review.";
    } else {
        $query = "INSERT INTO reviews (patient_name, review_text, rating, status, appointment_id) VALUES (?, ?, ?, 'pending', ?)";
        $params = [$patient_name, $review_text, $rating, $appointment_id];
        
        $result = executeQuery($query, $params);
        
        if ($result !== false) {
            $valid_appointment = false; // Hide form
            $message = "Thank you for your valuable feedback! It has been submitted successfully.";
        } else {
            $error = "An error occurred while saving your feedback. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Feedback - Skin Perfect Clinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0EA5E9;
            --secondary: #0284C7;
            --accent: #10B981;
            --text-dark: #334155;
            --text-light: #64748B;
            --bg-light: #F8FAFC;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, #E2E8F0 100%);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .feedback-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        /* Star Rating Logic */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 30px;
            color: #CBD5E1;
            transition: color 0.2s;
            margin: 0;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #F59E0B;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .go-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .go-back:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

    <div class="feedback-container">
        <div class="header">
            <h1><i class="fa-solid fa-heart-pulse" style="margin-right: 10px;"></i> How did we do?</h1>
            <?php if ($patient_name): ?>
                <p>Hi <?php echo htmlspecialchars($patient_name); ?>, we'd love to hear about your experience.</p>
            <?php else: ?>
                <p>Skin Perfect Clinic Feedback</p>
            <?php endif; ?>
        </div>

        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div><?php echo htmlspecialchars($message); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($valid_appointment): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Your Rating</label>
                        <div class="star-rating" title="Give a rating">
                            <input type="radio" id="star5" name="rating" value="5" checked />
                            <label for="star5" title="5 stars"><i class="fa-solid fa-star"></i></label>
                            
                            <input type="radio" id="star4" name="rating" value="4" />
                            <label for="star4" title="4 stars"><i class="fa-solid fa-star"></i></label>
                            
                            <input type="radio" id="star3" name="rating" value="3" />
                            <label for="star3" title="3 stars"><i class="fa-solid fa-star"></i></label>
                            
                            <input type="radio" id="star2" name="rating" value="2" />
                            <label for="star2" title="2 stars"><i class="fa-solid fa-star"></i></label>
                            
                            <input type="radio" id="star1" name="rating" value="1" />
                            <label for="star1" title="1 star"><i class="fa-solid fa-star"></i></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="review_text">Tell us about your visit</label>
                        <textarea id="review_text" name="review_text" placeholder="The doctor was very helpful..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> Submit Feedback
                    </button>
                </form>
            <?php endif; ?>

            <a href="index.php" class="go-back">Return to Homepage</a>
        </div>
    </div>

</body>
</html>
