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
function displayImage($binaryData)
{
    if ($binaryData === null || empty($binaryData)) {
        return 'Assets/padrao.png'; // Caminho para imagem padrão
    }
    return 'data:image/jpeg;base64,' . base64_encode($binaryData);
}

// Função para transformar URLs em links clicáveis
function transformURLsIntoLinks($text)
{
    $pattern = '/(https?:\/\/[^\s]+)/';
    return preg_replace($pattern, '<a href="$1" target="_blank">$1</a>', htmlspecialchars($text));
}

// Tipos de Notificação (baseado na inserção em tblTipoNotificacao)
define('NOTIFICACAO_CURTIDA_POST', 1);
define('NOTIFICACAO_CURTIDA_COMENTARIO', 2);
define('NOTIFICACAO_COMENTARIO', 3);
define('NOTIFICACAO_SEGUIMENTO', 4);

function contarNotificacoesNaoLidas($conn, $usuario_id)
{
    $sql = "SELECT COUNT(*) as total FROM tblNotificacao
            WHERE idUsuario = ? AND visualizada = 0";
    $stmt = sqlsrv_query($conn, $sql, array($usuario_id));
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return $row['total'];
    }
    return 0;
}
?>