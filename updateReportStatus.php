<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

// Decode the incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Ensure required fields are present
if (!isset($data['report_id']) || !isset($data['status'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$report_id = $data['report_id'];
$status = $data['status'];

try {
    // Update the report's status
    $query = "UPDATE reports SET status = :status WHERE id = :report_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':status' => $status,
        ':report_id' => $report_id
    ]);

    echo json_encode(["success" => true, "message" => "Report status updated successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

$conn = null; // Close the connection
?>
