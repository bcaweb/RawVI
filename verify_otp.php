<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // PHPMailer

// Check if email session exists
if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit;
}

$message = "";

// Function to send OTP
function sendOtp($email) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expire'] = time() + 600; // OTP valid for 10 minutes

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'toolstudy2@gmail.com';      // Replace with your Gmail
        $mail->Password   = 'fhvl jgcc qptf xtaj';        // Replace with Gmail App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('toolstudy2@gmail.com', 'RawVI Support');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'OTP for Password Reset';
        $mail->Body = "
            <h3>Hello,</h3>
            <p>Your OTP for password reset is:</p>
            <h2>$otp</h2>
            <p>It expires in 10 minutes.</p>
        ";

        $mail->send();
        return "OTP resend sucessful !!";
    } catch (Exception $e) {
        return "❌ Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Resend OTP if clicked
if (isset($_GET['resend'])) {
    $message = sendOtp($_SESSION['email']);
}

// Verify OTP form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = trim($_POST['otp']);
    
    if (time() > $_SESSION['otp_expire']) {
        $message = "❌ OTP expired. Request again.";
        session_unset();
    } elseif ($user_otp == $_SESSION['otp']) {
        // OTP verified
        header("Location: reset_password.php");
        exit;
    } else {
        $message = "❌ Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Verify OTP</h2>
        <form action="verify_otp.php" method="post">
            <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>
        <div class="message"><?php if(!empty($message)) echo htmlspecialchars($message); ?></div>
        <!-- Resend OTP -->
        <p><a href="verify_otp.php?resend=1">Resend OTP</a></p>
    </div>
</body>
</html>
