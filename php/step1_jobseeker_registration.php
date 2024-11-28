<?php
session_start();

require_once 'db_connection.php';

$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $display_name = trim($_POST['display_name']);
    $contact_no = trim($_POST['contact_no']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm_pass'];

   
    if (empty($display_name) || empty($contact_no) || empty($email) || empty($username) || empty($password) || empty($confirm_pass)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_pass) {
        $error_message = "Passwords do not match.";
    } else {
       
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); 

        $stmt = $conn->prepare("INSERT INTO users (contact_no, email, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $contact_no, $email, $username, $hashed_password);

        if ($stmt->execute()) {
           
            $user_id = $stmt->insert_id;

            $user_type_id = 1;

           
            $stmt_profile = $conn->prepare("INSERT INTO users_profile (user_id, display_name, user_type_id) VALUES (?, ?, ?)");
            $stmt_profile->bind_param("isi", $user_id, $display_name, $user_type_id);

            if ($stmt_profile->execute()) {
               
                $_SESSION['user_id'] = $user_id; 
                $_SESSION['display_name'] = $display_name;
                $_SESSION['user_type_id'] = $user_type_id;

               
                header("Location: ../php/step2_jobseeker_registration.php");
                exit();
            } else {
                $error_message = "Failed to save profile information.";
            }
        } else {
            $error_message = "Failed to register user.";
        }

        $stmt->close();
        $stmt_profile->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp</title>
    
</head>
<body class="signup">
    <div class="signup-container">
        <form action="" method="POST">
            <h1>Sign Up</h1>
            <h3>Personal Information</h3>

            <div class="user-details">
                <div class="input-box">
                    <label for="display-name">Name</label>
                    <input type="text" name="display_name" required />
                </div>
             
                <div class="input-box full-width">
                    <label for="contact_no">Contact Number</label>
                    <input type="text" name="contact_no" required />
                </div>

                <div class="input-box full-width">
                    <label for="email">Email</label>
                    <input type="email" name="email" required />
                </div>
            
                <div class="input-box full-width">
                    <label for="username">Username</label>
                    <input type="text" name="username" required />
                </div>

                <div class="input-box full-width">
                    <label for="password">Password</label>
                    <input type="password" name="password" required />
                </div>

                <div class="input-box full-width">
                    <label for="confirm_pass">Confirm Password</label>
                    <input type="password" name="confirm_pass" required />
                </div>
            </div>
          
            <input type="submit" name="submit" value="Next" class="form-btn">
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </form>
    </div>
    
</body>
</html>
