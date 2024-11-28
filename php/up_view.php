<?php
require_once 'db_connection.php';

if (!isset($_GET['id'])) {
    header('Location: admin_user_mgmt.php');
    exit();
}

$user_id = (int)$_GET['id'];

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User Profile</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/up_view.css">
</head>
<body>
    <?php include 'navs/navbar_admin.php'; ?>

    <div class="container">
        <h1>User Profile</h1>

        <div class="profile-section">
            <div class="profile-header">
                <?php 
                $profile_picture = isset($user['profile_picture_url']) && $user['profile_picture_url'] != "" 
                    ? "../img_profiles/" . $user['profile_picture_url'] 
                    : '../images/default-avatar.png';
                ?>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-image">
                <h2><?php echo htmlspecialchars($user['display_name']); ?></h2>
                <p>User Type: <?php echo htmlspecialchars($user['type_name']); ?></p>
            </div>

            <div class="basic-info">
                <h3>Basic Information</h3>
                <table>
                    <tr>
                        <th>User ID:</th>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    </tr>
                    <tr>
                        <th>Username:</th>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Contact:</th>
                        <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><?php echo htmlspecialchars($user['status']); ?></td>
                    </tr>
                    <tr>
                        <th>Last Login:</th>
                        <td><?php echo $user['last_login_date'] ? date('Y-m-d H:i:s', strtotime($user['last_login_date'])) : 'Never'; ?></td>
                    </tr>
                </table>
            </div>

            <div class="actions">
                <a href="up_edit.php?id=<?php echo $user['user_id']; ?>" class="edit-button">Edit Profile</a>
            </div>
        </div>
    </div>
</body>
</html>
