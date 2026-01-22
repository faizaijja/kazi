<?php
require "connect.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents("php://input"), true);

// BACKEND: Validation
function validateLogin($data)
{
    $errors = [];

    // Required fields
    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    }
    if (empty($data['password'])) {
        $errors['password'] = 'Password is required';
    }

    // Email format validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    return $errors;
}

// Validate input
$validationErrors = validateLogin($data);
if (!empty($validationErrors)) {
    $firstError = array_values($validationErrors)[0];
    $firstField = array_keys($validationErrors)[0];

    echo json_encode([
        "success" => false,
        "message" => $firstError,
        "field" => $firstField,
        "errors" => $validationErrors
    ]);
    exit;
}

// Sanitize input
$email = strtolower(trim($data['email']));
$password = $data['password'];

try {
    $conn = getDbConnection();

    // Get user by email
    $sql = "SELECT user_id, full_name, email, password, user_type, created_at 
            FROM users 
            WHERE email = :email";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password",
            "field" => "email"
        ]);
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password",
            "field" => "password"
        ]);
        exit;
    }

    // Generate a simple token (in production, use JWT or more secure method)
    $token = bin2hex(random_bytes(32));

    // Optional: Store token in database for session management
    // $updateSql = "UPDATE users SET token = :token, last_login = NOW() WHERE user_id = :user_id";
    // $updateStmt = $conn->prepare($updateSql);
    // $updateStmt->execute([':token' => $token, ':user_id' => $user['user_id']]);

    // Remove password from response
    unset($user['password']);

    // Return success with user data and token
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "user_id" => $user['user_id'],
            "full_name" => $user['full_name'],
            "email" => $user['email'],
            "user_type" => $user['user_type'],
            "created_at" => $user['created_at']
        ],
        "token" => $token
    ]);

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Unable to complete login. Please try again."
    ]);
}
?>