<?php
header('Access-Control-Allow-Origin: http://localhost:3000'); // Allow requests from your React app
header('Access-Control-Allow-Credentials: true'); // Allow credentials
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Specify allowed methods
header('Access-Control-Allow-Headers: Content-Type'); // Specify allowed headers
// ... rest of your forgot-password.php code ...

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight requests
    exit; // Important: Exit after preflight
}
// Include the database connection
include 'db.php';

// Include PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generate a random 4-digit code
function generateCode() {
    return rand(1000, 9999);
}

// Send the email using Gmail
function sendEmail($to, $code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'watchcity08@gmail.com'; // Your Gmail address
        $mail->Password = 'vkdkhbvzrrrehoji'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('watchcity08@gmail.com', 'CityWatch Support');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset Link';
        $mail->Body = "Your password reset code is: http://localhost:3000/user/update-password";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : null;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateCode(); // Generate the 4-digit code

            try {
                // Insert the email and code into the database
                $stmt = $conn->prepare("INSERT INTO password_resets (email, code, created_at) VALUES (:email, :code, NOW())");
                $stmt->execute(['email' => $email, 'code' => $code]);

                // Send the email with the code
                if (sendEmail($email, $code)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'A reset code has been sent to your email. Please check your inbox.'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to send the reset code. Please try again later.'
                    ]);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to save the reset code: ' . $e->getMessage()
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid or missing email address.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON format.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
}
?>
