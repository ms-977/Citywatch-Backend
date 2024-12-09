<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

// Capture incoming POST/GET request data
$input = json_decode(file_get_contents("php://input"), true) ?? $_GET;

// Ensure action is set
if (!isset($input['action'])) {
    echo json_encode(["success" => false, "message" => "No action specified."]);
    exit;
}

$action = $input['action'];

try {
    // Fetch Admin Users
    if ($action === "fetchAdmins") {
        $query = "
            SELECT 
                user.id, 
                user.name, 
                user.email, 
                usergroup.usertype 
            FROM user 
            JOIN usergroup ON user.id = usergroup.user_id 
            WHERE usergroup.usertype = 'Admin'
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "admins" => $admins]);
        exit;
    }

    // Fetch All Users
    if ($action === "fetchUsers") {
        $query = "
            SELECT 
                user.id, 
                user.name, 
                user.email, 
                usergroup.usertype AS usertype 
            FROM user 
            JOIN usergroup ON user.id = usergroup.user_id
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "users" => $users]);
        exit;
    }

    // Add New Admin or User
    if ($action === "addUser") {
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $password = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);
        $usertype = $input['usertype'] ?? 'User';

        // Insert into user table
        $query = "INSERT INTO user (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password
        ]);

        // Get the last inserted user ID
        $user_id = $conn->lastInsertId();

        // Assign role in usergroup table
        $query = "INSERT INTO usergroup (user_id, usertype) VALUES (:user_id, :usertype)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':usertype' => $usertype
        ]);

        echo json_encode(["success" => true, "message" => "User added successfully!"]);
        exit;
    }

    // Update User Role
    if ($action === "updateRole") {
        $user_id = $input['user_id'] ?? 0;
        $role = $input['role'] ?? 'User';

        $query = "UPDATE usergroup SET usertype = :role WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':role' => $role,
            ':user_id' => $user_id
        ]);

        echo json_encode(["success" => true, "message" => "User role updated successfully!"]);
        exit;
    }

    // Delete User
    if ($action === "deleteUser") {
        $user_id = $input['user_id'] ?? 0;

        // Delete user from both tables
        $query = "DELETE FROM user WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id
        ]);

        echo json_encode(["success" => true, "message" => "User deleted successfully!"]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

?>
