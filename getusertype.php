<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

if (!isset($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "User ID missing."]);
    exit;
}

$user_id = $_GET['user_id'];

try {
    $query = "SELECT usertype FROM usergroup WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':user_id' => $user_id]);

    if ($stmt->rowCount() > 0) {
        $role = $stmt->fetch(PDO::FETCH_ASSOC)['usertype'];
        echo json_encode(["success" => true, "role" => $role]);
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
