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
    // Verifica like existente
    $sql_check = "SELECT 1 FROM tblLikesPorPost WHERE idUsuario = ? AND idPublicacao = ?";
    $stmt_check = sqlsrv_query($conn, $sql_check, array($usuario_id, $post_id));

    if (sqlsrv_has_rows($stmt_check)) {
        $sql = "DELETE FROM tblLikesPorPost WHERE idUsuario = ? AND idPublicacao = ?";
        $action = "unlike";
    } else {
        $sql = "INSERT INTO tblLikesPorPost (idUsuario, idPublicacao) VALUES (?, ?)";
        $action = "like";

        // Cria notificação apenas para like
        $sql_notificacao = "INSERT INTO tblNotificacao (mensagem, tipoNotificacao, idUsuario, visualizada, idAdmin, idReferencia)
                  SELECT ?, ?, p.idUsuario, 0, ?, ?
                  FROM tblPublicacao p
                  WHERE p.idPublicacao = ? AND p.idUsuario != ?";
        $mensagem = $_SESSION["nome"] . " curtiu sua publicação";
        $params_notificacao = array(
            $mensagem,
            NOTIFICACAO_CURTIDA_POST,
            $usuario_id, // Quem curtiu (idAdmin)
            $post_id,    // ID do post (idReferencia)
            $post_id,
            $usuario_id
        );
        sqlsrv_query($conn, $sql_notificacao, $params_notificacao);
    }

    $result = sqlsrv_query($conn, $sql, array($usuario_id, $post_id));

    if (!$result) {
        throw new Exception("Erro na operação");
    }

    // Contagem atualizada
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