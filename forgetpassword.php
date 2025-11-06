<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php'; // Correct path to autoload

$message = "";

// Database connection
$conn = new mysqli("localhost", "root", "", "rawvi");
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $_SESSION['otp_expire'] = time() + 600; // 10 minutes

        // Send OTP email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'toolstudy2@gmail.com'; // your Gmail
            $mail->Password   = 'fhvl jgcc qptf xtaj';   // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('toolstudy2@gmail.com', 'RawVI Support');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'OTP for Password Reset';
            $mail->Body    = "<h3>Hello,</h3>
                              <p>Your OTP is:</p>
                              <h2 style='color:#00c8ff;'>$otp</h2>
                              <p>Expires in 10 minutes.</p>";

            $mail->send();

            // Redirect to verify OTP page
            $_SESSION['message'] = "OTP sent successfully!";
            header("Location: verify_otp.php");
            exit;

        } catch (Exception $e) {
            $message = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "Email not found!";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>
    <form method="post" action="">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send OTP</button>
    </form>
    <div class="message"><?php if(!empty($message)) echo htmlspecialchars($message); ?></div>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>
