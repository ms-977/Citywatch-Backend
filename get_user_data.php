<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000"); // Change if needed
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include 'db.php'; // Ensure db.php sets up a PDO connection as $conn

// Assuming user is authenticated and their ID is available
// For this example, we will fetch the user ID from a session or directly from the query parameter
// You can adapt this to your authentication mechanism

$userId = $_GET['user_id'] ?? null; // Assuming the user_id is passed as a query parameter
// Or if you are using sessions:
// $userId = $_SESSION['user_id'];

if (!$userId) {
    echo json_encode(["success" => false, "message" => "User ID is required"]);
    exit;
}

try {
    // Fetch the user's data from the user table
    $sqlUser = "SELECT id, name, email, password FROM user WHERE id = :user_id";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->execute([':user_id' => $userId]);

    if ($stmtUser->rowCount() == 0) {
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;
    }

    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Fetch the user's address from the address table
    $sqlAddress = "SELECT state_name, zipcode FROM address WHERE user_id = :user_id";
    $stmtAddress = $conn->prepare($sqlAddress);
    $stmtAddress->execute([':user_id' => $userId]);

    if ($stmtAddress->rowCount() == 0) {
        echo json_encode(["success" => false, "message" => "Address not found"]);
        exit;
    }

    $address = $stmtAddress->fetch(PDO::FETCH_ASSOC);

    // Combine the user data and address into a single response
    $response = [
        "success" => true,
        "user" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
        ],
        "address" => [
            "state_name" => $address['state_name'],
            "zipcode" => $address['zipcode'],
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>