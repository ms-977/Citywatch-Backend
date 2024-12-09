<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection
include 'db.php';

// Capture incoming request
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    echo json_encode(["success" => false, "message" => "No action specified."]);
    exit;
}

try {
    if ($action === "fetchUsers") {
        $query = "
            SELECT 
                user.id, 
                user.name, 
                user.email, 
                usergroup.usertype AS role 
            FROM user 
            JOIN usergroup ON user.id = usergroup.user_id
            WHERE usergroup.usertype = 'Admin'
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "users" => $users]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
