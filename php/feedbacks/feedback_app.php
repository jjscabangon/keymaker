<?php
include('../db_connection.php'); 

session_start();
if (!isset($_SESSION['user_id'])) {
    die("User is not logged in. Please log in first.");
}

$user_id = $_SESSION['user_id'];

// Check if job_post_id and job_application_id are set in the URL
if (isset($_GET['job_application_id']) && isset($_GET['job_post_id'])) {
    $job_application_id = $_GET['job_application_id'];
    $job_post_id = $_GET['job_post_id'];
} else {
    die("Missing job application or job post ID.");
}

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the rating and comment from the form
    $rating = isset($_POST['rating']) ? $_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    $job_post_id = isset($_POST['job_post_id']) ? $_POST['job_post_id'] : null;
    $job_application_id = isset($_POST['job_application_id']) ? $_POST['job_application_id'] : null;

    // Validate the rating (it should be between 1 and 5)
    if ($rating < 1 || $rating > 5) {
        echo "Invalid rating. Please select a valid star rating.";
        exit;
    }

    // Prepare the SQL query to insert the feedback into the app_ratings table
    $sql = "INSERT INTO app_ratings (user_id, job_post_id, job_application_id, comment, rating, date_posted) 
            VALUES (?, ?, ?, ?, ?, NOW())";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('iiisd', $user_id, $job_post_id, $job_application_id, $comment, $rating); // 'i' for integer, 's' for string, 'd' for decimal

        // Execute the query
        if ($stmt->execute()) {
            // Set a session variable to show success message
            $_SESSION['feedback_success'] = "Thank you for your feedback!";
            // Redirect after submission (with job_post_id and job_application_id in the URL)
            header("Location: feedback_app.php?job_post_id=$job_post_id&job_application_id=$job_application_id");
            exit;
        } else {
            echo "Error: Could not submit feedback.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error: Could not prepare the query.";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'> <!--star icon-->
    <link rel="stylesheet" href="../../css/feedback_app.css">
    <title>Application Feedback</title>
</head>
<body>

<div class="wrapper">

    <h3>Application Feedback</h3>
    
    <!-- Display success message if feedback was successfully submitted -->
    <?php if (isset($_SESSION['feedback_success'])): ?>
        <script>
            window.onload = function() {
                alert("<?php echo $_SESSION['feedback_success']; ?>");
            }
        </script>
        <?php unset($_SESSION['feedback_success']); ?>
    <?php endif; ?>
    
    <!-- Ensure that the form action includes the job_post_id and job_application_id in the URL -->
    <form action="feedback_app.php?job_post_id=<?php echo $job_post_id; ?>&job_application_id=<?php echo $job_application_id; ?>" method="POST">
        <div class="rating">
            <input type="number" name="rating" hidden>
            <i class='bx bx-star star' style="--i: 0;"></i>
            <i class='bx bx-star star' style="--i: 1;"></i>
            <i class='bx bx-star star' style="--i: 2;"></i>
            <i class='bx bx-star star' style="--i: 3;"></i>
            <i class='bx bx-star star' style="--i: 4;"></i>
        </div>

        <textarea name="comment" cols="30" rows="5" placeholder="Your opinion..."></textarea>

        <!-- Hidden inputs to pass the job_post_id and job_application_id -->
        <input type="hidden" name="job_post_id" value="<?php echo htmlspecialchars($job_post_id); ?>">
        <input type="hidden" name="job_application_id" value="<?php echo htmlspecialchars($job_application_id); ?>">

        <div class="btn-group">
            <button type="submit" class="btn submit">Submit</button>
            <button type="button" class="btn cancel" onclick="window.location.href='index.php';">Cancel</button>
        </div>

    </form>
</div>

<script>
    const allStar = document.querySelectorAll('.rating .star')
    const ratingValue = document.querySelector('[name="rating"]');

    allStar.forEach((item, idx)=> {
        item.addEventListener('click', function () {
            let click = 0;
            ratingValue.value = idx + 1;

            allStar.forEach(i=> {
                i.classList.replace('bxs-star', 'bx-star');
                i.classList.remove('active');
            });
            for(let i=0; i<allStar.length; i++) {
                if(i <= idx) {
                    allStar[i].classList.replace('bx-star', 'bxs-star');
                    allStar[i].classList.add('active');
                } else {
                    allStar[i].style.setProperty('--i', click);
                    click++;
                }
            }
        });
    });
</script>

</body>
</html>
