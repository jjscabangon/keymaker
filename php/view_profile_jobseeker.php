<?php
include 'db_connection.php';
session_start();

$user_id = $_SESSION['user_id']; 
$viewed_user_id = $_GET['user_id'] ?? $user_id; 

$select_query = "
    SELECT 
        u.username, u.email, u.contact_no, u.status, 
        up.display_name, up.profile_picture_url, up.bio, 
        up.resume_url, up.cv_url, up.portfolio, up.is_verified 
    FROM 
        users u 
    JOIN 
        users_profile up 
    ON 
        u.user_id = up.user_id 
    WHERE 
        u.user_id = '$viewed_user_id'
";

$result = mysqli_query($conn, $select_query) or die('Query failed: ' . mysqli_error($conn));
$fetch = mysqli_fetch_assoc($result);

if (!$fetch) {
    die('User profile not found.');
}


$profile_picture = isset($fetch['profile_picture_url']) && $fetch['profile_picture_url'] != "" 
    ? "../img_profiles/" . $fetch['profile_picture_url'] 
    : '../images/default-avatar.png';


$is_verified = isset($fetch['is_verified']) && $fetch['is_verified'] == 1;


$feedback_query = "
    SELECT 
        rating, comment 
    FROM 
        jobseeker_ratings 
    WHERE 
        target_user_id = '$viewed_user_id'
";
$feedback_result = mysqli_query($conn, $feedback_query);
$feedbacks = mysqli_fetch_all($feedback_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
    <link rel="stylesheet" href="../css/viewprofile.css">
</head>
<body>
    <div class="container">
       
        <a href="javascript:history.back()" class="go-back">Go Back</a>

        
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
            <div>
                <h1><?php echo htmlspecialchars($fetch['display_name']); ?></h1>
                <p class="<?php echo $is_verified ? 'verified' : 'not-verified'; ?>">
                    <?php echo $is_verified ? 'Verified User' : 'Not Verified'; ?>
                </p>
            </div>
        </div>

       
        <div class="profile-info">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($fetch['email']); ?></p>
            <p><strong>Contact No:</strong> <?php echo htmlspecialchars($fetch['contact_no']); ?></p>
            <p><strong>Bio:</strong> <?php echo htmlspecialchars($fetch['bio']); ?></p>
        </div>

    
        <div class="section">
            <h2>Downloads</h2>
            <div class="download-links">
                <?php if (!empty($fetch['resume_url'])): ?>
                    <a href="<?php echo htmlspecialchars($fetch['resume_url']); ?>" target="_blank">Download Resume</a>
                <?php endif; ?>
                <?php if (!empty($fetch['cv_url'])): ?>
                    <a href="<?php echo htmlspecialchars($fetch['cv_url']); ?>" target="_blank">Download CV</a>
                <?php endif; ?>
                <?php if (!empty($fetch['portfolio'])): ?>
                    <a href="<?php echo htmlspecialchars($fetch['portfolio']); ?>" target="_blank">View Portfolio</a>
                <?php endif; ?>
            </div>
        </div>

       
        <div class="section feedback-section">
            <h2>Feedback</h2>

            <div class="section">
                <a href="../php/feedbacks/feedback_jobseeker.php?user_id=<?php echo $viewed_user_id; ?>" class="give-feedback-btn">
                    Give Feedback
                </a>
            </div>

            <?php if ($feedbacks): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback">
                        <p><strong>Rating:</strong> <?php echo htmlspecialchars($feedback['rating']); ?> / 5.0</p>
                        <p><strong>Comment:</strong> <?php echo htmlspecialchars($feedback['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No feedback available.</p>
            <?php endif; ?>

    
        </div>

        <?php if ($viewed_user_id == $user_id): ?>
            <div class="section">
                <a href="edit_profile_jobseeker.php?user_id=<?php echo $user_id; ?>" class="edit-profile-btn">Edit Profile</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
