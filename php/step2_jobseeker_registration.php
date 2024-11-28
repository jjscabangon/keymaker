<?php
session_start(); 

include('db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/step1_jobseeker_registration.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['skip'])) {
        header("Location: ../php/step3_jobseeker_registration.php");
        exit();
    }
   
    $resumePath = "../user_documents/" . basename($_FILES['resume_url']['name']);
    $cvPath = "../user_documents/" . basename($_FILES['cv_url']['name']);
    $clearancePath = "../doc_validation/" . basename($_FILES['clearance_url']['name']);

    
    if (move_uploaded_file($_FILES['resume_url']['tmp_name'], $resumePath) &&
        move_uploaded_file($_FILES['cv_url']['tmp_name'], $cvPath) &&  
        move_uploaded_file($_FILES['clearance_url']['tmp_name'], $clearancePath) ) {
        

       
        $stmt_profile = $conn->prepare("UPDATE users_profile SET resume_url = ?, cv_url = ?, police_clearance_url = ? WHERE user_id = ?");
        $stmt_profile->bind_param("sssi", $resumePath, $cvPath, $clearancePath, $user_id);

        if ($stmt_profile->execute()) {
           
            header("Location: ../php/step3_jobseeker_registration.php"); 
            exit();
        } else {
            $error_message = "Failed to update profile information: " . $stmt_profile->error;
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
    <title>Job Seeker Sign Up - Step 2</title>
    <link rel="stylesheet" href="../css/styles.css"> 
</head>
<body>
    <div class="container">
        <h2>Upload Resume and CV</h2>
        <br>
        <form action="" method="POST" enctype="multipart/form-data">
            <h3>Please upload the necessary documents to proceed.</h3>
            <h4>Accepted document files are pdf, doc, docx</h4>
            <br>
<!--can be edited to make it more approriate-->
<pre>
The information and documents you submit, including your resume,
CV, and police clearance, will be securely stored and used solely
for the purpose of validating your identity. Your privacy is important
to us, and we are committed to protecting your personal information.
</pre>
            <br>
            
            <label for="resume">Upload Resume:</label>
            <input type="file" id="resume_url" name="resume_url" ><br><br>

            <label for="cv">Upload CV:</label>
            <input type="file" id="cv_url" name="cv_url" ><br><br>

            <label for="police-clearance">Upload Police Clearance:</label>
            <input type="file" id="clearance_url" name="clearance_url" ><br><br>

            <input type="submit" name="submit" value="Next" class="form-btn">

            <input type="submit" name="skip" value="Skip" class="form-btn">

            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
