<?php

include 'db_connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_application_id = $_POST['job_application_id'];
    $status_id = $_POST['status_id'];

    // Validate that the job application ID and status ID are valid integers
    if (is_numeric($job_application_id) && is_numeric($status_id)) {
        
        $query = "UPDATE job_applications SET status_id = ? WHERE job_application_id = ?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ii", $status_id, $job_application_id);

            if ($stmt->execute()) {
                header("Location: ../php/tracking_employer.php?job_application_id=" . $job_application_id);
                exit;
            } else {
                echo "Error updating application status. Please try again.";
            }

            $stmt->close();
        } else {
            echo "Error preparing the update query. Please try again.";
        }
    } else {
        echo "Invalid application ID or status ID.";
    }
} else {
    echo "Invalid request.";
}
?>
