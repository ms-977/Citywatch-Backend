<?php
header('Access-Control-Allow-Origin: http://localhost:3000'); // Allow only this origin
// or
// header('Access-Control-Allow-Origin: *');  // Allow all origins (less secure)

header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow specific methods
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Allow specific headers
header('Access-Control-Max-Age: 3600'); // Cache preflight request for 1 hour

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // Handle preflight requests
}
// Include the database connection file
include('db.php'); // Assuming this file handles DB connection using PDO

// Set the response content type to JSON
header('Content-Type: application/json');

// Read input data from the request
$data = json_decode(file_get_contents("php://input"));

// Check if both required parameters (email and newPassword) are provided
if (!isset($data->email) || !isset($data->newPassword)) {
    echo json_encode(["status" => "error", "message" => "Email and newPassword are required"]);
    exit();
}

// Sanitize input data
$email = strtolower(trim($data->email)); // Ensure email is lowercased and trimmed
$newPassword = trim($data->newPassword);

// Validate password length (e.g., minimum 6 characters)
if (strlen($newPassword) < 6) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters"]);
    exit();
}

try {
    // Prepare the SQL query to select the user by email
    $sql = "
    SELECT 
    user.id, 
    user.name, 
    user.email, 
    user.password, 
    ug.usertype  -- Use 'ug' alias instead of 'usergroup'
FROM user user
JOIN usergroup ug ON user.id = ug.user_id  -- Correct join alias
WHERE LOWER(user.email) = LOWER(:email)

";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();

    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Prepare the SQL query to update the password
    $updateSql = "UPDATE user SET password = :password WHERE id = :id";

    // Prepare the statement for updating the password
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $updateStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
    
    // Execute the update
    if ($updateStmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password"]);
    }
} catch (PDOException $e) {
    // Handle any errors during database interaction
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
