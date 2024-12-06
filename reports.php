<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Ensure this sets up a PDO connection as $conn

try {
    // Fetch all reports including imageurl from the database
    $sql = "SELECT 
    reports.id, 
    user.name AS username, 
    address.state_name AS location, 
    reports.category, -- Include the category name
    reports.description, 
    reports.priority, 
    reports.imageurl, 
    reports.longitude, 
    reports.latitude,
    reports.status 
FROM reports
LEFT JOIN user ON reports.user_id = user.id
LEFT JOIN address ON reports.user_id = address.user_id";
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all reports as associative array
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if reports exist
    if (!empty($reports)) {
        echo json_encode(["success" => true, "data" => $reports]);
    } else {
        echo json_encode(["success" => false, "message" => "No reports found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
