<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dob = $_POST['dob'];
    
    $query = "INSERT INTO users (fullname, username, email, password, birthdate) VALUES (?, ?, ?, ?, ?)";
    $params = array($name, $username, $email, $password, $dob);
    
    if (!sqlsrv_query($conn, $query, $params)) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    // Buscar os dados completos do usuário após o cadastro
    $query = "SELECT * FROM users WHERE username = ?";
    $params = array($username);
    $result = sqlsrv_query($conn, $query, $params);
    $user = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
    }

    header("Location: setup_profile.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = ?";
    $params = array($email);
    $result = sqlsrv_query($conn, $query, $params);
    $user = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        header("Location: profile.php");
        exit();
    } else {
        echo "Credenciais inválidas.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post">
        <h2>Cadastro</h2>
        <input type="text" name="fullname" placeholder="Nome Completo" required>
        <input type="text" name="username" placeholder="Nome de Usuário" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Senha" required>
        <input type="date" name="dob" required>
        <button type="submit" name="register">Cadastrar</button>
    </form>
    <form method="post">
        <h2>Login</h2>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Senha" required>
        <button type="submit" name="login">Entrar</button>
    </form>
</body>
</html>
