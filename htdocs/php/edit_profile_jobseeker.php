<?php
include 'db_connection.php'; 
include '../php/navs/navbar_jobseeker.php';

session_start();
$user_id = $_SESSION['user_id'];  

// Ensure the page is only accessible by the logged-in user for editing
if (!isset($user_id)) {
    header('Location: login.php');
    exit;
}


$query = "SELECT u.username, u.email, u.contact_no, u.status, 
                 up.display_name, up.profile_picture_url, up.bio, 
                 up.resume_url, up.cv_url, up.portfolio, up.is_verified 
          FROM users u 
          JOIN users_profile up ON u.user_id = up.user_id 
          WHERE u.user_id = '$user_id'";
$select = mysqli_query($conn, $query) or die('Query failed: ' . mysqli_error($conn));
$fetch = mysqli_fetch_assoc($select);

// Default profile picture
$profile_picture = (!empty($fetch['profile_picture_url'])) 
    ? "../img_profiles/" . $fetch['profile_picture_url'] 
    : '../images/default-avatar.png';

$is_verified = $fetch['is_verified'] ?? 0;

// Handle profile update
if (isset($_POST['update_profile'])) {
    $updated_name = mysqli_real_escape_string($conn, $_POST['update_name']);
    $updated_email = mysqli_real_escape_string($conn, $_POST['update_email']);
    $updated_contact_no = mysqli_real_escape_string($conn, $_POST['update_contact_no']);
    $updated_bio = mysqli_real_escape_string($conn, $_POST['message']);

    // Profile picture upload
    if (!empty($_FILES['profile_picture_url']['name'])) {
        $image_name = basename($_FILES['profile_picture_url']['name']);
        $image_path = "../img_profiles/" . $image_name;
        if (move_uploaded_file($_FILES['profile_picture_url']['tmp_name'], $image_path)) {
            $stmt = $conn->prepare("UPDATE users_profile SET profile_picture_url = ? WHERE user_id = ?");
            $stmt->bind_param("si", $image_name, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Resume upload
    if (!empty($_FILES['update_resume']['name'])) {
        $resume_name = basename($_FILES['update_resume']['name']);
        $resume_path = "../user_documents/" . $resume_name;
        if (move_uploaded_file($_FILES['update_resume']['tmp_name'], $resume_path)) {
            $stmt = $conn->prepare("UPDATE users_profile SET resume_url = ? WHERE user_id = ?");
            $stmt->bind_param("si", $resume_path, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // CV upload
    if (!empty($_FILES['update_cv']['name'])) {
        $cv_name = basename($_FILES['update_cv']['name']);
        $cv_path = "../user_documents/" . $cv_name;
        if (move_uploaded_file($_FILES['update_cv']['tmp_name'], $cv_path)) {
            $stmt = $conn->prepare("UPDATE users_profile SET cv_url = ? WHERE user_id = ?");
            $stmt->bind_param("si", $cv_path, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Portfolio upload
    if (!empty($_FILES['update_portfolio']['name'])) {
        $portfolio_name = basename($_FILES['update_portfolio']['name']);
        $portfolio_path = "../user_documents/" . $portfolio_name;
        if (move_uploaded_file($_FILES['update_portfolio']['tmp_name'], $portfolio_path)) {
            $stmt = $conn->prepare("UPDATE users_profile SET portfolio = ? WHERE user_id = ?");
            $stmt->bind_param("si", $portfolio_path, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update profile information
    $stmt = $conn->prepare("UPDATE users_profile SET display_name = ?, bio = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $updated_name, $updated_bio, $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE users SET email = ?, contact_no = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $updated_email, $updated_contact_no, $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/editprofile.css">
</head>
<body>
    <div class="update-profile">
        <form action="" method="post" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
            <span>Add Profile Picture: </span>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-image"><br><br>
                <input type="file" name="profile_picture_url" accept="image/*" class="box"><br><br>


                <span>Status: </span>
                <span class="<?php echo $is_verified ? 'verified' : 'not-verified'; ?>">
                    <?php echo $is_verified ? 'Verified' : 'Not Verified'; ?>
                </span><br><br>

                <span>Name: </span>
                <input type="text" name="update_name" value="<?php echo htmlspecialchars($fetch['display_name']); ?>" class="box"><br><br>

                <span>Email: </span>
                <input type="email" name="update_email" value="<?php echo htmlspecialchars($fetch['email']); ?>" class="box"><br><br>

                <span>Contact Number: </span>
                <input type="tel" name="update_contact_no" value="<?php echo htmlspecialchars($fetch['contact_no']); ?>" class="box"><br><br>

                <span>Add Bio: </span>
                <h5>Tell a little something about yourself</h5>
                <textarea name="message" rows="5" cols="50" class="box"><?php echo htmlspecialchars($fetch['bio']); ?></textarea><br><br>

                <span>Upload Resume: </span>
                <input type="file" name="update_resume" class="box"><br>

                <?php if (!empty($fetch['resume_url'])): ?>
                    <p>Resume: <a href="<?php echo $fetch['resume_url']; ?>" target="_blank">View </a> | <a download="<?php echo basename($fetch['resume_url']); ?>" href="<?php echo $fetch['resume_url']; ?>">Download</a></p><br>
                <?php endif; ?>

                <span>Upload CV: </span>
                <input type="file" name="update_cv" class="box"><br>

                
                <?php if (!empty($fetch['cv_url'])): ?>
                    <p>CV: <a href="<?php echo $fetch['cv_url']; ?>" target="_blank">View</a> | <a download="<?php echo basename($fetch['cv_url']); ?>" href="<?php echo $fetch['cv_url']; ?>">Download</a></p><br>
                <?php endif; ?>

                <span>Upload Portfolio: </span>
                <h5>A portfolio is a collection of your best work to show your skills.</h5>
                <p>If you have multiple files to share, you can upload a document or PDF with a link to your Google Drive folder.</p>
                <input type="file" name="update_portfolio" class="box"><br>

                <?php if (!empty($fetch['portfolio'])): ?>
                    <p>Portfolio: <a href="<?php echo $fetch['portfolio']; ?>" target="_blank">View</a> | <a download="<?php echo basename($fetch['portfolio']); ?>" href="<?php echo $fetch['portfolio']; ?>">Download</a></p><br>
                <?php endif; ?>

                <input type="submit" value="Save Changes" name="update_profile" class="btn">

            </div>
            </div>
        </form>
    </div>
</body>
</html>
