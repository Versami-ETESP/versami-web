<?php
session_start();
include 'config.php'; // Inclui o arquivo de configuração (assumindo que config.php está no mesmo nível)

header('Content-Type: application/json');

$arroba_usuario = $_POST['arroba_usuario'] ?? '';

if (empty($arroba_usuario)) {
    echo json_encode(["success" => false, "error" => "Nome de usuário não fornecido."]);
    exit;
}

try {
    // Buscar idUsuario e idPergunta (corrigido: idPergunta em vez de idPerguntaSecreta) e a pergunta
    $sql = "SELECT u.idUsuario, u.idPergunta, ps.pergunta 
            FROM tblUsuario u
            JOIN tblPerguntaSecreta ps ON u.idPergunta = ps.idPergunta
            WHERE u.arroba_usuario = ?";
    $params = array($arroba_usuario);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        // Logar erro específico do SQL Server
        error_log("Erro SQL ao buscar pergunta secreta em get_pergunta_secreta.php: " . print_r(sqlsrv_errors(), true));
        throw new Exception("Erro interno ao buscar pergunta secreta.");
    }

    $usuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($usuario && !empty($usuario['pergunta'])) {
        echo json_encode([
            "success" => true,
            "idUsuario" => $usuario['idUsuario'],
            "pergunta" => $usuario['pergunta']
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Nome de usuário não encontrado ou sem pergunta secreta configurada."]);
    }

} catch (Exception $e) {
    error_log("Exceção em get_pergunta_secreta.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>