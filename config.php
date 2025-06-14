<?php
$serverName = "DESKTOP-REJT7MF\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "versami",
    "Uid" => "sa",
    "PWD" => "12121515",
    "CharacterSet" => "UTF-8"
);
// Conexão com o SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("Falha na conexão: " . print_r(sqlsrv_errors(), true));
}

// Definir caminhos para imagens padrão
define('FOTO_PADRAO_PATH', __DIR__ . 'Assets/default_profile.png');
define('CAPA_PADRAO_PATH', __DIR__ . 'Assets/default_cover.png');

// Função para converter dados binários em base64
function displayImage($binaryData)
{
    if ($binaryData === null || empty($binaryData)) {
        return '../Assets/default_profile.png'; // Caminho para imagem padrão
    }
    return 'data:image/jpeg;base64,' . base64_encode($binaryData);
}

// NOVO: Função para garantir que strings de texto do BD sejam UTF-8
function convertToUtf8($string) {
    // Detecta se a string não é UTF-8 (assumindo que a origem pode ser ISO-8859-1)
    if (mb_detect_encoding($string, 'UTF-8', true) === false) {
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }
    return $string; // Já é UTF-8
}

// NOVA FUNÇÃO: Função para validar imagens, movida de SetupProfile.php
function validateImage($file, $maxSizeMB = 40, $minWidth = 100, $minHeight = 100, $maxWidth = 5000, $maxHeight = 5000)
{
    // Verifica se é um upload válido
    if (!is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    // Verifica tamanho máximo do arquivo (40MB)
    $maxSizeBytes = $maxSizeMB * 1024 * 1024;
    if ($file['size'] > $maxSizeBytes) {
        return false;
    }

    // Verifica tipo MIME da imagem
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return false;
    }

    // Verifica dimensões da imagem
    $imageSize = getimagesize($file['tmp_name']);
    if (!$imageSize) {
        return false;
    }

    list($width, $height) = $imageSize;
    if ($width < $minWidth || $height < $minHeight || $width > $maxWidth || $height > $maxHeight) {
        return false;
    }

    return true;
}


// Função para transformar URLs em links clicáveis
function transformURLsIntoLinks($text)
{
    // Primeiro, garanta que o texto é UTF-8 antes de htmlspecialchars
    $text_utf8 = convertToUtf8($text);
    $pattern = '/(https?:\/\/[^\s]+)/';
    return preg_replace($pattern, '<a href="$1" target="_blank">$1</a>', htmlspecialchars($text_utf8));
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