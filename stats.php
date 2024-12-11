<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === "getStatistics") {
        $filter = $_GET['filter'] ?? 'category'; // Default filter to 'category'
        $statistics = [];

        if ($filter === 'category') {
            // Group by category
            $stmt = $conn->prepare("SELECT category AS name, COUNT(*) AS total FROM reports GROUP BY category ORDER BY total DESC");
        } elseif ($filter === 'status') {
            // Group by status
            $stmt = $conn->prepare("SELECT status AS name, COUNT(*) AS total FROM reports GROUP BY status ORDER BY total DESC");
        } elseif ($filter === 'zipcode') {
            // Group by zipcode (assuming the last part of the address is the zipcode)
            $stmt = $conn->prepare("SELECT 
                SUBSTRING_INDEX(phyaddress, ' ', -1) AS name, 
                COUNT(*) AS total 
                FROM reports 
                GROUP BY name 
                ORDER BY total DESC");
        } else {
            echo json_encode(["success" => false, "message" => "Invalid filter"]);
            exit;
        }

        $stmt->execute();
        $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "statistics" => $statistics]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
