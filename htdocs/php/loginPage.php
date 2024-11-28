<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $query = "SELECT users.user_id, users.password, users_profile.user_type_id 
              FROM users 
              JOIN users_profile ON users.user_id = users_profile.user_id 
              WHERE users.username = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Failed to prepare the SQL statement.");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['user_type_id'] = $user['user_type_id'];

            
            if ($user['user_type_id'] == 1) {
                header("Location: ../php/listingpage_jobs.php");
            } elseif ($user['user_type_id'] == 2) {
                header("Location: ../php/listingpage_profiles.php");
            } elseif ($user['user_type_id'] == 3) {
                header("Location: ../php/admin.php");
            }
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }

    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/styles2.css">
</head>

<body class="login-container">
    <img src="../images/KMLogo1.png">

    <div class="login">
        <form action="" method="post">
            <h1>Login</h1>

            <?php if (!empty($error_message)) { ?>
                <div style="color: red;"><?= $error_message ?></div>
            <?php } ?>

            <div class="form-group">
                <label for="">Username</label>
                <input type="text" name="username" class="form-control" required><br><br>
            </div>

            <div class="form-group">
                <label for="">Password</label>
                <input type="password" name="password" class="form-control" required><br><br>
            </div>

            <a href="forgot-password.php">Forgot password?</a>

            <input type="submit" name="submit" value="Login" class="form-btn">
            <p>Don't have an account? <a href="../html/usertypeSelection.html">Register now</a></p>
        </form>
    </div>
</body>
</html>
