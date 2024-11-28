<?php
include ('../db_connection.php');

// Start session and get user_id from session
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: loginPage.php');
    exit();
}

$user_id = $_SESSION['user_id'];  // The logged-in user's ID

// Handle feedback form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $viewed_user_id = $_POST['user_id']; // The user being rated

    // Validate the rating and comment
    if (empty($rating) || empty($comment)) {
        // Redirect with error message
        header("Location: ../../php/feedbacks/feedback_employer.php?error=Rating and comment are required.&user_id=$viewed_user_id");
        exit();
    }

    // Verify that the user_id exists in the database
    $check_user_exists = mysqli_query($conn, "SELECT 1 FROM users WHERE user_id = '$user_id'");
    if (mysqli_num_rows($check_user_exists) == 0) {
        // If user_id doesn't exist in the database, show an error
        header("Location: ../../php/feedbacks/feedback_employer.php?error=User not found.&user_id=$viewed_user_id");
        exit();
    }

    // Insert feedback into the jobseeker_ratings table
    $insert_feedback = "INSERT INTO employer_ratings (user_id, target_user_id, rating, comment, date_posted) 
                        VALUES ('$user_id', '$viewed_user_id', '$rating', '$comment', NOW())";

    if (mysqli_query($conn, $insert_feedback)) {
        // Redirect after successful feedback
        header("Location: ../../php/view_profile_employer.php?user_id=$viewed_user_id"); // Redirect to profile page
    } else {
        // Redirect with error message
        header("Location: ../../php/feedbacks/feedback_employer.php?error=Error: " . mysqli_error($conn) . "&user_id=$viewed_user_id");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../css/feedback_app.css">
    <title>Employer Feedback</title>
</head>
<body>

<div class="wrapper">

    <h3>Employer Feedback</h3>
    
    <!-- Display error or success message if any -->
    <?php if (isset($_GET['error'])): ?>
        <script>
            window.onload = function() {
                alert("<?php echo $_GET['error']; ?>");
            }
        </script>
    <?php endif; ?>

    <!-- Feedback form -->
    <form action="../../php/feedbacks/feedback_employer.php" method="POST">
        <div class="rating">
            <input type="number" name="rating" hidden>
            <i class='bx bx-star star' style="--i: 0;"></i>
            <i class='bx bx-star star' style="--i: 1;"></i>
            <i class='bx bx-star star' style="--i: 2;"></i>
            <i class='bx bx-star star' style="--i: 3;"></i>
            <i class='bx bx-star star' style="--i: 4;"></i>
        </div>

        <textarea name="comment" cols="30" rows="5" placeholder="Your opinion..."></textarea>

        <!-- Hidden input for target user_id -->
        <input type="hidden" name="user_id" value="<?php echo $_GET['user_id']; ?>"> 

        <div class="btn-group">
            <button type="submit" class="btn submit">Submit</button>
            <button type="button" class="btn cancel" onclick="window.history.back();">Cancel</button>
        </div>

    </form>
</div>

<script>
    const allStar = document.querySelectorAll('.rating .star')
    const ratingValue = document.querySelector('[name="rating"]');

    allStar.forEach((item, idx) => {
        item.addEventListener('click', function () {
            let click = 0;
            ratingValue.value = idx + 1;

            allStar.forEach(i => {
                i.classList.replace('bxs-star', 'bx-star');
                i.classList.remove('active');
            });
            for (let i = 0; i < allStar.length; i++) {
                if (i <= idx) {
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
