<!--for forgot password func-->
<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$email = $_POST["email"];

$token = bin2hex(random_bytes(16));

$token_hash = hash("sha256", $token);

$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

$conn = require __DIR__ . "/db_connection.php";

$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("sss", $token_hash, $expiry, $email);

$stmt->execute();

if ($conn->affected_rows) {
    $mail = require __DIR__ . "/mailer.php";

    $mail->setFrom("noreply@example.com");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset Request";
    $mail->Body = <<<END

    
    <p>Hello,</p>
    <p>We received a request to reset the password for your account on The Official Key Maker.</p>
    <p>If you did not make this request, please ignore this email. Your password will remain unchanged.</p>
    <br>
    <p>If you requested a password reset, please click the link below to reset your password:</p>

    Click <a href ="http://localhost/KeyMakerWebsite/php/reset-password.php?token=$token">here</a>
    to reset your password.

    <br><br>
    <p><strong>Important Reminder:</strong></p>
    <p>Please keep your password private. Sharing it can affect your account's security and personal information.</p>

    <br>
    <p>Thank you for being a part of The Official Key Maker!</p>

    <p>If you have any questions or need further assistance, feel free to contact our support team or leave a message to OfficialKeyMaker@gmail.com.</p>


    <br><br>
    <p>Sent by The Official Key Maker Team</p>



    END;

    try {
        if ($mail->send()) {
            echo "Message sent, please check your inbox.";
        } else {
            echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }
} else {
    echo "No account associated with this email.";
}

  
