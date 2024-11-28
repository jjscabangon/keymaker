<?php
// police_clearances.php

include '../php/navs/navbar_admin.php';
include 'db_connection.php'; 
require 'functions.php';

// Handle POST requests
handlePostRequest($conn);

// Fetch uploaded files (pending, approved, declined) for Police Clearances


$sql_pending = "SELECT users.username, users.contact_no, users.email, users_profile.police_clearance_url, 
                      documents.document_type, documents.status, documents.approved_by, documents.approved_at, 
                      documents.declined_by, documents.declined_at, users_profile.is_verified, 
                      documents.remarks, documents.document_id 
               FROM documents
               INNER JOIN users ON documents.user_id = users.user_id
               INNER JOIN users_profile ON users.user_id = users_profile.user_id
               WHERE documents.document_type = 'police_clearance' AND documents.status = 'pending' 
                     AND users_profile.is_verified = 0";

$sql_verified = "SELECT users.username, users.contact_no, users.email, users_profile.police_clearance_url, 
                        documents.document_type, documents.status, documents.approved_by, documents.approved_at, 
                        documents.declined_by, documents.declined_at, users_profile.is_verified, 
                        documents.remarks, documents.document_id 
                 FROM documents
                 INNER JOIN users ON documents.user_id = users.user_id
                 INNER JOIN users_profile ON users.user_id = users_profile.user_id
                 WHERE documents.document_type = 'police_clearance' AND documents.status = 'approved'";

$sql_declined = "SELECT users.username, users.contact_no, users.email, users_profile.police_clearance_url, 
                        documents.document_type, documents.status, documents.approved_by, documents.approved_at, 
                        documents.declined_by, documents.declined_at, users_profile.is_verified, 
                        documents.remarks, documents.document_id 
                 FROM documents
                 INNER JOIN users ON documents.user_id = users.user_id
                 INNER JOIN users_profile ON users.user_id = users_profile.user_id
                 WHERE documents.document_type = 'police_clearance' AND documents.status = 'declined'";

$pending = $conn->query($sql_pending);
$result_verified = $conn->query($sql_verified);
$result_declined = $conn->query($sql_declined);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Clearance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Police Clearance Management</h2>
    <!-- Navigation Links -->
    <nav class="mb-4">
        <a href="../php/admin_validate_bp.php" class="btn btn-secondary">Business Permit</a>
        <a href="../php/admin_validate_pc.php" class="btn btn-primary">Police Clearance</a>
    </nav>

    <!-- Pending Police Clearances -->
    <h3>Pending</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Police Clearance URL</th>
                <th>Document Type</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Download</th>
                <th>View</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($pending->num_rows > 0) {
                while ($row = $pending->fetch_assoc()) {
                    $file_path = "../jobseeker/" . $row['police_clearance_url'];
                    $download_link = file_exists($file_path) ? '<a href="' . $file_path . '" class="btn btn-primary" download>Download</a>' : 'File not found';
                    $view_link = file_exists($file_path) ? '<a href="' . $file_path . '" target="_blank" class="btn btn-info">View</a>' : 'File not found';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['police_clearance_url']); ?></td>
                        <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                        <td><?php echo $download_link; ?></td>
                        <td><?php echo $view_link; ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="document_id" value="<?php echo $row['document_id']; ?>">
                                <button type="submit" name="action" value="approved" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="declined" class="btn btn-danger btn-sm">Decline</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='10'>No pending Police Clearances.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Approved Police Clearances -->
<h3>Approved</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Username</th>
            <th>Contact Number</th>
            <th>Email</th>
            <th>Police Clearance URL</th>
            <th>Document Type</th>
            <th>Status</th>
            <th>Approved By</th>
            <th>Approved At</th>
            <th>Download</th>
            <th>View</th>
            <th>Verify</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result_verified->num_rows > 0) {
            while ($row = $result_verified->fetch_assoc()) {
                $file_path = "../jobseeker/" . $row['police_clearance_url'];
                $download_link = file_exists($file_path) ? '<a href="' . $file_path . '" class="btn btn-primary" download>Download</a>' : 'File not found';
                $view_link = file_exists($file_path) ? '<a href="' . $file_path . '" target="_blank" class="btn btn-info">View</a>' : 'File not found';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['police_clearance_url']); ?></td>
                    <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['approved_by']); ?></td>
                    <td><?php echo htmlspecialchars($row['approved_at']); ?></td>
                    <td><?php echo $download_link; ?></td>
                    <td><?php echo $view_link; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="document_id" value="<?php echo $row['document_id']; ?>">
                            <button type="submit" name="action" value="verified" class="btn btn-success btn-sm">Verify</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='11'>No approved Police Clearances.</td></tr>";
        }
        ?>
    </tbody>
</table>


    <!-- Declined Police Clearances -->
    <h3>Declined</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Police Clearance URL</th>
                <th>Document Type</th>
                <th>Status</th>
                <th>Declined By</th>
                <th>Declined At</th>
                <th>Remarks</th>
                <th>Download</th>
                <th>View</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_declined->num_rows > 0) {
                while ($row = $result_declined->fetch_assoc()) {
                    $file_path = "../jobseeker/" . $row['police_clearance_url'];
                    $download_link = file_exists($file_path) ? '<a href="' . $file_path . '" class="btn btn-primary" download>Download</a>' : 'File not found';
                    $view_link = file_exists($file_path) ? '<a href="' . $file_path . '" target="_blank" class="btn btn-info">View</a>' : 'File not found';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['police_clearance_url']); ?></td>
                        <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['declined_by']); ?></td>
                        <td><?php echo htmlspecialchars($row['declined_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                        <td><?php echo $download_link; ?></td>
                        <td><?php echo $view_link; ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='11'>No declined Police Clearances.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


</body>
</html>

<?php
$conn->close();
?>