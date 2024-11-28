<?php

include 'db_connection.php';

$job_application_id = isset($_GET['job_application_id']) ? $_GET['job_application_id'] : null;

if ($job_application_id) {
    
    $query = "SELECT ja.status_id, jas.status_name, ja.user_id, ja.job_post_id, jt.title_name
              FROM job_applications ja
              JOIN job_application_status jas ON ja.status_id = jas.status_id
              JOIN job_postings jp ON ja.job_post_id = jp.job_post_id
              JOIN job_titles jt ON jp.job_title_id = jt.job_title_id
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
} else {
    echo "<p>Job application ID is missing!</p>";
    exit;
}


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
            
            <p class="head_1">Track the Application Progress for <span style="color: #ff4732;"><?php echo htmlspecialchars($application['title_name']); ?></span></p><br>
            <p class="head_2">Our job website doesn’t have a messaging system yet. To connect with employers, please reach out to them directly via email.</p>
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

        <!--using switch case for showing the rwsult-->
        <!--notarized contract lnik added here -->
        <?php
    
       
        switch ($application['status_id']) {
            case 1:
                echo "<p class='status-message'>Your application has been submitted.</p>";
                break;
            case 2:
                echo "<p class='status-message'>Your application is under review.</p>";
                break;
            case 3:
                echo "<p class='status-message'>You have been shortlisted. Good luck!</p>";
                break;
            case 4:
                echo "<p class='status-message'>You have an interview scheduled. Prepare well!</p>";
                break;
            case 5:
                echo "<b><p class='status-message'>Congratulations! You have been hired for this position.</p></b><br>"; 
                echo "<p class='status-message'>Please review and sign the <a href='../contract/NOTARIZED-CONTRACT.pdf' target='_blank'>NOTARIZED CONTRACT</a> before proceeding then send to employer via email or onsite.</p><br>";
                echo "<p class='status-message'>Thank you for applying! Share your feedback on your application experience to help us improve. <a href='../php/feedbacks/feedback_app.php?job_post_id=" . $application['job_post_id'] . "&job_application_id=" . $job_application_id . "'>Click here</a> </p>";
                break;
            case 6:
                echo "<p class='status-message'>We're sorry, but you have been rejected for this position.</p><br>";
                echo "<p class='status-message'>Thank you for applying! Share your feedback on your application experience to help us improve. <a href='../php/feedbacks/feedback_app.php?job_post_id=" . $application['job_post_id'] . "&job_application_id=" . $job_application_id . "'>Click here</a> </p>";

                break;
            default:
                echo "<p class='status-message'>Unknown status.</p>";
                break;
        }
        
        ?>

    </div>

   
</body>
</html>
