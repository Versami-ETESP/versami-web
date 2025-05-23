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
    $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, idReferencia)
                       SELECT ?, ?, p.idUsuario, ?
                       FROM tblPublicacao p
                       WHERE p.idPublicacao = ? AND p.idUsuario != ?";
    
    $mensagem = $_SESSION["nome"] . " comentou: " . substr($comentario, 0, 50);
    $params_notificacao = array(
        $mensagem, 
        NOTIFICACAO_COMENTARIO, 
        $post_id,  // idReferencia
        $post_id,   // para a cláusula WHERE
        $usuario_id // para a cláusula WHERE
    );
    
    $stmt_notificacao = sqlsrv_query($conn, $sql_notificacao, $params_notificacao);
    
    if (!$stmt_notificacao) {
        // Não interrompe o fluxo, apenas registra o erro
        error_log("Erro ao criar notificação: " . print_r(sqlsrv_errors(), true));
    }

    // Redireciona de volta para a página anterior
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>