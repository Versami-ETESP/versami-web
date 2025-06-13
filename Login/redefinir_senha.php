<?php
session_start();
include '../config.php'; // Inclui o arquivo de configuração (assumindo que config.php está no diretório pai ou no mesmo nível)

header('Content-Type: application/json');

$arroba_usuario = $_POST['arroba_usuario'] ?? '';
$resposta_secreta = $_POST['resposta_secreta'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';

if (empty($arroba_usuario) || empty($resposta_secreta) || empty($nova_senha)) {
    echo json_encode(["success" => false, "error" => "Todos os campos são obrigatórios."]);
    exit;
}

if (strlen($nova_senha) < 8) {
    echo json_encode(["success" => false, "error" => "A nova senha deve ter pelo menos 8 caracteres."]);
    exit;
}

try {
    // 1. Buscar dados do usuário e a resposta secreta armazenada
    $sql = "SELECT idUsuario, resposta FROM tblUsuario WHERE arroba_usuario = ?";
    $params = array($arroba_usuario);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        error_log("Erro SQL ao buscar usuário para redefinição de senha: " . print_r(sqlsrv_errors(), true));
        throw new Exception("Erro interno ao redefinir a senha.");
    }

    $usuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(["success" => false, "error" => "Nome de usuário não encontrado."]);
        exit;
    }

    // CONVERSÃO PARA UPPERCASE ANTES DE HASHEAR
    $resposta_secreta_uppercase = mb_strtoupper($resposta_secreta, 'UTF-8'); // Use mb_strtoupper para UTF-8
    // Criptografar a resposta fornecida para comparação
    $respostaSecretaHashFornecida = hash("sha256", $resposta_secreta_uppercase); // Use a versão em maiúsculas

    // 2. Comparar a resposta fornecida com a armazenada
    if ($respostaSecretaHashFornecida !== $usuario['resposta']) {
        echo json_encode(["success" => false, "error" => "Resposta secreta incorreta."]);
        exit;
    }

    // 3. Criptografar a nova senha e atualizar no banco de dados
    $novaSenhaHash = hash("sha256", $nova_senha);

    $sql_update = "UPDATE tblUsuario SET senha = ? WHERE idUsuario = ?";
    $params_update = array($novaSenhaHash, $usuario['idUsuario']);
    $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

    if ($stmt_update === false) {
        error_log("Erro SQL ao atualizar senha: " . print_r(sqlsrv_errors(), true));
        throw new Exception("Erro ao atualizar a senha no banco de dados.");
    }

    echo json_encode(["success" => true, "message" => "Senha redefinida com sucesso! Você pode fazer login agora."]);

} catch (Exception $e) {
    error_log("Exceção em redefinir_senha.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>