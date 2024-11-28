<?php

include 'db_connection.php';

// Get app id
$job_application_id = $_GET['job_application_id']; 


$query = "SELECT ja.status_id, jas.status_name, ja.user_id, ja.job_post_id 
          FROM job_applications ja
          JOIN job_application_status jas ON ja.status_id = jas.status_id
          WHERE ja.job_application_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $job_application_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();


if (!$application) {
    echo "<p>No application found!</p>";
    exit;
}



    $job_post_query = "SELECT jt.title_name  
                    FROM job_postings jp 
                    JOIN job_titles jt ON jp.job_title_id = jt.job_title_id
                    WHERE jp.job_post_id = ?";
    $job_post_stmt = $conn->prepare($job_post_query);
    $job_post_stmt->bind_param("i", $application['job_post_id']);
    $job_post_stmt->execute();
    $job_post_result = $job_post_stmt->get_result();
    $job_post = $job_post_result->fetch_assoc();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Application Progress</title>
    <link rel="stylesheet" href="../css/tracking.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
    <div class="main">
        <div class="head">
        <p class="head_1">Track the Application Progress for <span style="color: #ff4732;"><?php echo htmlspecialchars($job_post['title_name']); ?></span></p><br>    
        <p class="head_2">To update the application status, select the appropriate step below.</p>
        </div>

        <ul>
            <?php
            $statuses = ['submit_application', 'under_review', 'shortlisted', 'interview', 'hired/rejected'];

            // Loop through each status step and determine if it's active based on the current status_id
            for ($i = 1; $i <= 5; $i++) {
                $is_active = $application['status_id'] >= $i ? "active" : "";
                $status_label = ucfirst(str_replace('_', ' ', $statuses[$i - 1]));

                echo "
                    <li>
                        <div class='progress step-$i $is_active'>
                            <p>$i</p>
                            <i class='uil uil-check'></i>
                        </div>
                        <p class='text'>$status_label</p>
                    </li>
                ";
            }
            ?>

        </ul>
        <br>

        <form method="POST" action="../php/application_status.php">
            <input type="hidden" name="job_application_id" value="<?php echo $job_application_id; ?>">
            <label for="status">Update Status:</label>
            <select name="status_id" id="status" required>
                <option value="2" <?php if ($application['status_id'] == 2) echo 'selected'; ?>>Under Review</option>
                <option value="3" <?php if ($application['status_id'] == 3) echo 'selected'; ?>>Shortlisted</option>
                <option value="4" <?php if ($application['status_id'] == 4) echo 'selected'; ?>>Interview</option>
                <option value="5" <?php if ($application['status_id'] == 5) echo 'selected'; ?>>Hired</option>
                <option value="6" <?php if ($application['status_id'] == 6) echo 'selected'; ?>>Rejected</option>
            </select>
            <button type="submit">Update</button>

        <br><br>

            
        </form>

          <p>View the jobseeker's profile and leave your feedback.
            <a href="../php/profile_jobseeker.php?user_id=<?php echo $application['user_id']; ?>" target="_blank"> View Profile</a>
          </p>

    </div>

    
</body>
</html>


