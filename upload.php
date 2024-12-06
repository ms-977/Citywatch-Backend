<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Ensure this sets up a PDO connection as $conn

// Get `user_id` and `report_id` from POST data
$user_id = $_POST['user_id'] ?? null;
$report_id = $_POST['report_id'] ?? null;
$custom_base_url = $_POST['custom_base_url'] ?? null; // Optional base URL

if (!$user_id || !$report_id) {
    echo json_encode(["success" => false, "message" => "User ID and Report ID are required"]);
    exit;
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Directory to save uploaded images
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Citywatch/CityWatch-Backend/uploads/';
    $relativeDir = '/Citywatch/CityWatch-Backend/uploads/'; // Default relative path for database

    // Ensure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate a unique filename
    $fileName = basename($_FILES['image']['name']);
    $uniqueName = uniqid() . "_" . $fileName;
    $uploadFile = $uploadDir . $uniqueName;

    // Move uploaded file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        // Use custom base URL if provided, otherwise default to relative path
        $filePath = $custom_base_url
            ? rtrim($custom_base_url, '/') . '/' . $uniqueName
            : $relativeDir . $uniqueName;

        try {
            // Update the `reports` table with the image path
            $sql = "UPDATE reports SET imageurl = :filePath WHERE id = :report_id AND user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':filePath' => $filePath,
                ':report_id' => $report_id,
                ':user_id' => $user_id,
            ]);

            // Check if any row was updated
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "success" => true,
                    "message" => "Image uploaded and mapped successfully",
                    "filePath" => $filePath
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to update report with image path. No rows affected."
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to move uploaded file"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No file uploaded or an error occurred"]);
}
?>
