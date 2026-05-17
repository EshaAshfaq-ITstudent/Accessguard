<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get the email from the form
    $user_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($user_email)) {
        die("Email is required.");
    }

    // 2. Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 3. Check if the email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 4. If the email exists, generate a secure token
        $token = bin2hex(random_bytes(32)); 
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); 

        // 5. Save the token and its expiration time in the database
        $stmt_update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expires, $user_email);
        $stmt_update->execute();
        $stmt_update->close();

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'connectolead@gmail.com'; 
            $mail->Password   = 'swvd bgkd tzgx dgro';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('connectolead@gmail.com', 'ConnectoLead');
            $mail->addAddress($user_email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $reset_link = "http://localhost/Project/reset_password.php?token=$token";
            $mail->Body    = "
                <p>Hello,</p>
                <p>You requested a password reset. Click the link below to reset your password:</p>
                <p><a href='$reset_link'>$reset_link</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";

            $mail->send();
            echo "A password reset link has been sent to your email address.";
        } catch (Exception $e) {
            echo "An error occurred while sending the email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "If a user with that email exists, a password reset link has been sent.";
        // It's a security best practice not to reveal whether the email exists.
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: forgot_password.php");
    exit();
}
?>