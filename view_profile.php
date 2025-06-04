<?php
session_start();
include 'config.php';

if (!isset($_GET['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_GET['username'];

// Buscar dados do usuário pelo nome de usuário
$query = "SELECT * FROM users WHERE username = ?";
$params = array($username);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false || !$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "Usuário não encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user['fullname']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="profile">
        <img src="uploads/<?php echo htmlspecialchars($user['cover_pic']); ?>" class="cover">
        <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-pic">
        <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
        <p>@<?php echo htmlspecialchars($user['username']); ?></p>
        <p><?php echo htmlspecialchars($user['bio']); ?></p>
        <a href="profile.php">Voltar</a>
    </div>

</body>
</html>
