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
            <li><a href="../php/listingpage_jobs.php">Find Jobs</a></li>
            <li><a href="../php/listingpage_profiles.php">Find Users</a></li>
            <li><a href="../php/listingpage_feedback.php">Application Feedback</a></li>
            
           
        </ul>
        <img src="../images/default-avatar.png" class="user-pic" onclick="toggleMenu()">

        
        <div class="sub-menu-wrap" id="subMenu">
            <div class="sub-menu">
                <a href="../php/edit_profile_jobseeker.php" class="sub-menu-link">
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
