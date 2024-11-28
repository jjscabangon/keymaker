<?php
session_start();

include('db_connection.php');

if (isset($_GET['job_post_id'])) {
    $job_post_id = $_GET['job_post_id'];
} else {
    die("Invalid job post. Please go back and select a job to apply for.");
}


// Validate job_post_id exists in job_postings table
$stmt_job_check = $conn->prepare("SELECT COUNT(*) FROM job_postings WHERE job_post_id = ?");
$stmt_job_check->bind_param("i", $job_post_id);
$stmt_job_check->execute();
$stmt_job_check->bind_result($job_exists);
$stmt_job_check->fetch();
$stmt_job_check->close();


if ($job_exists == 0) {
    die("Invalid job post. Please go back and select a job to apply for.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // Get user profile ID
    $stmt_profile_id = $conn->prepare("SELECT user_profile_id FROM users_profile WHERE user_id = ?");
    $stmt_profile_id->bind_param("i", $user_id);
    $stmt_profile_id->execute();
    $stmt_profile_id->bind_result($user_profile_id);
    $stmt_profile_id->fetch();
    $stmt_profile_id->close();

    
    $resumePath = "../user_documents/" . basename($_FILES['resume_url']['name']);
    $cvPath = "../user_documents/" . basename($_FILES['cv_url']['name']);
    
    // Move files and insert data if upload successful
    if (move_uploaded_file($_FILES['resume_url']['tmp_name'], $resumePath) &&
        move_uploaded_file($_FILES['cv_url']['tmp_name'], $cvPath)) {

        $stmt_update_profile = $conn->prepare("UPDATE users_profile SET resume_url = ?, cv_url = ? WHERE user_profile_id = ?");
        $stmt_update_profile->bind_param("ssi", $resumePath, $cvPath, $user_profile_id);

        if ($stmt_update_profile->execute()) {
            $status_id = 1; //default value
            $applied_at = date("Y-m-d H:i:s");

            $stmt_application = $conn->prepare("INSERT INTO job_applications (user_id, user_profile_id, job_post_id, status_id, applied_at) VALUES (?, ?, ?, ?, ?)");
            $stmt_application->bind_param("iiiis", $user_id, $user_profile_id, $job_post_id, $status_id, $applied_at);

            if ($stmt_application->execute()) {
                $job_application_id = $stmt_application->insert_id;
                header("Location: ../php/tracking_jobseeker.php?job_application_id=" . $job_application_id);
                exit();
            } else {
                $error_message = "Failed to submit application: " . $stmt_application->error;
            }
        } else {
            $error_message = "Failed to update profile information: " . $stmt_update_profile->error;
        }
    } else {
        $error_message = "Sorry, there was an error uploading your files.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application</title>
    <link rel="stylesheet" href="../css/styles.css"> 
</head>
<body>
    <div class="container">
        <h2>Submit Your Application</h2>
        <br>
        <form action="application_page.php?job_post_id=<?php echo htmlspecialchars($job_post_id); ?>" method="POST" enctype="multipart/form-data">
        <h3>Upload your resume and CV</h3>
            <br>

            <label for="resume">Upload Resume:</label>
            <input type="file" id="resume_url" name="resume_url" required><br><br>

            <label for="cv">Upload CV:</label>
            <input type="file" id="cv_url" name="cv_url" required><br><br>

            <input type="submit" name="submit" value="Submit Application" class="form-btn">

            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
