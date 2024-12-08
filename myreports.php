<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User ID is required"]);
    exit;
}

try {
    $sql = "
        SELECT 
            reports.id, 
            user.name AS username, 
            reports.category, 
            reports.description, 
            reports.priority, 
            reports.imageurl, 
            reports.status, 
            reports.longitude, 
            reports.latitude
        FROM reports
        JOIN user ON reports.user_id = user.id
        WHERE reports.user_id = :user_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($reports) {
        echo json_encode(["success" => true, "data" => $reports]);
    } else {
        echo json_encode(["success" => false, "message" => "No reports found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

?>
