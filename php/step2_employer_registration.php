<?php
session_start(); 

include('db_connection.php'); 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/step1_employer_registration.php"); 
    exit();
}

$error_message = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['skip'])) {
        header("Location: ../php/listingpage_profiles.php");
        exit();
    }
   
    $contact_person = trim($_POST['contact_person']);
    
    
    $business_permitPath = "../doc_validation/" . basename($_FILES['business_permit_url']['name']);

   
    if (move_uploaded_file($_FILES['business_permit_url']['tmp_name'], $business_permitPath)) {
        
       

       
        $stmt_profile = $conn->prepare("UPDATE users_profile SET business_permit_url = ?, contact_person = ? WHERE user_id = ?");
        $stmt_profile->bind_param("ssi", $business_permitPath, $contact_person, $user_id);

       
        if ($stmt_profile->execute()) {
           
            header("Location: ../php/listingpage_profiles.php"); 
            exit();
        } else {
            $error_message = "Failed to update profile information: " . $stmt_profile->error;
        }
    } else {
        $error_message = "Sorry, there was an error uploading your business permit.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Employer Sign Up - Step 2</title>
        
    </head>
<body class="signup">
        <div class="signup-container">
            <form action="" method="POST" enctype="multipart/form-data">

            <div class="user-details">
            <h3>Business Information</h3>


            <div class="input-box">
                <label for="contact-person">Contact Person</label>
                <input type="text" name="contact_person" required />  <!--decide if it will be required-->
            </div>
            <br>

            <label for="business-permit">Upload Business Permit:</label>
            <input type="file" id="business_permit_url" name="business_permit_url"><br><br>

            </div>
            <input type="submit" name="submit" value="Next" class="form-btn">

            <input type="submit" name="skip" value="Skip" class="form-btn">

                        <?php if ($error_message): ?>
                            <p class="error-message"><?php echo $error_message; ?></p>
                        <?php endif; ?>
                    </form>
                </div>
    
</body>
</html>
