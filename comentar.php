<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_id = $_POST["post_id"];
    $comentario = trim($_POST["comentario"]);
    $usuario_id = $_SESSION["usuario_id"];

    // Verifica se o comentário não está vazio
    if (empty($comentario)) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=empty_comment");
        exit;
    }

    // Insere o comentário
    $sql_insert = "INSERT INTO tblComentario (comentario, data_coment, idPublicacao, idUsuario) 
                   VALUES (?, GETDATE(), ?, ?)";

    $params_insert = array($comentario, $post_id, $usuario_id);
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

    if (!$stmt_insert) {
        die("Erro ao inserir comentário: " . print_r(sqlsrv_errors(), true));
    }

    // Cria notificação para o dono do post (se não for o próprio usuário)
    $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, visualizada, idAdmin, idReferencia)
                   SELECT ?, ?, p.idUsuario, 0, ?, ?
                   FROM tblPublicacao p
                   WHERE p.idPublicacao = ? AND p.idUsuario != ?";

    $mensagem = $_SESSION["nome"] . " comentou: " . substr($comentario, 0, 50);
    $params_notificacao = array(
        $mensagem,
        NOTIFICACAO_COMENTARIO,
        $usuario_id, // Quem comentou (idAdmin)
        $post_id,    // ID do post (idReferencia)
        $post_id,
        $usuario_id
    );

    $stmt_notificacao = sqlsrv_query($conn, $sql_notificacao, $params_notificacao);

    if (!$stmt_notificacao) {
        error_log("Erro ao criar notificação: " . print_r(sqlsrv_errors(), true));
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>