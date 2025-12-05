<?php

function uploadDocument($db, $files, $post_data)
{
    if (!isset($files['document'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    $file = $files['document'];
    $provider_id = $post_data['provider_id'];
    $document_type = $post_data['document_type'];

    // Validate file type
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $upload_path = 'uploads/documents/' . $filename;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Save to database
        $query = "INSERT INTO verification_documents 
                  (provider_id, document_type, document_url, verification_status, uploaded_at)
                  VALUES (:provider_id, :type, :url, 'pending', NOW())";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':provider_id', $provider_id);
        $stmt->bindParam(':type', $document_type);
        $stmt->bindParam(':url', $upload_path);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Document uploaded',
                'file_url' => $upload_path,
                'document_id' => $db->lastInsertId()
            ];
        }
    }

    return ['success' => false, 'message' => 'Upload failed'];
}

function uploadProfilePicture($db, $files, $post_data)
{
    // Similar implementation
}

?>