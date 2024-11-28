<?php
require_once 'db_connection.php';

if (!isset($_GET['id'])) {
    header('Location: admin_user_mgmt.php');
    exit();
}

$user_id = (int)$_GET['id'];
$error_message = '';
$success_message = '';

// Image upload restrictions
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('MIN_IMAGE_DIMENSION', 100);
define('MAX_IMAGE_DIMENSION', 400);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Fetch user data
$sql = "SELECT u.*, up.display_name, up.profile_picture_url, ut.type_name 
        FROM users u 
        JOIN users_profile up ON u.user_id = up.user_id 
        JOIN user_types ut ON up.user_type_id = ut.user_type_id 
        WHERE u.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: ua_mgmt.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_update']) && $_POST['confirm_update'] === 'yes') {
        // Validate required fields
        $display_name = trim($_POST['display_name']);
        $email = trim($_POST['email']);
        $contact_no = trim($_POST['contact_no']);
        
        if (empty($display_name) || empty($email) || empty($contact_no)) {
            $error_message = "Required fields cannot be empty.";
        } else {
            // Check if email already exists for other users
            $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $email_check->bind_param("si", $email, $user_id);
            $email_check->execute();
            if ($email_check->get_result()->num_rows > 0) {
                $error_message = "Email already exists for another user.";
            } else {
                // Handle profile picture upload
                $profile_picture_url = $user['profile_picture_url'];
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['profile_picture'];
                    
                    // Validate file size
                    if ($file['size'] > MAX_FILE_SIZE) {
                        $error_message = "File size must be less than 10MB.";
                    } else {
                        // Validate file type
                        $file_type = mime_content_type($file['tmp_name']);
                        if (!in_array($file_type, ALLOWED_TYPES)) {
                            $error_message = "Only JPG, JPEG, and PNG files are allowed.";
                        } else {
                            // Validate dimensions
                            list($width, $height) = getimagesize($file['tmp_name']);
                            if ($width < MIN_IMAGE_DIMENSION || $height < MIN_IMAGE_DIMENSION || 
                                $width > MAX_IMAGE_DIMENSION || $height > MAX_IMAGE_DIMENSION) {
                                $error_message = "Image dimensions must be between 100x100 and 400x400 pixels.";
                            } else {
                                // Generate unique filename
                                $filename = uniqid() . '_' . $file['name'];
                                $upload_path = '../uploads/' . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    // Delete old profile picture if exists
                                    if ($profile_picture_url && file_exists('../uploads/' . $profile_picture_url)) {
                                        unlink('../uploads/' . $profile_picture_url);
                                    }
                                    $profile_picture_url = $filename;
                                } else {
                                    $error_message = "Failed to upload profile picture.";
                                }
                            }
                        }
                    }
                }

                if (empty($error_message)) {
                    // Begin transaction
                    $conn->begin_transaction();
                    try {
                        // Update users table
                        $stmt_users = $conn->prepare("UPDATE users SET email = ?, contact_no = ? WHERE user_id = ?");
                        $stmt_users->bind_param("ssi", $email, $contact_no, $user_id);
                        $stmt_users->execute();

                        // Update users_profile table   
                        $stmt_profile = $conn->prepare("UPDATE users_profile SET display_name = ?, profile_picture_url = ? WHERE user_id = ?");
                        $stmt_profile->bind_param("ssi", $display_name, $profile_picture_url, $user_id);
                        $stmt_profile->execute();

                        $conn->commit();
                        $success_message = "Profile updated successfully!";
                        
                        // Refresh user data
                        $stmt->execute();
                        $user = $stmt->get_result()->fetch_assoc();
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error updating profile: " . $e->getMessage();
                    }
                }
            }
        }
    }

    // Password reset functionality - updated to check for existing password
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                // Get current password hash from database
                $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $current_password_hash = $stmt->get_result()->fetch_assoc()['password'];
                
                // Check if new password matches current password
                if (password_verify($_POST['new_password'], $current_password_hash)) {
                    $error_message = "New password cannot be the same as your current password.";
                } else {
                    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $new_password, $user_id);
                    $stmt->execute();
                    $success_message = "Password changed successfully.";
                }
            } else {
                $error_message = "Passwords do not match.";
            }
        } else {
            $error_message = "Both password fields are required.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User Profile</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/up_edit.css">
</head>
<body>
    <?php include 'navs/navbar_admin.php'; ?>

    <div class="container">
        <h1>Edit User Profile</h1>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to save these changes?');">
            <div class="profile-section">
                <div class="profile-header">
                    <img src="<?php echo !empty($user['profile_picture_url']) ? 
                        htmlspecialchars($user['profile_picture_url']) : 
                        '../images/default-avatar.png'; ?>" 
                        alt="Profile Picture" class="profile-picture">
                    
                    <div class="profile-picture-upload">
                        <label for="profile_picture">Change Profile Picture:</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/jpg">
                        <p class="help-text">
                            Allowed formats: JPG, JPEG, PNG<br>
                            Maximum size: 10MB<br>
                            Dimensions: 100x100 to 400x400 pixels
                        </p>
                    </div>
                </div>

                <div class="basic-info">
                    <h3>Basic Information</h3>
                    <div class="form-group">
                        <label for="display_name">Name:</label>
                        <input type="text" id="display_name" name="display_name" 
                               value="<?php echo htmlspecialchars($user['display_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Username:</label>
                        <span class="readonly-field"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_no">Contact:</label>
                        <input type="text" id="contact_no" name="contact_no" 
                               value="<?php echo htmlspecialchars($user['contact_no']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>User Type:</label>
                        <span><?php echo htmlspecialchars($user['type_name']); ?></span>
                    </div>

                    <div class="form-group">
                        <label>Status:</label>
                        <span><?php echo htmlspecialchars($user['status']); ?></span>
                    </div>

                    <div class="form-actions">
                        <input type="hidden" name="confirm_update" value="yes">
                        <button type="submit">Save Changes</button>
                        <button type="reset">Clear Changes</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="password-section">
            <h3>Change Password</h3>
            <form method="POST" name="password-form" onsubmit="return confirm('Are you sure you want to change the password?');">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let formChanged = false;

    // Track changes in the basic information form
    document.querySelector('form[enctype="multipart/form-data"]').addEventListener('change', function() {
        formChanged = true;
    });

    // Track changes in the password form
    document.querySelector('form[name="password-form"]').addEventListener('change', function() {
        formChanged = true;
    });

    // Reset the formChanged flag when forms are submitted
    document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function() {
        formChanged = false;
    });

    document.querySelector('form[name="password-form"]').addEventListener('submit', function() {
        formChanged = false;
    });

    // Add event listener for page leave
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            // This message might not be displayed in modern browsers, 
            // but the prompt will still appear
            const message = 'Are you sure you don\'t want to continue editing the user profile?';
            e.returnValue = message;
            return message;
        }
    });

    // Add click handlers for navigation links
    document.querySelectorAll('nav a, .sub-menu-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (formChanged) {
                if (!confirm('Are you sure you don\'t want to continue editing the user profile?')) {
                    e.preventDefault();
                }
            }
        });
    });
    </script>
</body>
</html>
