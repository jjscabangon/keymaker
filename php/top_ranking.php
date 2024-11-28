<?php
include 'db_connection.php';
session_start();


$ranking_query = "
    SELECT 
        u.user_id,
        up.display_name,
        up.profile_picture_url,
        up.is_verified,
        COUNT(jr.jobseeker_rating_id) as total_ratings,
        ROUND(AVG(jr.rating), 1) as average_rating,
        ROUND(AVG(jr.rating) * COUNT(jr.jobseeker_rating_id), 1) as ranking_score
    FROM 
        users u
    JOIN 
        users_profile up ON u.user_id = up.user_id
    LEFT JOIN 
        jobseeker_ratings jr ON u.user_id = jr.target_user_id
    WHERE 
        up.user_type_id = 1
    GROUP BY 
        u.user_id, up.display_name, up.profile_picture_url, up.is_verified
    HAVING 
        total_ratings >= 3
    ORDER BY 
        ranking_score DESC
    LIMIT 10
";

$result = mysqli_query($conn, $ranking_query);
$rankings = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Ranked Jobseekers</title>
    <link rel="stylesheet" href="../css/top_ranking.css">

</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">Back</a>
        <h1>Top Ranked Jobseekers</h1>

        <?php 
        $rank = 1;
        foreach ($rankings as $user): 
            $profile_picture = $user['profile_picture_url'] ? "../img_profiles/" . $user['profile_picture_url'] : '../images/default-avatar.png';
        ?>
            <a href="view_profile_jobseeker.php?user_id=<?php echo $user['user_id']; ?>" style="text-decoration: none; color: inherit;">
                <div class="ranking-card">
                    <div>#<?php echo $rank++; ?></div>
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile" class="profile-img">
                    <div>
                        <h3>
                            <?php echo htmlspecialchars($user['display_name']); ?>
                            <?php if ($user['is_verified']): ?>
                                <span class="verified-badge">Verified</span>
                            <?php endif; ?>
                        </h3>
                        <p>Ratings: <?php echo $user['total_ratings']; ?> | Average: ★<?php echo $user['average_rating']; ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>

        <?php if (empty($rankings)): ?>
            <p style="text-align: center;">No ranked jobseekers found.</p>
        <?php endif; ?>
    </div>
</body>
</html>