<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: step1_jobseeker_registration.php");
    exit();
}

if (isset($_POST['save'])) {
    $skills = $_POST['skills'];
    $skill_levels = $_POST['skill_levels'];
    $user_id = $_SESSION['user_id']; 

    foreach ($skills as $index => $skill_id) {
        $skill_level_id = $skill_levels[$index];
       

        $query = "INSERT INTO job_seeker_skills (user_id, skill_id, skill_level_id) 
                  VALUES ('$user_id', '$skill_id', '$skill_level_id')";
        $query_run = mysqli_query($conn, $query);

        if (!$query_run) {
            $_SESSION['status'] = "Failed to save skills.";
            header("Location: step3_jobseeker_registration.php");
            exit();
        }
    }

    $_SESSION['status'] = "Skills inserted successfully";
    header("Location: listingpage_jobs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Skills</title>
    <!--create external css file for this-->
    <style>
        .category-button {
            background-color: #b30000; 
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .skills-list {
            display: none; 
            padding: 10px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }

        .skills-list input {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <form action="" method="POST">
        <!-- Construction Skills -->
         <h1>Choose 3 main skills</h1>
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
        <br>

        <h3>Skill 1</h3>
        <input type="text" id="selected-skill1" readonly/>
                <select name="skill_levels[]">
                    <option value="1">Beginner</option>
                    <option value="2">Intermediate</option>
                    <option value="3">Advanced</option>
                    <option value="4">Expert</option>
                </select>
        
        <h3>Skill 2</h3>
        <input type="text" id="selected-skill2" readonly />
                <select name="skill_levels[]">
                    <option value="1">Beginner</option>
                    <option value="2">Intermediate</option>
                    <option value="3">Advanced</option>
                    <option value="4">Expert</option>
                </select>

        <h3>Skill 3</h3>
        <input type="text" id="selected-skill3" readonly />
                <select name="skill_levels[]">
                    <option value="1">Beginner</option>
                    <option value="2">Intermediate</option>
                    <option value="3">Advanced</option>
                    <option value="4">Expert</option>
                </select>
        
        <br><br>

        <div class="form-group">
            <button type="submit" name="save" class="btn">Submit</button>
        </div>

        
    </form>

    <script src="../javascript/step3_jobseeker_registration.js"></script>
</body>
</html>
