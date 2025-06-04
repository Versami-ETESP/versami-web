<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["success" => false, "error" => "Usuário não autenticado"]);
    exit;
}

if (!isset($_POST["comentario_id"])) {
    echo json_encode(["success" => false, "error" => "ID do comentário não fornecido"]);
    exit;
}

$comentario_id = $_POST["comentario_id"];
$usuario_id = $_SESSION["usuario_id"];

$sql_check = "SELECT 1 FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = ?";
$params = array($usuario_id, $comentario_id);
$stmt_check = sqlsrv_query($conn, $sql_check, $params);

if (sqlsrv_has_rows($stmt_check)) {
    $sql = "DELETE FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = ?";
    $action = "unlike";
    $mensagem = $_SESSION["nome"] . " curtiu seu comentário";
    // Remove notification
    // idAdmin is NULL in notification for user actions, so need to match on idUsuario and message
    $sql_delete_notif = "DELETE FROM tblNotificacao WHERE tipoNotificacao = ? AND idUsuario = (SELECT idUsuario FROM tblComentario WHERE idComentario = ?) AND idAdmin IS NULL AND mensagem = ?";
    sqlsrv_query($conn, $sql_delete_notif, array(NOTIFICACAO_CURTIDA_COMENTARIO, $comentario_id, $mensagem));

} else {
    $sql = "INSERT INTO tblLikesPorComentario (idUsuario, idComentario) VALUES (?, ?)";
    $action = "like";

    $sql_comment_owner = "SELECT idUsuario FROM tblComentario WHERE idComentario = ?";
    $stmt_comment_owner = sqlsrv_query($conn, $sql_comment_owner, array($comentario_id));
    $comment_owner_row = sqlsrv_fetch_array($stmt_comment_owner, SQLSRV_FETCH_ASSOC);
    $comment_owner_id = $comment_owner_row['idUsuario'];

    if ($comment_owner_id != $usuario_id) {
        $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, visualizada, idAdmin, dataNotificacao)
                           VALUES (?, ?, ?, 0, NULL, GETDATE())"; // idAdmin is NULL for user actions
        $mensagem = $_SESSION["nome"] . " curtiu seu comentário";
        sqlsrv_query($conn, $sql_notificacao, array(
            $mensagem,
            NOTIFICACAO_CURTIDA_COMENTARIO,
            $comment_owner_id, // Recipient: Comment owner
        ));
    }
}

$result = sqlsrv_query($conn, $sql, $params);

if ($result === false) {
    echo json_encode(["success" => false, "error" => "Erro no banco de dados"]);
    exit;
}

$sql_count = "SELECT COUNT(*) AS total FROM tblLikesPorComentario WHERE idComentario = ?";
$stmt_count = sqlsrv_query($conn, $sql_count, array($comentario_id));
$total = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC)["total"];

echo json_encode([
    "success" => true,
    "action" => $action,
    "total_likes" => $total
]);
?>