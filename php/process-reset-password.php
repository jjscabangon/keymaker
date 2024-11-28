<!--for forgot password func-->
<?php
$token = $_POST["token"];
$new_password = $_POST["password"]; 
$token_hash = hash("sha256", $token);
$conn = require __DIR__ . "/db_connection.php";


$sql = "SELECT * FROM users
         WHERE reset_token_hash = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die("Token not found");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired");
}


$password_hash = password_hash($new_password, PASSWORD_DEFAULT);


$sql = "UPDATE users
        SET password = ?,
            reset_token_hash = NULL,
            reset_token_expires_at = NULL
        WHERE user_id = ?"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $password_hash, $user["user_id"]); 

$stmt->execute();

echo "Password updated. You can now login.";
?>
