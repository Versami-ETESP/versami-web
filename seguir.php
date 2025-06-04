<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION["usuario_id"])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit;
}

$seguidor_id = $_SESSION["usuario_id"];
$seguido_id = $_POST["seguido_id"] ?? null;

if (!$seguido_id) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["success" => false, "message" => "ID do usuário seguido não fornecido."]);
    exit;
}

if ($seguidor_id == $seguido_id) {
    echo json_encode(["success" => false, "message" => "Você não pode seguir a si mesmo."]);
    exit;
}

$sql_check = "SELECT 1 FROM tblSeguidores WHERE idSeguidor = ? AND idSeguido = ?";
$params_check = array($seguidor_id, $seguido_id);
$stmt_check = sqlsrv_query($conn, $sql_check, $params_check);

if ($stmt_check === false) {
    echo json_encode(["success" => false, "message" => "Erro ao verificar seguimento."]);
    exit;
}

$action = "";
if (sqlsrv_has_rows($stmt_check)) {
    $sql = "DELETE FROM tblSeguidores WHERE idSeguidor = ? AND idSeguido = ?";
    $action = "unfollow";

    $mensagem_notif = $_SESSION["nome"] . " começou a te seguir";
    $sql_delete_notif = "DELETE FROM tblNotificacao WHERE tipoNotificacao = ? AND idUsuario = ? AND idAdmin IS NULL AND mensagem = ?";
    sqlsrv_query($conn, $sql_delete_notif, array(NOTIFICACAO_SEGUIMENTO, $seguido_id, $mensagem_notif));

} else {
    $sql = "INSERT INTO tblSeguidores (idSeguidor, idSeguido) VALUES (?, ?)";
    $action = "follow";

    $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, visualizada, idAdmin, dataNotificacao)
                         VALUES (?, ?, ?, 0, NULL, GETDATE())"; // idAdmin is NULL for user actions
    $mensagem = $_SESSION["nome"] . " começou a te seguir";
    $params_notif = array(
        $mensagem,
        NOTIFICACAO_SEGUIMENTO,
        $seguido_id, // Recipient: The user being followed
    );
    $stmt_notif = sqlsrv_query($conn, $sql_notificacao, $params_notif);
    if ($stmt_notif === false) {
        error_log("Erro ao criar notificação de seguimento: " . print_r(sqlsrv_errors(), true));
    }
}

$result = sqlsrv_query($conn, $sql, $params_check);

if ($result === false) {
    echo json_encode([
        "success" => false,
        "message" => "Erro no banco de dados ao " . ($action === "follow" ? "seguir" : "deixar de seguir") . ".",
        "error_details" => print_r(sqlsrv_errors(), true)
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "action" => $action
]);
?>