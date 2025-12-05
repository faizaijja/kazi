<?php
// API Router - Keep this separate from index.php

// Start the session
session_start();

// Database connection function
function getDbConnection()
{
    $host = "localhost";
    $dbname = "kazi";
    $username = "root";
    $password = "";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
    }
}

// Get database connection
$db = getDbConnection();

// Get request details
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path (adjust according to your setup)
$path = str_replace('/backend', '', $path);

// Get JSON input for POST/PUT requests
$input = json_decode(file_get_contents('php://input'), true);

// Set headers for JSON response
header('Content-Type: application/json');

// Route the request
switch ($path) {
    // ============ AUTH ROUTES ============
    case '/api/auth/register':
        if ($method == 'POST') {
            require_once 'functions/auth.php';
            echo json_encode(registerUser($db, $input));
        }
        break;

    case '/api/auth/login':
        if ($method == 'POST') {
            require_once 'functions/auth.php';
            echo json_encode(loginUser($db, $input));
        }
        break;

    // ============ JOB ROUTES ============
    case '/api/jobs':
        // Check if user is logged in for protected routes
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        require_once 'functions/jobs.php';
        if ($method == 'GET') {
            echo json_encode(getJobs($db));
        } elseif ($method == 'POST') {
            echo json_encode(createJob($db, $input));
        }
        break;

    case (preg_match('/^\/api\/jobs\/(\d+)$/', $path, $matches) ? true : false):
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        require_once 'functions/jobs.php';
        $job_id = $matches[1];

        if ($method == 'GET') {
            echo json_encode(getJobById($db, $job_id));
        } elseif ($method == 'PUT') {
            echo json_encode(updateJob($db, $job_id, $input));
        } elseif ($method == 'DELETE') {
            echo json_encode(deleteJob($db, $job_id));
        }
        break;

    // ============ PROVIDER ROUTES ============
    case '/api/providers':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        require_once 'functions/providers.php';
        if ($method == 'GET') {
            echo json_encode(getProviders($db, $_GET));
        }
        break;

    case (preg_match('/^\/api\/providers\/(\d+)$/', $path, $matches) ? true : false):
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        require_once 'functions/providers.php';
        $provider_id = $matches[1];

        if ($method == 'GET') {
            echo json_encode(getProviderById($db, $provider_id));
        } elseif ($method == 'PUT') {
            echo json_encode(updateProvider($db, $provider_id, $input));
        }
        break;

    // ============ FILE UPLOAD ROUTES ============
    case '/api/upload/document':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($method == 'POST') {
            require_once 'functions/uploads.php';
            echo json_encode(uploadDocument($db, $_FILES, $_POST));
        }
        break;

    case '/api/upload/profile':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($method == 'POST') {
            require_once 'functions/uploads.php';
            echo json_encode(uploadProfilePicture($db, $_FILES, $_POST));
        }
        break;

    // ============ DEFAULT ============
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found', 'path' => $path]);
        break;
}
?>