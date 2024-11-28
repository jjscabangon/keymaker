<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
    <link rel="stylesheet" href="../css/jobdetails.css">
</head>
<body>

<?php
    if (isset($_GET['job_post_id'])) {
        $job_post_id = mysqli_real_escape_string($conn, $_GET['job_post_id']);

        $sql = "SELECT jp.*, jt.title_name, jl.level_name, wl.location_type, et.type_name, jc.category_name, up.display_name, up.contact_person, u.contact_no, u.email
                FROM job_postings jp 
                JOIN job_titles jt ON jp.job_title_id = jt.job_title_id 
                JOIN job_levels jl ON jp.job_level_id = jl.job_level_id 
                JOIN work_locations wl ON jp.work_location_id = wl.work_location_id 
                JOIN employment_types et ON jp.employment_type_id = et.employment_type_id 
                JOIN job_categories jc ON jp.job_category_id = jc.job_category_id 
                JOIN users_profile up ON jp.user_id = up.user_id  
                 JOIN users u ON jp.user_id = u.user_id
                WHERE jp.job_post_id = '$job_post_id' AND jp.status = 'open'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            echo "<div class='job-details'>
                  <h1>".$row['title_name']."</h1>
                  <p><strong>Employer Name:</strong> ".$row['display_name']. "</p>
                  <p><strong>Contact Person:</strong> ".$row['contact_person']. "</p>   
                  <p><strong>Contact Number:</strong> ".$row['contact_no']."</p>  
                  <p><strong>Email:</strong> ".$row['email']."</p>           
                  <p><strong>Job Level:</strong> ".$row['level_name']."</p>
                  <p><strong>Location:</strong> ".$row['location_type']."</p>
                  <p><strong>Employment Type:</strong> ".$row['type_name']."</p>
                  <p><strong>Job Category:</strong> ".$row['category_name']."</p>
                  <p><strong>Job Description:</strong> ".$row['description']."</p>
                  <p><strong>Salary:</strong> ".$row['salary_from']." - ".$row['salary_to']."</p>
                  <p><strong>Address:</strong> ".$row['address']."</p>
                  <p><strong>Date Posted:</strong> ".$row['date_posted']."</p>
                  <p><strong>Available Slots:</strong> ".$row['available_slots']."</p>
                  <p><strong>Status:</strong> ".$row['status']."</p>
                  </div>";

           
                  echo "<form action='application_page.php' method='GET'>
                            <input type='hidden' name='job_post_id' value='" . $job_post_id . "'>
                            <button type='submit'>Apply</button>
                        </form>";
          
        } else {
            echo "No job details found.";
        }
    } else {
        echo "No job selected.";
    }
?>

</body>
</html>
