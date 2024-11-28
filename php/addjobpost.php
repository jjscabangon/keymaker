<?php 
session_start();
include 'db_connection.php'; 
include '../php/navs/navbar_employer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type_id'] != 2) {
    header("Location: loginPage.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch employer profile info
$profile_sql = "SELECT up.display_name, up.contact_person, u.contact_no, u.email 
                FROM users_profile up
                JOIN users u ON up.user_id = u.user_id
                WHERE up.user_id = '$user_id'";
$profile_result = mysqli_query($conn, $profile_sql);

if (mysqli_num_rows($profile_result) > 0) {
    $profile_row = mysqli_fetch_assoc($profile_result);
    $display_name = $profile_row['display_name'];
    $contact_person = $profile_row['contact_person'];
    $contact_no = $profile_row['contact_no'];
    $email = $profile_row['email'];
} else {
    $display_name = 'No Display Name';
    $contact_person = 'No Contact Person';
    $contact_no = 'No Contact Number';
    $email = 'No Email';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $job_level_id = mysqli_real_escape_string($conn, $_POST['job_level_id']);
    $work_location_id = mysqli_real_escape_string($conn, $_POST['work_location_id']);
    $employment_type_id = mysqli_real_escape_string($conn, $_POST['employment_type_id']);
    $job_category_id = mysqli_real_escape_string($conn, $_POST['job_category_id']);
    $salary_from = mysqli_real_escape_string($conn, $_POST['salary_from']);
    $salary_to = mysqli_real_escape_string($conn, $_POST['salary_to']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $available_slots = mysqli_real_escape_string($conn, $_POST['available_slots']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $status = 'open';  
    $date_posted = date('Y-m-d H:i:s'); 
    
    // Check if job title exists
    $check_title_sql = "SELECT job_title_id FROM job_titles WHERE title_name = '$job_title'";
    $title_result = mysqli_query($conn, $check_title_sql);

    if (mysqli_num_rows($title_result) > 0) {
        $title_row = mysqli_fetch_assoc($title_result);
        $job_title_id = $title_row['job_title_id'];
    } else {
        $insert_title_sql = "INSERT INTO job_titles (title_name) VALUES ('$job_title')";
        if (mysqli_query($conn, $insert_title_sql)) {
            $job_title_id = mysqli_insert_id($conn); 
        } else {
            echo "Error adding job title: " . mysqli_error($conn);
            exit; 
        }
    }

    // Insert job posting
    $sql = "INSERT INTO job_postings (user_id, job_title_id, job_level_id, work_location_id, 
            employment_type_id, job_category_id, salary_from, salary_to, address, date_posted, 
            status, available_slots, description)
            VALUES ('$user_id', '$job_title_id', '$job_level_id', '$work_location_id', 
            '$employment_type_id', '$job_category_id', '$salary_from', '$salary_to', '$address', 
            '$date_posted', '$status', '$available_slots', '$description')";

    if (mysqli_query($conn, $sql)) {
        $job_post_id = mysqli_insert_id($conn);
        
        // Insert required skills
        if (isset($_POST['skills']) && is_array($_POST['skills'])) {
            foreach ($_POST['skills'] as $skill_id) {
                $skill_id = mysqli_real_escape_string($conn, $skill_id);
                $skill_sql = "INSERT INTO required_skills (job_post_id, skill_id) 
                             VALUES ('$job_post_id', '$skill_id')";
                mysqli_query($conn, $skill_sql);
            }
            echo "<script>alert('Job and required skills successfully posted!');</script>";
        } else {
            echo "<script>alert('Job posted but no skills were selected.');</script>";
        }
    } else {
        echo "<script>alert('Error posting job: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job Post</title>
    <link rel="stylesheet" href="../css/jobpost.css">
    <style>
        .skills-list {
            display: none;
            padding: 10px;
            border: 1px solid #ccc;
            margin: 5px 0;
        }
        
        .category-button {
            background-color: #b30000;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            margin: 5px 0;
        }

        #selected-skill1, #selected-skill2, #selected-skill3 {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h2>Add New Job Posting</h2>
    <br>

    <form action="addjobpost.php" method="POST">
        <label for="display_name">Business Name:</label>
        <input type="text" name="display_name" value="<?php echo htmlspecialchars($display_name); ?>" readonly><br><br>

        <label for="contact_person">Contact Person:</label>
        <input type="text" name="contact_person" value="<?php echo htmlspecialchars($contact_person); ?>"><br><br> <!--remove readonly-->

        <label for="contact_no">Contact Number:</label>
        <input type="text" name="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>" readonly><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly><br><br>


        <label for="job_title">Job Title:</label>
        <input type="text" name="job_title" required><br><br>

        <label for="job_level_id">Job Level:</label>
        <select name="job_level_id" required>
            <option value="" disabled selected>Select a Job Level</option>
            <?php
                $level_sql = "SELECT * FROM job_levels";
                $level_result = mysqli_query($conn, $level_sql);
                while ($level_row = mysqli_fetch_assoc($level_result)) {
                    echo "<option value='".$level_row['job_level_id']."'>".$level_row['level_name']."</option>";
                }
            ?>
            </select><br><br>

        <label for="work_location_id">Work Location:</label>
        <select name="work_location_id" required>
            <option value="" disabled selected>Select Work Location</option>
            <?php
                $location_sql = "SELECT * FROM work_locations";
                $location_result = mysqli_query($conn, $location_sql);
                while ($location_row = mysqli_fetch_assoc($location_result)) {
                    echo "<option value='".$location_row['work_location_id']."'>".$location_row['location_type']."</option>";
                }
            ?>
        </select><br><br>

        <label for="employment_type_id">Employment Type:</label>
        <select name="employment_type_id" required>
            <option value="" disabled selected>Select Employment Type</option>
            <?php
                $type_sql = "SELECT * FROM employment_types";
                $type_result = mysqli_query($conn, $type_sql);
                while ($type_row = mysqli_fetch_assoc($type_result)) {
                    echo "<option value='".$type_row['employment_type_id']."'>".$type_row['type_name']."</option>";
                }
            ?>
        </select><br><br>

        <label for="job_category_id">Job Category:</label>
        <select name="job_category_id" required>
            <option value="" disabled selected>Select Job Category</option>
            <?php
                $category_sql = "SELECT * FROM job_categories";
                $category_result = mysqli_query($conn, $category_sql);
                while ($category_row = mysqli_fetch_assoc($category_result)) {
                    echo "<option value='".$category_row['job_category_id']."'>".$category_row['category_name']."</option>";
                }
            ?>
        </select><br><br>

        <label for="salary_from">Salary From:</label>
        <input type="number" name="salary_from" required><br><br>

        <label for="salary_to">Salary To:</label>
        <input type="number" name="salary_to" required><br><br>

        <label for="address">Address:</label>
        <input type="text" name="address" required><br><br>

        <label for="available_slots">Available Slots:</label>
        <input type="number" name="available_slots" required><br><br>

        <label for="description">Job Description:</label>
        <input type="text" name="description" required><br><br>

        <div id="invalid" style="color: red;"></div>

        <h3>Selected Required Skills:</h3>
        <h4>Skill 1</h4>
        <input type="text" id="selected-skill1" readonly/>
        
        <h4>Skill 2</h4>
        <input type="text" id="selected-skill2" readonly/>
        
        <h4>Skill 3</h4>
        <input type="text" id="selected-skill3" readonly/>
        
        <br><br>

        <h3>Required Skills for the Job</h3>
        <div class="form-group">
            <button type="button" class="category-button" onclick="toggleSkills('construction-skills')">Construction</button>

            <div class="skills-list" id="construction-skills">
                <label><input type="checkbox" name="skills[]" value="1" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Welding</label><br>
                <label><input type="checkbox" name="skills[]" value="2" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Concrete Work (Trabaho sa Semento)</label><br>
                <label><input type="checkbox" name="skills[]" value="3" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Roofing (Pagtatakip ng bubong)</label><br>
                <label><input type="checkbox" name="skills[]" value="4" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Carpentry (Karpinterya)</label><br>
                <label><input type="checkbox" name="skills[]" value="5" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Masonry (Masonerya)</label><br>
                <label><input type="checkbox" name="skills[]" value="6" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Painting (Pagpipinta)</label><br>
                <label><input type="checkbox" name="skills[]" value="7" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Tiling (Paglalagay ng Bato/ Tiles)</label><br>
                <label><input type="checkbox" name="skills[]" value="8" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Demolition (Demolisyon)</label><br>
                <label><input type="checkbox" name="skills[]" value="9" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Shoveling (Pagpapala)</label><br>
                <label><input type="checkbox" name="skills[]" value="10" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Sanding (Pagliliha)</label><br>

            </div>
        </div>

        <br>
        <!-- Cleaning Skills -->
        <div class="form-group">
            <button type="button" class="category-button" onclick="toggleSkills('cleaning-skills')">Cleaning</button>

            <div class="skills-list" id="cleaning-skills">
                <label><input type="checkbox" name="skills[]" value="11" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Laundry (Paglalaba)</label><br>
                <label><input type="checkbox" name="skills[]" value="12" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Derusting (Pagtanggal ng kalawang)</label><br>
                <label><input type="checkbox" name="skills[]" value="13" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Gutter Cleaning (Paglilinis ng Imbornal)</label><br>
                <label><input type="checkbox" name="skills[]" value="14" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Pressure Washing (Paghuhugas ng Presyon)</label><br>
                <label><input type="checkbox" name="skills[]" value="15" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Tree Trimming (Pagputol ng Sanga)</label><br>
                <label><input type="checkbox" name="skills[]" value="16" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Pest Control</label><br>
                <label><input type="checkbox" name="skills[]" value="17" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Sweeping (Pagwawalis)</label><br>
                <label><input type="checkbox" name="skills[]" value="18" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Surface Scrubbing (Paglilinis ng ibabaw)</label><br>
                <label><input type="checkbox" name="skills[]" value="19" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Mopping (Pagpupunas ng sahig)</label><br>
                <label><input type="checkbox" name="skills[]" value="20" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Vacuuming (Pag-vacuum)</label><br>
            </div>
        </div>

        <br>
        <!-- food service Skills -->
        <div class="form-group">
            <button type="button" class="category-button" onclick="toggleSkills('food-service-skills')">Food Service</button>

            <div class="skills-list" id="food-service-skills">
                <label><input type="checkbox" name="skills[]" value="21" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Dishwashing (Paghuhugas ng Pinggan)</label><br>
                <label><input type="checkbox" name="skills[]" value="22" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Food Stocking (Pagaayos ng pagkain ex.delata)</label><br>
                <label><input type="checkbox" name="skills[]" value="23" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Food Preparation (Paghahanda ng Pagkain)</label><br>
                <label><input type="checkbox" name="skills[]" value="24" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Serving (Paghahain)</label><br>
                <label><input type="checkbox" name="skills[]" value="25" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Table Bussing (Paglilinis ng Mesa)</label><br>
                <label><input type="checkbox" name="skills[]" value="26" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Order Taking (Pagtanggap ng Order)</label><br>
                <label><input type="checkbox" name="skills[]" value="27" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Delivering (Paghahatid)</label><br>
                <label><input type="checkbox" name="skills[]" value="28" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Cooking (Pagluluto)</label><br>
                <label><input type="checkbox" name="skills[]" value="29" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Bagging (Pagbabalot)</label><br>
                <label><input type="checkbox" name="skills[]" value="30" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Meat Cutting (Paghihiwa ng karne)</label><br>
            </div>
        </div>

        <br>
         <!-- gardening Skills -->
        <div class="form-group">
            <button type="button" class="category-button" onclick="toggleSkills('gardening-skills')">Gardening & Landscaping</button>

            <div class="skills-list" id="gardening-skills">
                <label><input type="checkbox" name="skills[]" value="31" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Leaf Blowing</label><br>
                <label><input type="checkbox" name="skills[]" value="32" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Planting (Pagtatanim)</label><br>
                <label><input type="checkbox" name="skills[]" value="33" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Pruning & Trimming (Pagputol/pag-iksi ng mga sangga)</label><br>
                <label><input type="checkbox" name="skills[]" value="34" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Weeding  (Pagtatanggal ng Damo)</label><br>
                <label><input type="checkbox" name="skills[]" value="35" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Watering (Pagdidilig)</label><br>
            </div>

        <br><br>
        <!--freelance & side hustle skills-->
        <div class="form-group">
            <button type="button" class="category-button" onclick="toggleSkills('freelance-skills')">Freelance & Side Hustle</button>

            <div class="skills-list" id="freelance-skills">
                <label><input type="checkbox" name="skills[]" value="36" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Copywriting</label><br>
                <label><input type="checkbox" name="skills[]" value="37" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Graphic Design</label><br>
                <label><input type="checkbox" name="skills[]" value="38" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Video Editing</label><br>
                <label><input type="checkbox" name="skills[]" value="39" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Web Design</label><br>
                <label><input type="checkbox" name="skills[]" value="40" class="skills" onclick="return limitfunc()" onchange="updateTextbox()">Photography</label><br>
            </div>
        </div>

        <button type="submit">Add Job</button>
    </form>

    <script src="../javascript/step3_jobseeker_registration.js"></script>
</body>
</html>
