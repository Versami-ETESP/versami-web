<?php
session_start();
include '../config.php'; // Inclui o arquivo de configuração para acesso ao banco e constantes

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    error_log("Denunciar_post: Usuário não autenticado."); // Added for debugging
    echo json_encode(["success" => false, "error" => "Usuário não autenticado."]);
    exit;
}

// Verifica se o ID da publicação foi recebido
$post_id = $_POST["post_id"] ?? null;
if (!$post_id) {
    error_log("Denunciar_post: ID da publicação não fornecido."); // Added for debugging
    echo json_encode(["success" => false, "error" => "ID da publicação não fornecido."]);
    exit;
}

$idUsuarioLogado = $_SESSION["usuario_id"];

try {
    // 1. Verificar se o usuário já denunciou este post (boa prática para evitar duplicatas)
    $sql_check_denuncia = "SELECT 1 FROM tblDenuncia WHERE idUsuario = ? AND idPublicacao = ?";
    $params_check_denuncia = array($idUsuarioLogado, $post_id);
    $stmt_check_denuncia = sqlsrv_query($conn, $sql_check_denuncia, $params_check_denuncia);

    if ($stmt_check_denuncia === false) {
        // Log the error from the check query itself
        error_log("Erro no SELECT de verificação de denúncia em denunciar_post.php: " . print_r(sqlsrv_errors(), true));
        echo json_encode(["success" => false, "error" => "Erro ao verificar denúncias existentes."]);
        exit;
    }

    if (sqlsrv_has_rows($stmt_check_denuncia)) {
        error_log("Denunciar_post: Usuário já denunciou esta publicação (idUsuario: $idUsuarioLogado, idPublicacao: $post_id)."); // Added for debugging
        echo json_encode(["success" => false, "message" => "Você já denunciou esta publicação."]);
        exit;
    }

    // 2. Inserir a denúncia na tabela tblDenuncia
    // O status padrão é 'PENDENTE', que assumimos ter idStatusDenuncia = 1
    // idAdmin é NULL porque a denúncia é feita por um usuário comum
    $sql_insert_denuncia = "INSERT INTO tblDenuncia (data_denuncia, observacao_admin, idUsuario, idPublicacao, idAdmin, statusDenun)
                            VALUES (GETDATE(), NULL, ?, ?, NULL, 1)"; // Assuming 1 is the ID for 'PENDENTE'

    $params_insert_denuncia = array($idUsuarioLogado, $post_id);
    $stmt_insert_denuncia = sqlsrv_query($conn, $sql_insert_denuncia, $params_insert_denuncia);

    if ($stmt_insert_denuncia === false) {
        // Capture and log detailed SQL Server errors
        $errors = sqlsrv_errors();
        error_log("Erro ao inserir denúncia em denunciar_post.php: " . print_r($errors, true));
        error_log("SQL Query: " . $sql_insert_denuncia);
        error_log("SQL Params: " . print_r($params_insert_denuncia, true));
        echo json_encode(["success" => false, "error" => "Erro ao registrar a denúncia. Por favor, tente novamente."]);
        exit;
    }

    error_log("Denúncia registrada com sucesso! (idUsuario: $idUsuarioLogado, idPublicacao: $post_id)"); // Added for debugging
    echo json_encode(["success" => true, "message" => "Denúncia registrada com sucesso!"]);

} catch (Exception $e) {
    // Catch any other unexpected PHP errors
    error_log("Exceção inesperada em denunciar_post.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>