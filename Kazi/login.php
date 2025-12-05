<?php
include "connect.php";

// Start session at the very top
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Get user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $login_successful = false;

        // First try with password_verify (for hashed passwords)
        if (password_verify($password, $row['password'])) {
            $login_successful = true;
        }
        // Fallback: Check if password matches plaintext (for legacy passwords)
        else if ($password === $row['password']) {
            $login_successful = true;
        }

        if ($login_successful) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user data in session - MATCH YOUR DATABASE COLUMNS
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_type'] = $row['user_type']; // Changed from 'role' to 'user_type'

            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid password";
        }
    } else {
        $error_message = "User not found";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body,
        html {
            height: 100%;
            overflow: hidden;
        }

        .container {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        /* Full background image */
        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('images/abstract-blur-supermarket-retail-store.jpg');
            background-size: 100% auto;
            background-position: center;
            background-repeat: no-repeat;
            filter: brightness(0.8);
            z-index: -1;
        }

        .login-form-container {
            padding: 0 50px;
            z-index: 1;
            width: 450px;
            margin-left: 550px;
        }

        .login-form {
            width: 100%;
            background-color: rgba(8, 10, 5, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.4);
        }

        .login-form h2 {
            margin-bottom: 30px;
            text-align: center;
            color: white;
            font-size: 24px;
        }

        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: #ff6666;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: white;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            background-color: white;
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        .primary-btn {
            width: 100%;
            padding: 12px;
            background-color: lightcoral;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
            font-weight: bold;
        }

        .primary-btn:hover {
            background-color: lightgreen;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .login-form-container {
                width: 90%;
                margin: 0 auto;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Full Background Image -->
        <div class="bg-image"></div>

        <!-- Login Form -->
        <div class="login-form-container">
            <div class="login-form">
                <h2>Kazi</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" placeholder="Enter your password"
                            required>
                        <div class="forgot-password">
                            <a href="forgot_password.php">Forgot password?</a>
                            <a href="signup.php">Create account</a>
                        </div>
                    </div>
                    <button type="submit" class="primary-btn">LOGIN</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>