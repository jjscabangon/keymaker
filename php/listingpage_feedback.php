<?php

include 'db_connection.php';

session_start();


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    include 'db_connection.php';


    $sql = "SELECT users_profile.user_type_id 
            FROM users
            INNER JOIN users_profile ON users.user_id = users_profile.user_id
            WHERE users.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userType = $row['user_type_id'];

       
        if ($userType == 1) {
            include 'navs/navbar_jobseeker.php';  
        } elseif ($userType == 2) {
            include 'navs/navbar_employer.php';  
        } else {
            echo "Unknown user type.";          
        }
    } else {
        header("Location: loginPage.php"); 
        exit();
    }
    $stmt->close();
} else {
    header("Location: loginPage.php");  
    exit();
}

 
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

$query = "
    SELECT ar.rating, ar.comment, ar.date_posted, up.display_name
    FROM app_ratings ar
    JOIN users_profile up ON ar.user_id = up.user_id
    WHERE ar.comment LIKE ? OR up.display_name LIKE ?
    ORDER BY ar.date_posted DESC
";

$stmt = $conn->prepare($query);

$search_term = '%' . $search_query . '%';
$stmt->bind_param('ss', $search_term, $search_term);

$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Feedback Listings</title>
    <link rel="stylesheet" href="../css/feedbacklisting.css"> 
</head>
<body>

    <div class="feedback-container">
        <h2>Application Feedbacks</h2>

      
        <form method="GET" action="listingpage_feedback.php">
            <input type="text" name="search" placeholder="Search feedback..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <?php
        if ($result->num_rows > 0) {
            // Loop through each feedback
            while ($row = $result->fetch_assoc()) {
                echo "<div class='feedback-item'>";
                echo "<div class='feedback-header'>";
                echo "<p><strong>Submitted by:</strong> " . htmlspecialchars($row['display_name']) . "</p>";
                echo "<p><strong>Rating:</strong> " . $row['rating'] . " stars</p>";
                echo "</div>";
                echo "<div class='feedback-comment'>";
                echo "<p><strong>Comment:</strong> " . $row['comment'] . "</p>";
                echo "</div>";
                echo "<div class='feedback-date'>";
                echo "<p><strong>Posted on:</strong> " . date("F j, Y, g:i a", strtotime($row['date_posted'])) . "</p>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No feedback available.</p>";
        }
        ?>

    </div>

</body>
</html>

<?php

$stmt->close();
$conn->close();
?>
