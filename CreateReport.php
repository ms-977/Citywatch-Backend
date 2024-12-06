<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

include 'db.php'; // Ensure this sets up a PDO connection as $conn

try {
    // Handle form data
    $user_id = $_POST['user_id'] ?? null;
    $category = $_POST['category'] ?? null; // Receive category name
    $description = $_POST['description'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $latitude = $_POST['latitude'] ?? null;
    $priority = $_POST['priority'] ?? null;

    if (!$user_id || !$category || !$description || !$longitude || !$latitude || !$priority) {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    // Handle file upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/Citywatch/CityWatch-Backend/uploads/";
        $relative_dir = "/Citywatch/CityWatch-Backend/uploads/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = $relative_dir . $file_name;
        } else {
            echo json_encode(["success" => false, "message" => "Failed to upload image."]);
            exit;
        }
    }

    // Insert report into the database
    $sql = "INSERT INTO reports (user_id, category, longitude, latitude, priority, imageurl, description) 
            VALUES (:user_id, :category, :longitude, :latitude, :priority, :image_url, :description)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':category' => $category,
        ':longitude' => $longitude,
        ':latitude' => $latitude,
        ':priority' => $priority,
        ':image_url' => $image_url,
        ':description' => $description,
    ]);

    echo json_encode(["success" => true, "message" => "Report submitted successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
