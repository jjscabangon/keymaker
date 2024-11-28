<?php 
include 'db_connection.php'; 
include '../php/navs/navbar_jobseeker.php';

session_start(); 

// Make sure user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type_id'] != 1) {
    header("Location: loginPage.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Page</title>
    <link rel="stylesheet" href="../css/joblisting.css">
</head>

<body>

<form action="" method="GET">

    <input type="text" name="search" placeholder="Search" style="margin-top: 25px; margin-left: 55px;">
    <button type="submit" name="submit-search">Search</button>

    <select name="job_level" style="margin-top: 10px; margin-left: 100px;">
        <option value="">Job Level</option>
        <option value="Entry Level">Entry Level</option>
        <option value="Mid Level">Mid Level</option>
        <option value="Advanced Level">Advanced Level</option>
    </select>

    <select name="work_location" style="margin-top: 10px; margin-left: 10px;">
        <option value="">Work Location</option>
        <option value="Onsite">Onsite</option>
        <option value="Hybrid">Hybrid</option>
        <option value="Remote">Remote</option>
    </select>

    <select name="employment_type" style="margin-top: 10px; margin-left: 10px;">
        <option value="">Employment Type</option>
        <option value="Full-Time">Full-Time</option>
        <option value="Part-Time">Part-Time</option>
        <option value="Freelance">Freelance</option>
    </select>

    <input type="number" name="salary_from" placeholder="Salary From" min="0" 
        value="<?php echo $salary_from > 0 ? $salary_from : ''; ?>">
    <input type="number" name="salary_to" placeholder="Salary To" min="0" 
        value="<?php echo $salary_to > 0 ? $salary_to : ''; ?>" >

    <button type="submit" style="margin-top: 25px; margin-left: 25px;">Apply Filters</button>
    <button type="reset" style="margin-top: 10px; margin-left: 10px;">Reset Filters</button>

</form>

<h1>Available Jobs</h1>

<div class="job-container">
    <?php
       
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $job_level = isset($_GET['job_level']) ? mysqli_real_escape_string($conn, $_GET['job_level']) : '';
        $work_location = isset($_GET['work_location']) ? mysqli_real_escape_string($conn, $_GET['work_location']) : '';
        $employment_type = isset($_GET['employment_type']) ? mysqli_real_escape_string($conn, $_GET['employment_type']) : '';
        $salary_from = isset($_GET['salary_from']) ? max(0, (int)$_GET['salary_from']) : 0; 
        $salary_to = isset($_GET['salary_to']) ? max(0, (int)$_GET['salary_to']) : 0; 
        
       
        $sql = "SELECT 
                jp.*, 
                jt.title_name, 
                jl.level_name, 
                wl.location_type, 
                et.type_name, 
                jc.category_name,
                COUNT(DISTINCT jss.skill_id) as matching_skills,
                GROUP_CONCAT(DISTINCT 
                    CASE 
                        WHEN jss.skill_id IS NOT NULL THEN s.skill_name 
                    END
                ) as matching_skill_names
            FROM job_postings jp 
            JOIN job_titles jt ON jp.job_title_id = jt.job_title_id 
            JOIN job_levels jl ON jp.job_level_id = jl.job_level_id 
            JOIN work_locations wl ON jp.work_location_id = wl.work_location_id 
            JOIN employment_types et ON jp.employment_type_id = et.employment_type_id 
            JOIN job_categories jc ON jp.job_category_id = jc.job_category_id 
            LEFT JOIN required_skills rs ON jp.job_post_id = rs.job_post_id
            LEFT JOIN skills s ON rs.skill_id = s.skill_id
            LEFT JOIN job_seeker_skills jss ON rs.skill_id = jss.skill_id 
                AND jss.user_id = '$user_id'
            WHERE jp.status = 'open'";
        
       
        if (!empty($search)) {
            $sql .= " AND jt.title_name LIKE '%$search%'";
        }
        
        if (!empty($job_level)) {
            $sql .= " AND jl.level_name = '$job_level'";
        }
        
        if (!empty($work_location)) {
            $sql .= " AND wl.location_type = '$work_location'";
        }
        
        if (!empty($employment_type)) {
            $sql .= " AND et.type_name = '$employment_type'";
        }
        
        if ($salary_from > 0 && $salary_to > 0) {
            $sql .= " AND jp.salary_from >= $salary_from AND jp.salary_to <= $salary_to";
        } elseif ($salary_from > 0) {
            // Only show jobs where the starting salary is equal or higher than the filter
            $sql .= " AND jp.salary_from >= $salary_from";
        } elseif ($salary_to > 0) {
            // Only show jobs where the maximum salary is equal or lower than the filter
            $sql .= " AND jp.salary_to <= $salary_to";
        }
        
        $sql .= " GROUP BY jp.job_post_id
                  ORDER BY matching_skills DESC, jp.date_posted DESC";
        
        $result = mysqli_query($conn, $sql);
        $queryResults = mysqli_num_rows($result);
        
        if ($queryResults == 0) {
            echo "No jobs match your criteria.<br>";
        }

        echo "There are " . $queryResults . " results!<br>";

        if ($queryResults > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $required_skills_sql = "SELECT COUNT(*) as total_required 
                                      FROM required_skills 
                                      WHERE job_post_id = " . $row['job_post_id'];
                $required_result = mysqli_query($conn, $required_skills_sql);
                $required_row = mysqli_fetch_assoc($required_result);
                $total_required = $required_row['total_required'];

                // Calculate match percentage
                $match_percentage = $total_required > 0 ? 
                    round(($row['matching_skills'] / $total_required) * 100) : 0;

                echo "<a href='jobPost.php?job_post_id=".$row['job_post_id']."'>
                      <div class='job'>
                      <h3>Job Title: ".$row['title_name']."</h3>
                      <h3>Job Level: ".$row['level_name']."</h3>
                      <h3>Work Location: ".$row['location_type']."</h3>
                      <h3>Salary: ".$row['salary_from']." - ".$row['salary_to']."</h3>
                      <h3>Address: ".$row['address']."</h3>
                      <h3>Employment Type: ".$row['type_name']."</h3>
                      <h3>Date Posted: ".$row['date_posted']."</h3>
                      <h3>Available Slots: ".$row['available_slots']."</h3>
                      <h3>Skills Match: ".$match_percentage."% (".$row['matching_skills']." out of ".$total_required." skills)</h3>";
                
                if ($row['matching_skill_names']) {
                    echo "<h3>Matching Skills: ".$row['matching_skill_names']."</h3>";
                }
                
                echo "</div></a>";
            }
        }
    ?>
</div>

<!-- Add some CSS for the skills match display -->
<style>
.job h3:last-child {
    color: #b30000;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}
</style>

</body>
</html>
