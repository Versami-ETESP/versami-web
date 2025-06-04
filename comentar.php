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

    if (empty($comentario)) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=empty_comment");
        exit;
    }

    $sql_insert = "INSERT INTO tblComentario (comentario, data_coment, idPublicacao, idUsuario)
                   VALUES (?, GETDATE(), ?, ?)";

    $params_insert = array($comentario, $post_id, $usuario_id);
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

    if (!$stmt_insert) {
        die("Erro ao inserir comentário: " . print_r(sqlsrv_errors(), true));
    }

    $sql_post_owner = "SELECT idUsuario FROM tblPublicacao WHERE idPublicacao = ?";
    $stmt_post_owner = sqlsrv_query($conn, $sql_post_owner, array($post_id));
    $post_owner_row = sqlsrv_fetch_array($stmt_post_owner, SQLSRV_FETCH_ASSOC);
    $post_owner_id = $post_owner_row['idUsuario'];

    if ($post_owner_id != $usuario_id) {
        $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, visualizada, idAdmin, dataNotificacao)
                           VALUES (?, ?, ?, 0, NULL, GETDATE())"; // idAdmin is NULL for user actions

        $mensagem = $_SESSION["nome"] . " comentou: " . substr($comentario, 0, 50);
        $params_notificacao = array(
            $mensagem,
            NOTIFICACAO_COMENTARIO,
            $post_owner_id, // Recipient: Post owner
        );

        $stmt_notificacao = sqlsrv_query($conn, $sql_notificacao, $params_notificacao);

        if (!$stmt_notificacao) {
            error_log("Erro ao criar notificação: " . print_r(sqlsrv_errors(), true));
        }
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>