<?php

function getJobs($db, $filters = [])
{
    $query = "SELECT j.*, c.category_name, u.full_name as client_name 
              FROM job_requests j
              LEFT JOIN service_categories c ON j.category_id = c.category_id
              LEFT JOIN users u ON j.client_id = u.user_id
              WHERE j.status = 'open'
              ORDER BY j.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'data' => $jobs
    ];
}

function getJobById($db, $job_id)
{
    $query = "SELECT j.*, c.category_name, u.full_name as client_name, u.phone_number,
              l.address_line1, l.city, l.state
              FROM job_requests j
              LEFT JOIN service_categories c ON j.category_id = c.category_id
              LEFT JOIN users u ON j.client_id = u.user_id
              LEFT JOIN locations l ON j.location_id = l.location_id
              WHERE j.job_id = :job_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        return ['success' => false, 'message' => 'Job not found'];
    }

    return [
        'success' => true,
        'data' => $stmt->fetch(PDO::FETCH_ASSOC)
    ];
}

function createJob($db, $data)
{
    // Validate
    if (empty($data['title']) || empty($data['category_id'])) {
        return ['success' => false, 'message' => 'Title and category required'];
    }

    $query = "INSERT INTO job_requests 
              (client_id, category_id, title, description, budget_min, budget_max, 
               preferred_date, urgency, status, created_at)
              VALUES 
              (:client_id, :category_id, :title, :description, :budget_min, :budget_max,
               :preferred_date, :urgency, 'open', NOW())";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':client_id', $data['client_id']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':budget_min', $data['budget_min']);
    $stmt->bindParam(':budget_max', $data['budget_max']);
    $stmt->bindParam(':preferred_date', $data['preferred_date']);
    $stmt->bindParam(':urgency', $data['urgency']);

    try {
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Job posted successfully',
                'job_id' => $db->lastInsertId()
            ];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create job'];
    }
}

function updateJob($db, $job_id, $data)
{
    // Implementation for updating job
}

function deleteJob($db, $job_id)
{
    $query = "DELETE FROM job_requests WHERE job_id = :job_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':job_id', $job_id);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Job deleted'];
    }
    return ['success' => false, 'message' => 'Delete failed'];
}

?>