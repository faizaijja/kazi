<?php

function registerUser($db, $data)
{
    // Validate input
    if (empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    // Check if email exists
    $check_query = "SELECT user_id FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $data['email']);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Insert user
    $query = "INSERT INTO users (email, password_hash, full_name, user_type, created_at) 
              VALUES (:email, :password, :name, :type, NOW())";

    $stmt = $db->prepare($query);
    $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':name', $data['full_name']);
    $stmt->bindParam(':type', $data['user_type']);

    try {
        if ($stmt->execute()) {
            $user_id = $db->lastInsertId();

            // If service provider, create provider profile
            if ($data['user_type'] === 'service_provider') {
                $provider_query = "INSERT INTO service_providers (user_id, availability_status) 
                                   VALUES (:user_id, 'available')";
                $provider_stmt = $db->prepare($provider_query);
                $provider_stmt->bindParam(':user_id', $user_id);
                $provider_stmt->execute();
            }

            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $user_id
            ];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

function loginUser($db, $data)
{
    if (empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'message' => 'Email and password required'];
    }

    $query = "SELECT user_id, email, password_hash, full_name, user_type, is_verified 
              FROM users WHERE email = :email AND is_active = 1";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($data['password'], $user['password_hash'])) {
        // Generate simple token (in production, use JWT)
        $token = bin2hex(random_bytes(32));

        // Store token in database (you'd need a tokens table)
        // For now, just return user data

        unset($user['password_hash']); // Don't send password

        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ];
    }

    return ['success' => false, 'message' => 'Invalid credentials'];
}

function getCurrentUser($db, $token)
{
    // Verify token and return user
    // Implementation depends on your token strategy
}

?>