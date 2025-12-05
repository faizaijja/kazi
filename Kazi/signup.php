<?php
include "connect.php";

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate that required fields exist
    if (
        empty($_POST['full_name']) || empty($_POST['email']) ||
        empty($_POST['password']) || empty($_POST['user_type'])
    ) {
        $error_message = "All fields are required";
    } else {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : null;
        $user_type = $_POST['user_type'];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format";
        } else {
            // Validate user_type
            $allowed_types = ['client', 'provider', 'admin'];
            if (!in_array($user_type, $allowed_types)) {
                $error_message = "Invalid user type";
            } else {
                try {
                    // Use prepared statements (PDO)
                    $conn = getDbConnection();

                    $sql = "INSERT INTO users (full_name, email, password, phone_number, user_type) 
                            VALUES (:full_name, :email, :password, :phone_number, :user_type)";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':full_name' => $full_name,
                        ':email' => $email,
                        ':password' => $password,
                        ':phone_number' => $phone_number,
                        ':user_type' => $user_type
                    ]);

                    $success_message = "Registration successful! Redirecting to login...";

                    // Redirect after 2 seconds
                    header("refresh:2;url=login.php");

                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error_message = "Email already exists";
                    } else {
                        $error_message = "Registration failed: " . $e->getMessage();
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Kazi</title>
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
            background-color: rgba(255, 0, 0, 0.2);
            color: #ff6b6b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ff6b6b;
        }

        .success-message {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #4CAF50;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: white;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
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

        <!-- Signup Form -->
        <div class="login-form-container">
            <div class="login-form">
                <h2>Create Account - Kazi</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" placeholder="Enter your phone number">
                    </div>

                    <div class="form-group">
                        <label for="user_type">I am a:</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">Select user type</option>
                            <option value="client">Client</option>
                            <option value="provider">Service Provider</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="primary-btn">Create Account</button>
                </form>

                <div class="register-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>