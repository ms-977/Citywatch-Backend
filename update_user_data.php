<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000"); // Change if needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include 'db.php'; // Ensure db.php sets up a PDO connection as $conn

// Get the input data (assuming JSON is sent in the body)
$inputData = json_decode(file_get_contents("php://input"), true);

// Ensure we have the necessary fields in the POST request
$userId = $inputData['user_id'] ?? null;
$name = $inputData['name'] ?? null;
$email = $inputData['email'] ?? null;
$zipcode = $inputData['zipcode'] ?? null;
$password = $inputData['password'] ?? null; // New password field

if (!$userId) {
    echo json_encode(["success" => false, "message" => "User ID is required"]);
    exit;
}

try {
    // Start building the SQL update query dynamically for the user table
    $updateUserFields = [];
    $updateUserValues = [':user_id' => $userId]; // Always include user_id

    // Only add fields if they are provided in the request
    if ($name !== null) {
        $updateUserFields[] = "name = :name";
        $updateUserValues[':name'] = $name;
    }
    if ($email !== null) {
        $updateUserFields[] = "email = :email";
        $updateUserValues[':email'] = $email;
    }
    if ($password !== null) {
        // Hash the new password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateUserFields[] = "password = :password";
        $updateUserValues[':password'] = $hashedPassword;
    }

    // If no fields are provided for the user, skip updating the user table
    if (!empty($updateUserFields)) {
        // Build the SQL query for user table
        $sqlUser = "UPDATE user SET " . implode(', ', $updateUserFields) . " WHERE id = :user_id";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->execute($updateUserValues);
    }

    // Start building the SQL update query dynamically for the address table
    $updateAddressFields = [];
    $updateAddressValues = [':user_id' => $userId]; // Always include user_id

    // Only add zipcode to the update if it's provided
    if ($zipcode !== null) {
        $updateAddressFields[] = "zipcode = :zipcode";
        $updateAddressValues[':zipcode'] = $zipcode;
    }

    // If no fields are provided for the address, skip updating the address table
    if (!empty($updateAddressFields)) {
        // Build the SQL query for address table
        $sqlAddress = "UPDATE address SET " . implode(', ', $updateAddressFields) . " WHERE user_id = :user_id";
        $stmtAddress = $conn->prepare($sqlAddress);
        $stmtAddress->execute($updateAddressValues);
    }

    // If at least one field was updated, send success
    echo json_encode(["success" => true, "message" => "User and/or address data updated successfully"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>