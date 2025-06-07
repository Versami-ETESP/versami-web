<?php
// Não deve haver nada antes desta tag PHP, nem mesmo espaços ou linhas em branco.
// Garanta que o arquivo esteja salvo como UTF-8 sem BOM (Byte Order Mark).

// TEMPORÁRIO PARA DEBUG: Ativar exibição de todos os erros e logar
// DESATIVAR EM PRODUÇÃO!
error_reporting(E_ALL); // Reporta todos os erros
ini_set('display_errors', 1); // Exibe erros no navegador (para ver imediatamente)
ini_set('log_errors', 1);     // Garante que erros sejam logados
ini_set('error_log', __DIR__ . '/phperror_debug.log'); // Define um arquivo de log específico para este script

session_start();

$config_path = '../config.php'; // Caminho para config.php (um nível acima)
if (!file_exists($config_path)) {
    error_log("ERRO FATAL DEBUG: Arquivo de configuração não encontrado em: " . $config_path);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "error" => "Erro interno do servidor: Configuração ausente. (DEBUG: conf.php)"]);
    exit;
}
include $config_path; // Inclui o arquivo de configuração

header('Content-Type: application/json'); // Garante que a resposta será JSON

// Verificar se a conexão $conn foi estabelecida por config.php
if (!isset($conn) || $conn === null) {
    $sqlsrv_errors = function_exists('sqlsrv_errors') ? sqlsrv_errors() : 'Função sqlsrv_errors não existe.';
    error_log("ERRO FATAL DEBUG: Variável de conexão \$conn não estabelecida após include de config.php. Erros: " . print_r($sqlsrv_errors, true));
    echo json_encode(["success" => false, "error" => "Erro interno: Falha na conexão com o banco de dados. (DEBUG: \$conn null)"]);
    exit;
} else {
    error_log("DEBUG: Conexão com o BD estabelecida com sucesso.");
}


$arroba_usuario = $_POST['arroba_usuario'] ?? '';

if (empty($arroba_usuario)) {
    error_log("DEBUG: Nome de usuário não fornecido na requisição.");
    echo json_encode(["success" => false, "error" => "Nome de usuário não fornecido."]);
    exit;
}

try {
    $sql = "SELECT u.idUsuario, u.idPergunta, ps.pergunta 
            FROM tblUsuario u
            JOIN tblPerguntaSecreta ps ON u.idPergunta = ps.idPergunta
            WHERE u.arroba_usuario = ?";
    $params = array($arroba_usuario);

    error_log("DEBUG: Executando SQL: " . $sql . " com parâmetros: " . print_r($params, true));

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $errors = sqlsrv_errors();
        error_log("DEBUG: Erro SQL ao executar query de pergunta secreta: " . print_r($errors, true));
        echo json_encode(["success" => false, "error" => "Erro interno ao buscar pergunta secreta: Falha na query SQL."]);
        exit;
    }

    $usuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($usuario && !empty($usuario['pergunta'])) {
        // CORREÇÃO AQUI: Converter a pergunta para UTF-8 antes de usar
        $pergunta_utf8 = convertToUtf8($usuario['pergunta']);
        error_log("DEBUG: Pergunta encontrada para '" . $arroba_usuario . "': " . $pergunta_utf8); // Loga a versão UTF-8
        
        echo json_encode([
            "success" => true,
            "idUsuario" => $usuario['idUsuario'],
            "pergunta" => $pergunta_utf8 // Usa a pergunta convertida
        ]);
    } else {
        error_log("DEBUG: Usuário '" . $arroba_usuario . "' não encontrado ou sem pergunta. Resultado da busca: " . print_r($usuario, true));
        echo json_encode(["success" => false, "error" => "Nome de usuário não encontrado ou sem pergunta secreta configurada."]);
    }

} catch (Exception $e) {
    error_log("DEBUG: Exceção PHP capturada em get_pergunta_secreta.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erro inesperado no script: " . $e->getMessage()]);
}
// Não deve haver nada após esta tag PHP, nem mesmo espaços ou linhas em branco.
?>