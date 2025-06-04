<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["success" => false, "error" => "Usuário não autenticado"]);
    exit;
}

$post_id = $_POST["post_id"] ?? null;
if (!$post_id) {
    echo json_encode(["success" => false, "error" => "ID do post não fornecido"]);
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

try {
    $sql_check = "SELECT 1 FROM tblLikesPorPost WHERE idUsuario = ? AND idPublicacao = ?";
    $stmt_check = sqlsrv_query($conn, $sql_check, array($usuario_id, $post_id));

    if (sqlsrv_has_rows($stmt_check)) {
        $sql = "DELETE FROM tblLikesPorPost WHERE idUsuario = ? AND idPublicacao = ?";
        $action = "unlike";
        $mensagem = $_SESSION["nome"] . " curtiu sua publicação";
        // Remove notification
        // idAdmin is NULL in notification for user actions, so need to match on idUsuario and message
        $sql_delete_notif = "DELETE FROM tblNotificacao WHERE tipoNotificacao = ? AND idUsuario = (SELECT idUsuario FROM tblPublicacao WHERE idPublicacao = ?) AND idAdmin IS NULL AND mensagem = ?";
        sqlsrv_query($conn, $sql_delete_notif, array(NOTIFICACAO_CURTIDA_POST, $post_id, $mensagem));

    } else {
        $sql = "INSERT INTO tblLikesPorPost (idUsuario, idPublicacao) VALUES (?, ?)";
        $action = "like";

        $sql_post_owner = "SELECT idUsuario FROM tblPublicacao WHERE idPublicacao = ?";
        $stmt_post_owner = sqlsrv_query($conn, $sql_post_owner, array($post_id));
        $post_owner_row = sqlsrv_fetch_array($stmt_post_owner, SQLSRV_FETCH_ASSOC);
        $post_owner_id = $post_owner_row['idUsuario'];

        if ($post_owner_id != $usuario_id) {
            $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, visualizada, idAdmin, dataNotificacao)
                                VALUES (?, ?, ?, 0, NULL, GETDATE())"; // idAdmin is NULL for user actions
            $mensagem = $_SESSION["nome"] . " curtiu sua publicação";
            $params_notificacao = array(
                $mensagem,
                NOTIFICACAO_CURTIDA_POST,
                $post_owner_id, // Recipient: Post owner
            );
            sqlsrv_query($conn, $sql_notificacao, $params_notificacao);
        }
    }

    $result = sqlsrv_query($conn, $sql, array($usuario_id, $post_id));

    if (!$result) {
        throw new Exception("Erro na operação");
    }

    $sql_count = "SELECT COUNT(*) AS total FROM tblLikesPorPost WHERE idPublicacao = ?";
    $stmt_count = sqlsrv_query($conn, $sql_count, array($post_id));
    $total = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC)["total"];

    echo json_encode([
        "success" => true,
        "action" => $action,
        "total_likes" => $total
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>