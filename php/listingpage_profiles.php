<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing & Search Page</title>
    <link rel="stylesheet" href="../css/joblisting.css">
</head>
<body>

    <form action="" method="POST" align="center">
        <input type="text" name="search" placeholder="Search user...">
        <select name="user_type">
            <option value="">All User Types</option>
            <option value="1">Job Seeker</option>
            <option value="2">Employer</option>
        </select>
        <button type="submit" name="submit-search">Search</button>
    </form>

    <div class="user-container">
        <table border="1" cellpadding="10" cellspacing="0" align="center">
            <thead>
                <tr>
                    <th>Profile Picture</th>
                    <th>Username</th>
                    <th>Contact No</th>
                    <th>Status</th>
                    <th>User Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (isset($_POST['submit-search'])) {
                        $search = mysqli_real_escape_string($conn, $_POST['search']);
                        $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
                      
                        $sql = "SELECT users_profile.user_id, users_profile.display_name, users_profile.profile_picture_url, users.contact_no, 
                                users.status, user_types.type_name 
                                FROM users_profile
                                INNER JOIN users ON users_profile.user_id = users.user_id
                                INNER JOIN user_types ON users_profile.user_type_id = user_types.user_type_id
                                WHERE user_types.user_type_id != 3"; 

                        if (!empty($search)) {
                            $sql .= " AND (users_profile.display_name LIKE '%$search%' 
                                OR users.contact_no LIKE '%$search%' 
                                OR users.status LIKE '%$search%')";
                        }

                        if (!empty($user_type)) {
                            $sql .= " AND user_types.user_type_id = $user_type";
                        }

                        $result = mysqli_query($conn, $sql);

                        if (!$result) {
                            die("Error in query execution: " . mysqli_error($conn));
                        }

                        $queryResult = mysqli_num_rows($result);

                        
                        if ($queryResult > 0) {
                            echo "<tr><td colspan='5' align='center'>There are " . $queryResult . " result(s)!</td></tr>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                $profileLink = ($row['type_name'] == 'Job Seeker') ? 'view_profile_jobseeker.php' : 'view_profile_employer.php'; /*edit link of employer*/ 
                                echo "<tr>
                                    <td><img src='../img_profiles/" . $row['profile_picture_url'] . "' alt='Profile Picture' width='50'></td>
                                    <td><a href='" . $profileLink . "?user_id=".$row['user_id']."'>".$row['display_name']."</a></td>
                                    <td>".$row['contact_no']."</td>
                                    <td>".$row['status']."</td>
                                    <td>".$row['type_name']."</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' align='center'>There are no results matching your search!</td></tr>";
                        }
                    } else {
                        $sql = "SELECT users_profile.user_id, users_profile.display_name, users_profile.profile_picture_url, users.contact_no, 
                                users.status, user_types.type_name 
                                FROM users_profile
                                INNER JOIN users ON users_profile.user_id = users.user_id
                                INNER JOIN user_types ON users_profile.user_type_id = user_types.user_type_id
                                WHERE user_types.user_type_id != 3";  

                        $result = mysqli_query($conn, $sql);

                        if (!$result) {
                            die("Error in query execution: " . mysqli_error($conn));
                        }

                        $queryResults = mysqli_num_rows($result);

                        if ($queryResults > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $profileLink = ($row['type_name'] == 'Job Seeker') ? 'view_profile_jobseeker.php' : 'view_profile_employer.php'; /*edit link of employer*/
                                echo "<tr>
                                    <td><img src='../img_profiles/" . $row['profile_picture_url'] . "' alt='Profile Picture' width='50'></td>
                                    <td><a href='" . $profileLink . "?user_id=".$row['user_id']."'>".$row['display_name']."</a></td>
                                    <td>".$row['contact_no']."</td>
                                    <td>".$row['status']."</td>
                                    <td>".$row['type_name']."</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' align='center'>No users found.</td></tr>";
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>
