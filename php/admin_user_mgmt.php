<?php

include '../php/navs/navbar_admin.php';
include 'db_connection.php';  // Changed path since both files are in the same directory

// Pagination
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order = $sort === 'oldest' ? 'ASC' : 'DESC';

// Filtering
$user_type = isset($_GET['user_type']) ? (int)$_GET['user_type'] : 0;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Base query
$sql = "SELECT u.*, up.display_name, up.profile_picture_url, up.user_type_id, ut.type_name,
        u.deactivated_at, u.reactivated_at 
        FROM users u 
        JOIN users_profile up ON u.user_id = up.user_id 
        JOIN user_types ut ON up.user_type_id = ut.user_type_id 
        WHERE up.user_type_id != 3";

// Add filters
if ($user_type > 0) {
    $sql .= " AND up.user_type_id = $user_type";
}

if ($search) {
    $sql .= " AND (up.display_name LIKE '%$search%' 
              OR u.email LIKE '%$search%' 
              OR u.contact_no LIKE '%$search%')";
}

if ($status_filter) {
    $sql .= " AND u.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Add sorting and pagination
$sql .= " ORDER BY u.user_id $order LIMIT $offset, $results_per_page";

$result = $conn->query($sql);
$total_results = $result->num_rows;

// Initialize messages
$error_message = '';
$success_message = '';

// Process status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    // Get current user status
    $status_check = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
    $status_check->bind_param("i", $user_id);
    $status_check->execute();
    $result_status = $status_check->get_result();
    $current_status = strtolower($result_status->fetch_assoc()['status']);
    
    switch($action) {
        case 'activate':
            if ($current_status === 'active') {
                $error_message = "User is already active.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET status = 'Active', reactivated_at = NOW() WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute() ? $success_message = "User has been activated successfully." : $error_message = "Failed to activate user.";
            }
            break;
            
        case 'mark_inactive':
            if ($current_status === 'inactive') {
                $error_message = "User is already inactive.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET status = 'Inactive' WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute() ? $success_message = "User has been marked as inactive." : $error_message = "Failed to mark user as inactive.";
            }
            break;
            
        case 'suspend':
            if ($current_status === 'suspended') {
                $error_message = "User is already suspended.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET status = 'Suspended' WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute() ? $success_message = "User has been suspended." : $error_message = "Failed to suspend user.";
            }
            break;
            
        case 'deactivate':
            if ($current_status === 'deactivated') {
                $error_message = "User is already deactivated.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET status = 'Deactivated', deactivated_at = NOW() WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute() ? $success_message = "User has been deactivated." : $error_message = "Failed to deactivate user.";
            }
            break;
    }
    
    // Refresh the page to show updated status
    if (!$error_message) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Account Management</title>
    <link rel="stylesheet" href="../css/ua_mgmt.css">
</head>
<body>
 
    
    <h1>User Account Management</h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form method="GET" action="" class="filters-form">
        <div class="search-section">
            <input type="text" name="search" placeholder="Search users..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </div>
        
        <div class="filter-section">
            <select name="user_type">
                <option value="0">All Users</option>
                <option value="1" <?php echo $user_type === 1 ? 'selected' : ''; ?>>Job Seekers</option>
                <option value="2" <?php echo $user_type === 2 ? 'selected' : ''; ?>>Employers</option>
            </select>
            
            <select name="status">
                <option value="">All Statuses</option>
                <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="Suspended" <?php echo $status_filter === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                <option value="Deactivated" <?php echo $status_filter === 'Deactivated' ? 'selected' : ''; ?>>Deactivated</option>
            </select>
            
            <button type="submit">Apply Filters</button>
        </div>
    </form>

    <?php if ($total_results === 0): ?>
        <div class="no-results">
            <p>No users found matching your criteria.</p>
            <?php if ($search || $status_filter || $user_type): ?>
                <p>Try adjusting your filters or search terms.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php 
                        $profile_picture = isset($user['profile_picture_url']) && $user['profile_picture_url'] != "" 
                            ? "../img_profiles/" . $user['profile_picture_url'] 
                            : '../images/default-avatar.png';
                        ?>
                        <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" width="50">
                    </td>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['type_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                    <td><?php echo $user['last_login_date']; ?></td>
                    <td class="actions">
                        <button onclick="window.location.href='up_view.php?id=<?php echo $user['user_id']; ?>'" class="action-btn btn-view">
                            View
                        </button>
                        <button onclick="window.location.href='up_edit.php?id=<?php echo $user['user_id']; ?>'" class="action-btn btn-edit">
                            Edit
                        </button>
                        
                        <div class="status-dropdown-container">
                            <select class="status-dropdown" onchange="changeUserStatus(this, <?php echo $user['user_id']; ?>)">
                                <option value="">Change User Status</option>
                                <?php if (strtolower($user['status']) !== 'active'): ?>
                                    <option value="activate">Activate</option>
                                <?php endif; ?>
                                <?php if (strtolower($user['status']) !== 'inactive'): ?>
                                    <option value="mark_inactive">Mark Inactive</option>
                                <?php endif; ?>
                                <?php if (strtolower($user['status']) !== 'suspended'): ?>
                                    <option value="suspend">Suspend</option>
                                <?php endif; ?>
                                <?php if (strtolower($user['status']) !== 'deactivated'): ?>
                                    <option value="deactivate">Deactivate</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php
    // Pagination
    $total_sql = "SELECT COUNT(*) as count FROM users u JOIN users_profile up ON u.user_id = up.user_id WHERE 1=1";
    if ($user_type > 0) $total_sql .= " AND up.user_type_id = $user_type";
    if ($search) $total_sql .= " AND (up.display_name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.contact_no LIKE '%$search%')";
    if ($status_filter) $total_sql .= " AND u.status = '" . $conn->real_escape_string($status_filter) . "'";
    
    $total_result = $conn->query($total_sql);
    $total_users = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $results_per_page);
    
    if ($total_pages > 1):
    ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&user_type=<?php echo $user_type; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
               class="<?php echo $page === $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <script>
    function changeUserStatus(selectElement, userId) {
        const action = selectElement.value;
        if (!action) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        form.appendChild(actionInput);
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
    </script>

</body>
</html>
