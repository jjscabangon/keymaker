<?php
// functions.php
include 'db_connection.php'; 

session_start();


$user_id = $_SESSION['user_id']; 



function updateStatus($conn, $document_id, $status) {
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in.";
        return;
    }

    $user_id = $_SESSION['user_id'];
    $timestamp = date("Y-m-d H:i:s");

    if ($status === 'approved') {
        $sql = "UPDATE documents 
                SET status = 'approved', approved_by = '$user_id', approved_at = '$timestamp',  
                    declined_by = NULL, declined_at = NULL 
                WHERE document_id = '$document_id'";
    } else if ($status === 'declined') {
        $sql = "UPDATE documents 
                SET status = 'declined', declined_by = '$user_id', declined_at = '$timestamp', 
                    approved_by = NULL, approved_at = NULL 
                WHERE document_id = '$document_id'";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Status updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating status: " . $conn->error . "</div>";
    }
}

function handlePostRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['document_id'], $_POST['action'])) {
            $document_id  = $_POST['document_id'];
            $action = $_POST['action'];

            if ($action === 'verified') {
                verifyDocument($conn, $document_id);
            } else {
                updateStatus($conn, $document_id, $action);
            }
        }
    }
}

// To verify the document based on the document_id of the user
function verifyDocument($conn, $document_id) {
    $sql = "UPDATE users_profile 
            INNER JOIN documents ON users_profile.user_id = documents.user_id 
            SET is_verified = 1 
            WHERE documents.document_id = '$document_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Document verified successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error verifying document: " . $conn->error . "</div>";
    }
}
?>