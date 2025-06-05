<?php
session_start();
include 'config.php'; // Inclui o arquivo de configuração para acesso ao banco e constantes

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["success" => false, "error" => "Usuário não autenticado."]);
    exit;
}

// Verifica se o ID da publicação foi recebido
$post_id = $_POST["post_id"] ?? null;
if (!$post_id) {
    echo json_encode(["success" => false, "error" => "ID da publicação não fornecido."]);
    exit;
}

$idUsuarioLogado = $_SESSION["usuario_id"];

try {
    // 1. Verificar se o usuário já denunciou este post (opcional, mas boa prática)
    $sql_check_denuncia = "SELECT 1 FROM tblDenuncia WHERE idUsuario = ? AND idPublicacao = ?";
    $params_check_denuncia = array($idUsuarioLogado, $post_id);
    $stmt_check_denuncia = sqlsrv_query($conn, $sql_check_denuncia, $params_check_denuncia);

    if (sqlsrv_has_rows($stmt_check_denuncia)) {
        echo json_encode(["success" => false, "message" => "Você já denunciou esta publicação."]);
        exit;
    }

    // 2. Inserir a denúncia na tabela tblDenuncia
    // O status padrão é 'PENDENTE', que assumimos ter idStatusDenuncia = 1 (do INSERT INTO tblStatusDenuncia VALUES ('PENDENTE'))
    // idAdmin é NULL porque a denúncia é feita por um usuário comum
    $sql_insert_denuncia = "INSERT INTO tblDenuncia (data_denuncia, observacao_admin, idUsuario, idPublicacao, idAdmin, statusDenun)
                            VALUES (GETDATE(), NULL, ?, ?, NULL, 1)"; // 1 = PENDENTE

    $params_insert_denuncia = array($idUsuarioLogado, $post_id);
    $stmt_insert_denuncia = sqlsrv_query($conn, $sql_insert_denuncia, $params_insert_denuncia);

    if ($stmt_insert_denuncia === false) {
        // Erro na inserção da denúncia
        $errors = sqlsrv_errors();
        error_log("Erro ao inserir denúncia: " . print_r($errors, true)); // Loga o erro detalhado
        echo json_encode(["success" => false, "error" => "Erro ao registrar a denúncia. Por favor, tente novamente."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Denúncia registrada com sucesso!"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>