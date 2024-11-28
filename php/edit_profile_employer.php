<?php
include 'db_connection.php'; 
include '../php/navs/navbar_employer.php';

session_start();
$user_id = $_SESSION['user_id'];  

// Get the user_id from the URL, defaulting to the logged-in user's ID
$viewed_user_id = $_GET['user_id'] ?? $user_id;


$select = mysqli_query($conn, "SELECT u.username, u.email, u.contact_no, u.status, up.display_name, up.profile_picture_url, up.bio, up.contact_person, up.business_permit_url 
FROM users u 
JOIN users_profile up ON u.user_id = up.user_id 
WHERE u.user_id = '$viewed_user_id'") or die('Query failed: ' . mysqli_error($conn));

$fetch = mysqli_fetch_assoc($select);

$profile_picture = isset($fetch['profile_picture_url']) && $fetch['profile_picture_url'] != "" 
    ? "../img_profiles/" . $fetch['profile_picture_url'] 
    : '../images/default-avatar.png';

$is_verified = isset($fetch['is_verified']) && $fetch['is_verified'] == 1;

if (isset($_POST['update_profile'])) {
    $updated_name = $_POST['update_name'];
    $updated_email = $_POST['update_email'];
    $updated_contact_no = $_POST['update_contact_no'];
    $updated_bio = $_POST['message'];
    $updated_contact_person = $_POST['update_contact_person'];

    // Profile picture upload
    if (isset($_FILES['profile_picture_url']['name']) && $_FILES['profile_picture_url']['name'] != "") {
        $image_name = basename($_FILES['profile_picture_url']['name']);
        $img_profile = "../img_profiles/" . $image_name;

        if (move_uploaded_file($_FILES['profile_picture_url']['tmp_name'], $img_profile)) {
            $stmt_profile = $conn->prepare("UPDATE users_profile SET profile_picture_url = ? WHERE user_id = ?");
            $stmt_profile->bind_param("si", $image_name, $viewed_user_id);
            $stmt_profile->execute();
            $stmt_profile->close(); 
        }
    }

    // Business permit upload
    if (isset($_FILES['update_business_permit']['name']) && $_FILES['update_business_permit']['name'] != "") {
        $permit_name = basename($_FILES['update_business_permit']['name']);
        $permit_path = "../user_documents/" . $permit_name;

        if (move_uploaded_file($_FILES['update_business_permit']['tmp_name'], $permit_path)) {
            $stmt_permit = $conn->prepare("UPDATE users_profile SET business_permit_url = ? WHERE user_id = ?");
            $stmt_permit->bind_param("si", $permit_path, $viewed_user_id);
            $stmt_permit->execute();
            $stmt_permit->close();
        }
    }

    // Update profile information
    $update_profile_query = "UPDATE users_profile SET display_name = '$updated_name', bio = '$updated_bio', contact_person = '$updated_contact_person' WHERE user_id = '$viewed_user_id'";
    mysqli_query($conn, $update_profile_query);

    $update_users_query = "UPDATE users SET email = '$updated_email', contact_no = '$updated_contact_no' WHERE user_id = '$viewed_user_id'";
    mysqli_query($conn, $update_users_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employer Profile</title>
    <link rel="stylesheet" href="../css/editprofile.css">
</head>
<body>

<div class="update-profile">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
                <span>Profile Picture: </span>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-image"><br><br>
                <input type="file" name="profile_picture_url" accept="image/*" class="box"><br><br>

                <!-- Verification Status -->
                <span>Status: </span>
                <?php if ($is_verified): ?>
                    <span class="verified-badge" style="color: green; font-weight: bold;">Verified</span>
                <?php else: ?>
                    <span class="not-verified-badge" style="color: red; font-weight: bold;">Not Verified</span>
                <?php endif; ?>
                <br><br>

                <span>Business Name: </span>
                <input type="text" name="update_name" value="<?php echo isset($fetch['display_name']) ? $fetch['display_name'] : ''; ?>" class="box"><br><br>

                <span>Email: </span>
                <input type="email" name="update_email" value="<?php echo isset($fetch['email']) ? $fetch['email'] : ''; ?>" class="box"><br><br>

                <span>Contact Number: </span>
                <input type="tel" name="update_contact_no" value="<?php echo isset($fetch['contact_no']) ? $fetch['contact_no'] : ''; ?>" class="box"><br><br>

               <span>Contact Person</span>
               <input type="text" name="update_contact_person" value="<?php echo isset($fetch['contact_person']) ? $fetch['contact_person'] : ''; ?>" class="box"><br><br>

                <span>Bio: </span>
                <h5>Tell a little something about your business</h5>
                <textarea name="message" rows="5" cols="50" class="box" placeholder="Type Here..."><?php echo isset($fetch['bio']) ? $fetch['bio'] : ''; ?></textarea><br><br>

                <span>Upload Business Permit: </span>
                <input type="file" name="update_business_permit" class="box"><br>

                <?php if (!empty($fetch['business_permit_url'])): ?>
                    <p>Business Permit: <a href="<?php echo $fetch['business_permit_url']; ?>" target="_blank">View</a> | <a download="<?php echo basename($fetch['business_permit_url']); ?>" href="<?php echo $fetch['business_permit_url']; ?>">Download</a></p><br>
                <?php endif; ?>

                <input type="submit" value="Save Changes" name="update_profile" class="btn">
            </div>
        </div>
    </form>
</div>

</body>
</html>
