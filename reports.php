<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; 

try {
    // Fetch all reports including imageurl and created_at
    $sql = "SELECT 
        reports.id, 
        user.name AS username, 
        address.state_name AS location, 
        reports.category, 
        reports.description, 
        reports.priority, 
        reports.imageurl, 
        reports.longitude, 
        reports.latitude, 
        reports.status,
        reports.created_at AS date_reported
    FROM reports
    LEFT JOIN user ON reports.user_id = user.id
    LEFT JOIN address ON reports.user_id = address.user_id";
    
    // Execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all reports as associative array
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results
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
