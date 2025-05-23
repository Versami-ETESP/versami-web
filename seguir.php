<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$seguidor_id = $_SESSION["usuario_id"];
$seguido_id = $_POST["seguido_id"];

// Verifica se já segue
$sql_check = "SELECT 1 FROM tblSeguidores WHERE idSeguidor = ? AND idSeguido = ?";
$params_check = array($seguidor_id, $seguido_id);

if (sqlsrv_has_rows(sqlsrv_query($conn, $sql_check, $params_check))) {
    $sql = "DELETE FROM tblSeguidores WHERE idSeguidor = ? AND idSeguido = ?";
    $action = "unfollow";
} else {
    $sql = "INSERT INTO tblSeguidores (idSeguidor, idSeguido) VALUES (?, ?)";
    $action = "follow";
    
    // Cria notificação de seguidor
    $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, idReferencia)
                       VALUES (?, ?, ?, ?)";
    $mensagem = $_SESSION["nome"] . " começou a te seguir";
    sqlsrv_query($conn, $sql_notificacao, array(
        $mensagem, NOTIFICACAO_SEGUIDOR, $seguido_id, $seguidor_id
    ));
}

$result = sqlsrv_query($conn, $sql, $params_check);

// Retorna resposta JSON
echo json_encode([
    "success" => $result !== false,
    "action" => $action
]);
?>