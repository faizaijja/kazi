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

    // GET - Fetch all jobs
    if ($method === 'GET') {
        $sql = "SELECT 
                    j.job_id,
                    j.client_id,
                    j.category_id,
                    j.title,
                    j.description,
                    j.budget_min,
                    j.budget_max,
                    j.preferred_date,
                    j.preferred_time,
                    j.urgency,
                    j.status,
                    j.created_at,
                    j.updated_at,
                    u.full_name as client_name,
                    u.email as client_email
                FROM jobs j
                LEFT JOIN users u ON j.client_id = u.user_id
                ORDER BY j.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $jobs,
            "count" => count($jobs)
        ]);
    }

    // POST - Create new job
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['title']) || empty($data['description']) || empty($data['client_id'])) {
            throw new Exception("Missing required fields");
        }

        $sql = "INSERT INTO jobs (
                    client_id, 
                    category_id, 
                    title, 
                    description, 
                    budget_min, 
                    budget_max, 
                    preferred_date, 
                    preferred_time, 
                    urgency, 
                    status,
                    created_at
                ) VALUES (
                    :client_id,
                    :category_id,
                    :title,
                    :description,
                    :budget_min,
                    :budget_max,
                    :preferred_date,
                    :preferred_time,
                    :urgency,
                    'open',
                    NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':client_id' => $data['client_id'],
            ':category_id' => $data['category_id'] ?? null,
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':budget_min' => $data['budget_min'] ?? null,
            ':budget_max' => $data['budget_max'] ?? null,
            ':preferred_date' => $data['preferred_date'] ?? null,
            ':preferred_time' => $data['preferred_time'] ?? null,
            ':urgency' => $data['urgency'] ?? 'normal'
        ]);

        $jobId = $conn->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => "Job created successfully",
            "data" => ["job_id" => $jobId]
        ]);
    }

} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
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