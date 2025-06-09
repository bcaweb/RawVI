<?php
session_start();
$loginError = '';

// Check if the user has a "remember me" cookie
if (!isset($_SESSION['email']) && isset($_COOKIE['remember_user'])) {
    $saved_email = $_COOKIE['remember_user'];
    $remembered = true;
} else {
    $saved_email = '';
    $remembered = false;
}

if (isset($_POST['login'])) {
    // Establish database connection first
    $con = mysqli_connect("localhost", "root", "", "rawvi");
    
    if (!$con) {
        $loginError = "⚠️ Database connection failed.";
    } else {
        // Trim whitespace from inputs and sanitize
        $email = trim(mysqli_real_escape_string($con, $_POST['email']));
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        // First check if the email exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Check if password matches
            if ($user['password'] === $password) {
                // Set session variables
                $_SESSION['email'] = $email;
                $_SESSION['admin'] = $email;
                if (isset($user['id'])) {
                    $_SESSION['user_id'] = $user['id'];
                }
                
                // If remember me is checked, set a cookie
                if ($remember) {
                    setcookie('remember_user', $email, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                } else {
                    // Clear any existing cookies
                    if (isset($_COOKIE['remember_user'])) {
                        setcookie('remember_user', '', time() - 3600, '/'); // expire now
                    }
                }
                
                header("Location: https://localhost/RawVi/Admin/dashboard.php");
                exit();
            } else {
                $loginError = "❌ Incorrect password.";
            }
        } else {
            $loginError = "❌ Email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RawVi Admin - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color:rgb(121, 82, 179);
            --secondary-color: #6441a5;
            --accent-color: #4c3175;
            --text-color: #333;
            --light-text: #777;
            --border-color: #e0e0e0;
            --bg-color: #f8f9fa;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            margin: 0 20px;
        }
        
        .left-panel {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 40px;
            border-radius: 10px 0 0 10px;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        .left-panel h1 {
            font-size: 2.4rem;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .left-panel p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }
        
        .left-panel::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }
        
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }
        
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 0 10px 10px 0;
            width: 60%;
            box-shadow: var(--box-shadow);
        }
        
        .admin-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            color: var(--primary-color);
            letter-spacing: 1px;
        }
        
        .error {
            background-color: #ffe6e6;
            color: #d63031;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #ff4d4d;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 15px;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(121, 82, 179, 0.2);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            user-select: none;
        }
        
        .remember-me input {
            margin-right: 10px;
            cursor: pointer;
        }
        
        .remember-me label {
            font-size: 14px;
            color: var(--light-text);
            cursor: pointer;
        }
        
        .btn-group {
            margin-top: 30px;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        button:hover {
            background: var(--secondary-color);
        }
        
        button i {
            margin-right: 8px;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }
            
            .left-panel {
                width: 100%;
                border-radius: 10px 10px 0 0;
                padding: 30px;
            }
            
            .login-box {
                width: 100%;
                border-radius: 0 0 10px 10px;
            }
        }
        
        @media (max-width: 480px) {
            .left-panel {
                padding: 20px;
            }
            
            .login-box {
                padding: 25px;
            }
            
            .admin-title {
                font-size: 20px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1>RawVi Admin</h1>
            <p>Welcome to Admin. Access all your tools and analytics in one place. Login to continue your journey with RawVi.</p>
        </div>
        
        <div class="login-box">
            <p class="admin-title">ADMIN LOGIN</p>
            
            <?php if ($loginError != ''): ?>
                <div class="error"><?php echo $loginError; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($saved_email); ?>" required>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" <?php if ($remembered) echo 'checked'; ?>>
                    <label for="remember">Remember me</label>
                </div>
                
                <div class="btn-group">
                    <button type="submit" name="login"><i class="fas fa-sign-in-alt"></i> Sign In</button>
                </div>
              
            </form>
        </div>
    </div>
</body>
</html>
