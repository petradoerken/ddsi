<?php
// send-email.php - Upload this file to the same directory as your index.html on Bluehost

// Set response header to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Basic security: check if form fields exist
$required_fields = ['first_name', 'last_name', 'organization', 'title', 'email', 'inquiry_type', 'message'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
        exit;
    }
}

// Sanitize input data
$first_name = htmlspecialchars(trim($_POST['first_name']));
$last_name = htmlspecialchars(trim($_POST['last_name']));
$organization = htmlspecialchars(trim($_POST['organization']));
$title = htmlspecialchars(trim($_POST['title']));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$inquiry_type = htmlspecialchars(trim($_POST['inquiry_type']));
$message = htmlspecialchars(trim($_POST['message']));

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Your email address
$to = 'petra@ddlegacycapital.com';

// Email subject
$subject = 'DDSI Contact Form: ' . $inquiry_type . ' - ' . $first_name . ' ' . $last_name;

// Email body
$email_body = "New inquiry from DDSI website\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "CONTACT INFORMATION\n\n";
$email_body .= "Name: " . $first_name . ' ' . $last_name . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Organization: " . $organization . "\n";
$email_body .= "Title: " . $title . "\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "INQUIRY TYPE\n\n";
$email_body .= $inquiry_type . "\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "MESSAGE\n\n";
$email_body .= $message . "\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "Submitted: " . date('F j, Y, g:i a') . "\n";
$email_body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";

// Email headers
$headers = "From: DDSI Website <noreply@ddstrategic.com>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$mail_sent = mail($to, $subject, $email_body, $headers);

if ($mail_sent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Email sent successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email'
    ]);
}
?>
