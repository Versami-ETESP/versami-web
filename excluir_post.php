<?php
session_start();
include 'config.php';

$post_id = $_POST["idPublicacao"] ?? null;

// Verifica se o usuário é dono do post
$sql_verifica = "SELECT 1 FROM tblPublicacao 
                WHERE idPublicacao = ? AND idUsuario = ?";
$result = sqlsrv_query($conn, $sql_verifica, array(
    $post_id,
    $_SESSION["usuario_id"]
));

if (sqlsrv_has_rows($result)) {
    // Exclusão em cascata (comentários e likes serão removidos automaticamente)
    $sql_delete = "DELETE FROM tblPublicacao WHERE idPublicacao = ?";
    sqlsrv_query($conn, $sql_delete, array($post_id));
    
    echo json_encode(["success" => true]);
} else {
    http_response_code(403);
    echo json_encode(["error" => "Acesso negado"]);
}
?>