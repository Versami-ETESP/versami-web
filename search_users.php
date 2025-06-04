<?php
include 'config.php';

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    
    $query = "SELECT fullname, username, profile_pic, cover_pic FROM users 
              WHERE fullname LIKE ? OR username LIKE ?";
    
    $params = array("%$search%", "%$search%");
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo '<a href="profileUser.php?username=' . urlencode($user['username']) . '" class="card-user">';
        echo '<div class="content-card">';
        echo '<img src="uploads/' . htmlspecialchars($user['profile_pic']) . '" class="profile-mini">';
        echo '<div class="user-info">';
        echo '<h4>' . htmlspecialchars($user['fullname']) . '</h4>';
        echo '<p>@' . htmlspecialchars($user['username']) . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</a>';


            
    }
}
?>
