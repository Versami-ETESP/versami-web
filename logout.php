<?php
session_start();

// Armazena mensagem de logout
$_SESSION['logout_message'] = "Você saiu com sucesso. Volte logo!";

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona para a página de login
header("Location: Login/login.php");
exit;
?>