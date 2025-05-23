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

// Verifica se o like já existe
$sql_check = "SELECT 1 FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = ?";
$params = array($usuario_id, $comentario_id);
$stmt_check = sqlsrv_query($conn, $sql_check, $params);

if (sqlsrv_has_rows($stmt_check)) {
    // Remove o like
    $sql = "DELETE FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = ?";
    $action = "unlike";
} else {
    // Adiciona o like
    $sql = "INSERT INTO tblLikesPorComentario (idUsuario, idComentario) VALUES (?, ?)";
    $action = "like";
}

$result = sqlsrv_query($conn, $sql, $params);

if ($result === false) {
    echo json_encode(["success" => false, "error" => "Erro no banco de dados"]);
    exit;
}

// Conta os likes atualizados
$sql_count = "SELECT COUNT(*) AS total FROM tblLikesPorComentario WHERE idComentario = ?";
$stmt_count = sqlsrv_query($conn, $sql_count, array($comentario_id));
$total = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC)["total"];

echo json_encode([
    "success" => true,
    "action" => $action,
    "total_likes" => $total
]);

$sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, idReferencia)
                   SELECT ?, ?, c.idUsuario, ?
                   FROM tblComentario c
                   WHERE c.idComentario = ? AND c.idUsuario != ?";

$mensagem = $_SESSION["nome"] . " curtiu seu comentário";
sqlsrv_query($conn, $sql_notificacao, array(
    $mensagem, 
    NOTIFICACAO_CURTIDA, 
    $comentario_id,
    $comentario_id,
    $_SESSION["usuario_id"]
));
?>