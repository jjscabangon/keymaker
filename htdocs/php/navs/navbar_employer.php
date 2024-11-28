<?php

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    include 'db_connection.php';  

    
    $sql = "SELECT users_profile.profile_picture_url
            FROM users
            INNER JOIN users_profile ON users.user_id = users_profile.user_id
            WHERE users.user_id = ?";

    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);  
    }

    $stmt->bind_param("i", $user_id);

   
    if ($stmt->execute()) {
       
        $result = $stmt->get_result();

     
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $profile_picture_url = $row['profile_picture_url'];
        } else {
            $profile_picture_url = '../images/default-avatar.png'; 
          
 
        }
    } else {
       
        die('Execution error: ' . $stmt->error);
    }

} 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/navbar.css">
</head>

<body>
    
<header class="header">
    <img class="logo" src="../images/KMLogo1.png" alt="logo">
    <nav>
        <ul class="nav_links">
            <li><a href="../php/listingpage_profiles.php">Find Users</a></li>
            <li><a href="../php/addjobpost.php">Add Jobs</a></li>
            <li><a href="../php/listingpage_feedback.php">Application Feedback</a></li>
        </ul>

        <img src="../img_profiles/<?php echo $profile_picture_url; ?>" class="user-pic" onclick="toggleMenu()">

        

       
        <div class="sub-menu-wrap" id="subMenu">
            <div class="sub-menu">
                <a href="../php/edit_profile_employer.php" class="sub-menu-link">
                    <p>Profile</p>
                </a>

                <a href="../html/privacy_policy.html" class="sub-menu-link">
                    <p>Settings and Privacy</p>
                </a>

                <a href="../php/logout.php" class="sub-menu-link">
                    <p>Logout</p>
                </a>
            </div>
        </div>
    </nav>
</header>

<script>
  let subMenu = document.getElementById("subMenu");

  function toggleMenu(){
    subMenu.classList.toggle("open-menu");
  }
</script>
</body>
</html>
