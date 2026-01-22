<?php
require "connect.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// BACKEND: Business logic validation (security & data integrity)
function validateRegistration($data)
{
    $errors = [];

    // Required fields
    if (empty($data['full_name']))
        $errors['full_name'] = 'Name is required';
    if (empty($data['email']))
        $errors['email'] = 'Email is required';
    if (empty($data['password']))
        $errors['password'] = 'Password is required';
    if (empty($data['user_type']))
        $errors['user_type'] = 'User type is required';

    // Business rules
    if (!empty($data['full_name']) && strlen(trim($data['full_name'])) < 2) {
        $errors['full_name'] = 'Name must be at least 2 characters';
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (!empty($data['password']) && strlen($data['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    $allowed_types = ['client', 'service_provider'];
    if (!empty($data['user_type']) && !in_array($data['user_type'], $allowed_types)) {
        $errors['user_type'] = 'Invalid user type';
    }

    return $errors;
}

// Validate input
$validationErrors = validateRegistration($data);
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

// Sanitize and prepare data
$full_name = trim($data['full_name']);
$email = strtolower(trim($data['email']));
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$user_type = $data['user_type'];

try {
    $conn = getDbConnection();

    // Check if email exists
    $checkSql = "SELECT user_id FROM users WHERE email = :email";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([':email' => $email]);

    if ($checkStmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "This email is already registered",
            "field" => "email"
        ]);
        exit;
    }

    // Insert new user
    $sql = "INSERT INTO users (full_name, email, password, user_type, created_at)
            VALUES (:full_name, :email, :password, :user_type, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':full_name' => $full_name,
        ':email' => $email,
        ':password' => $password,
        ':user_type' => $user_type
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Registration successful"
    ]);

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Unable to complete registration. Please try again."
    ]);
}
?>