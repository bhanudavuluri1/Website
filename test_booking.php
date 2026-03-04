<?php
/**
 * Test Appointment Booking API
 */
header('Content-Type: application/json');

// Test data
$testData = [
    'patient_name' => 'Test User',
    'phone' => '7780337034',
    'email' => 'test@example.com',
    'appointment_date' => '2026-03-10',
    'appointment_time' => '10:00',
    'message' => 'Test appointment'
];

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($testData);

// Require database config
require_once './config/database.php';

echo "=== TEST APPOINTMENT BOOKING ===\n\n";

// Test 1: Check database connection
echo "1. Database Connection: " . (isset($conn) ? "✅ OK" : "❌ FAILED") . "\n";

// Test 2: Check if sendEmailViaSMTP function exists
echo "2. sendEmailViaSMTP Function: " . (function_exists('sendEmailViaSMTP') ? "✅ OK" : "❌ MISSING") . "\n";

// Test 3: Check getOrCreatePatient function
echo "3. getOrCreatePatient Function: " . (function_exists('getOrCreatePatient') ? "✅ OK" : "❌ MISSING") . "\n";

// Test 4: Try to create patient
echo "\n4. Creating/Getting Patient...\n";
$patient_id = getOrCreatePatient($testData['patient_name'], $testData['phone'], $testData['email']);
echo "   Patient ID: " . ($patient_id ? $patient_id : "❌ ERROR") . "\n";

// Test 5: Check available slots
echo "\n5. Checking Available Slots for " . $testData['appointment_date'] . "...\n";
$slots = getAvailableSlots($testData['appointment_date']);
echo "   Available Slots: " . count($slots) . "\n";
echo "   First 3: " . implode(", ", array_slice($slots, 0, 3)) . "...\n";

// Test 6: Check if slot is booked
echo "\n6. Checking if " . $testData['appointment_time'] . " is booked...\n";
$is_booked = isSlotBooked($testData['appointment_date'], $testData['appointment_time']);
echo "   Booked: " . ($is_booked ? "Yes" : "No") . "\n";

// Test 7: Check clinic info
echo "\n7. Checking Clinic Info...\n";
$clinic = getResult("SELECT clinic_name, email FROM clinic_info LIMIT 1");
if ($clinic) {
    echo "   Clinic: " . $clinic['clinic_name'] . "\n";
    echo "   Email: " . $clinic['email'] . "\n";
} else {
    echo "   ❌ No clinic info found\n";
}

// Test 8: Check sendmail configuration
echo "\n8. PHP Mail Configuration:\n";
echo "   SMTP: " . ini_get('SMTP') . "\n";
echo "   smtp_port: " . ini_get('smtp_port') . "\n";
echo "   sendmail_from: " . ini_get('sendmail_from') . "\n";

// Test 9: Try a test email
echo "\n9. Sending Test Email...\n";
$test_email = 'test@example.com';
$test_result = sendEmailViaSMTP($test_email, 'Test Email', '<h1>Test</h1>', "Content-Type: text/html\r\nFrom: test@test.com");
echo "   Result: " . ($test_result ? "✅ Sent" : "❌ Failed") . "\n";

// Test 10: Check if logs directory exists
echo "\n10. Logs Directory:\n";
$logs_dir = __DIR__ . '/logs';
echo "   Path: " . $logs_dir . "\n";
echo "   Exists: " . (is_dir($logs_dir) ? "Yes" : "No") . "\n";
echo "   Email log readable: " . (is_readable($logs_dir . '/email_log.txt') ? "Yes" : "Not yet") . "\n";

echo "\n=== TEST COMPLETE ===\n";
?>
