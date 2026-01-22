<?php
require "connect.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getDbConnection();

    // GET - Fetch all service providers
    if ($method === 'GET') {
        $sql = "SELECT 
                sp.provider_id,
                sp.user_id,
                sp.business_name,
                sp.bio,
                sp.years_of_experience,
                sp.hourly_rate,
                sp.availability_status,
                sp.rating_average,
                sp.total_jobs_completed,
                sp.verification_status,
                sp.category_id,
                sp.created_at,
                u.full_name,
                u.email
            FROM service_providers sp
            LEFT JOIN users u ON sp.user_id = u.user_id
            ORDER BY sp.rating_average DESC, sp.total_jobs_completed DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format data for React
        $formattedProviders = array_map(function ($provider) {
            return [
                'provider_id' => $provider['provider_id'],
                'user_id' => $provider['user_id'],
                'business_name' => $provider['business_name'] ?? 'Unknown',
                'bio' => $provider['bio'] ?? '',
                'years_of_experience' => (int) ($provider['years_of_experience'] ?? 0),
                'hourly_rate' => (float) ($provider['hourly_rate'] ?? 0),
                'availability_status' => $provider['availability_status'] ?? 'unavailable',
                'rating_average' => (float) ($provider['rating_average'] ?? 0),
                'total_jobs_completed' => (int) ($provider['total_jobs_completed'] ?? 0),
                'verification_status' => $provider['verification_status'] ?? 'unverified',
                'category_id' => $provider['category_id'],
                'created_at' => $provider['created_at'],
                'user' => [
                    'user_id' => $provider['user_id'],
                    'full_name' => $provider['full_name'],
                    'email' => $provider['email']
                ]
            ];
        }, $providers);

        echo json_encode([
            "success" => true,
            "data" => $formattedProviders,
            "count" => count($formattedProviders)
        ]);
    }

    // POST - Create new service provider
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['user_id'])) {
            throw new Exception("User ID is required");
        }

        $sql = "INSERT INTO service_providers (
                    user_id,
                    business_name,
                    bio,
                    years_of_experience,
                    hourly_rate,
                    availability_status,
                    rating_average,
                    total_jobs_completed,
                    verification_status,
                    category_id,
                    created_at
                ) VALUES (
                    :user_id,
                    :business_name,
                    :bio,
                    :years_of_experience,
                    :hourly_rate,
                    :availability_status,
                    :rating_average,
                    :total_jobs_completed,
                    :verification_status,
                    :category_id,
                    NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':business_name' => $data['business_name'] ?? $data['full_name'] ?? 'Unknown',
            ':bio' => $data['bio'] ?? '',
            ':years_of_experience' => $data['years_of_experience'] ?? 0,
            ':hourly_rate' => $data['hourly_rate'] ?? 0,
            ':availability_status' => $data['availability_status'] ?? 'available',
            ':rating_average' => 0,
            ':total_jobs_completed' => 0,
            ':verification_status' => 'unverified',
            ':category_id' => $data['category_id'] ?? null
        ]);

        $providerId = $conn->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => "Service provider profile created successfully",
            "data" => ["provider_id" => $providerId]
        ]);
    }

} catch (PDOException $e) {
    error_log("Service provider error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage(),
        "data" => []
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        "data" => []
    ]);
}
?>