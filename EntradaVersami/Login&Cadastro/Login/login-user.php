<?php
session_start();
include '../../BD/bd-conexao.php';

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