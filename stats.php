<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === "getStatistics") {
        $statistics = [];

        // Total Reports by Status
        $stmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM reports GROUP BY status");
        $stmt->execute();
        $statistics['reportsByStatus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Average Time to Close
        $stmt = $conn->prepare("SELECT AVG(DATEDIFF(STR_TO_DATE(closed_at, '%Y-%m-%d'), STR_TO_DATE(created_at, '%Y-%m-%d'))) AS avg_days_to_close FROM reports WHERE status = 'Closed'");
        $stmt->execute();
        $statistics['avgTimeToClose'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Top Categories
        $stmt = $conn->prepare("SELECT category, COUNT(*) AS report_count FROM reports GROUP BY category ORDER BY report_count DESC LIMIT 3");
        $stmt->execute();
        $statistics['topCategories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monthly Submissions
        $stmt = $conn->prepare("SELECT DATE_FORMAT(STR_TO_DATE(created_at, '%Y-%m-%d'), '%Y-%m') AS month, COUNT(*) AS report_count FROM reports GROUP BY month ORDER BY month");
        $stmt->execute();
        $statistics['monthlyReports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "statistics" => $statistics]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
