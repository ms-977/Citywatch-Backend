<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include 'db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Decode the incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate email and password
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

try {
    // Prepare and execute the query to fetch the user by email
    $sql = "SELECT * FROM user WHERE LOWER(email) = LOWER(:email)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

$conn = null; // Close the connection
?>
