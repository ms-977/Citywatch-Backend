<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include 'db.php'; // Ensure db.php sets up a PDO connection as $conn

// Retrieve the JSON payload from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Extract form fields
$name = $data['name'] ?? null;
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;
$state = $data['state'] ?? null;
$zipcode = $data['zipcode'] ?? null;

// Validate all fields
if (!$name || !$email || !$password || !$state || !$zipcode) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

try {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the user table
    $sqlUser = "INSERT INTO user (name, email, password) VALUES (:name, :email, :password)";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashedPassword,
    ]);

    $userId = $conn->lastInsertId(); // Get the last inserted user ID

    // Insert into the address table
    $sqlAddress = "INSERT INTO address (user_id, state_name, zipcode) VALUES (:user_id, :state_name, :zipcode)";
    $stmtAddress = $conn->prepare($sqlAddress);
    $stmtAddress->execute([
        ':user_id' => $userId,
        ':state_name' => $state,
        ':zipcode' => $zipcode,
    ]);

    echo json_encode(["success" => true, "message" => "User registered successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
