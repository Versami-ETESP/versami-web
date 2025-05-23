<?php
$serverName = "DESKTOP-REJT7MF\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "versamiredesocialtcc",
    "Uid" => "sa",
    "PWD" => "12121515"
);
// Conexão com o SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("Falha na conexão: " . print_r(sqlsrv_errors(), true));
}

// Definir caminhos para imagens padrão
define('FOTO_PADRAO_PATH', __DIR__ . '/Assets/padrao.png');
define('CAPA_PADRAO_PATH', __DIR__ . '/Assets/padraoCapa.png');

// Função para converter dados binários em base64
function displayImage($binaryData) {
    if ($binaryData === null || empty($binaryData)) {
        return 'imagens/perfil-padrao.jpg';
    }
    return 'data:image/jpeg;base64,' . base64_encode($binaryData);
}

// Função para transformar URLs em links clicáveis
function transformURLsIntoLinks($text) {
    $pattern = '/(https?:\/\/[^\s]+)/';
    return preg_replace($pattern, '<a href="$1" target="_blank">$1</a>', htmlspecialchars($text));
}

// Função para converter varbinary em base64 para exibição
// Tipos de Notificação
define('NOTIFICACAO_COMENTARIO', 1); // ID do tipo de notificação para comentários
define('NOTIFICACAO_CURTIDA', 2);    // ID do tipo de notificação para curtidas
define('NOTIFICACAO_SEGUIMENTO', 3); // ID do tipo de notificação para novos seguidores
?>