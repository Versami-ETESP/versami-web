<?php
session_start();
include 'config.php'; // Inclui o arquivo de configuração

header('Content-Type: application/json'); // Garante que a resposta seja JSON

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Usuário não autenticado."]);
    exit;
}

$userId = $_SESSION["usuario_id"];

try {
    $nome = $_POST["nome"] ?? null;
    $biografia = $_POST["biografia"] ?? null;

    $fotoUsuarioData = null;
    $fotoCapaData = null;

    // --- Processar upload de foto de perfil ---
    if (!empty($_FILES["fotoUsuario"]["tmp_name"]) && $_FILES["fotoUsuario"]["error"] == UPLOAD_ERR_OK) {
        if (!validateImage($_FILES["fotoUsuario"])) { // Sua função validateImage de setup_profile.php
            throw new Exception("A foto de perfil deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
        }
        $fotoUsuarioData = file_get_contents($_FILES["fotoUsuario"]["tmp_name"]);
    }

    // --- Processar upload de foto de capa ---
    if (!empty($_FILES["fotoCapa"]["tmp_name"]) && $_FILES["fotoCapa"]["error"] == UPLOAD_ERR_OK) {
        if (!validateImage($_FILES["fotoCapa"])) { // Sua função validateImage de setup_profile.php
            throw new Exception("A foto de capa deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
        }
        $fotoCapaData = file_get_contents($_FILES["fotoCapa"]["tmp_name"]);
    }

    // Construir a query de atualização dinamicamente
    $updateFields = [];
    $params = [];

    if ($nome !== null) {
        $updateFields[] = "nome = ?";
        $params[] = $nome;
    }
    if ($biografia !== null) {
        $updateFields[] = "bio_usuario = ?";
        $params[] = $biografia;
    }
    if ($fotoUsuarioData !== null) {
        $updateFields[] = "fotoUsuario = CONVERT(varbinary(max), ?)";
        $params[] = $fotoUsuarioData;
    }
    if ($fotoCapaData !== null) {
        $updateFields[] = "fotoCapa = CONVERT(varbinary(max), ?)";
        $params[] = $fotoCapaData;
    }

    if (empty($updateFields)) {
        echo json_encode(["success" => true, "message" => "Nenhuma alteração foi feita."]);
        exit;
    }

    $sql = "UPDATE tblUsuario SET " . implode(", ", $updateFields) . " WHERE idUsuario = ?";
    $params[] = $userId; // Adiciona o ID do usuário como o último parâmetro

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $errors = sqlsrv_errors();
        error_log("Erro no UPDATE de perfil: " . print_r($errors, true));
        error_log("SQL Query: " . $sql);
        error_log("SQL Params: " . print_r($params, true));
        throw new Exception("Erro ao atualizar o perfil no banco de dados.");
    }

    echo json_encode(["success" => true, "message" => "Perfil atualizado com sucesso!"]);

} catch (Exception $e) {
    error_log("Exceção em atualizar_perfil.php: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

function validateImage($file, $maxSizeMB = 40, $minWidth = 100, $minHeight = 100, $maxWidth = 5000, $maxHeight = 5000) {
    if (!is_uploaded_file($file['tmp_name'])) { return false; }
    $maxSizeBytes = $maxSizeMB * 1024 * 1024;
    if ($file['size'] > $maxSizeBytes) { return false; }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);
    if (!in_array($mimeType, $allowedMimeTypes)) { return false; }
    $imageSize = getimagesize($file['tmp_name']);
    if (!$imageSize) { return false; }
    list($width, $height) = $imageSize;
    if ($width < $minWidth || $height < $minHeight || $width > $maxWidth || $height > $maxHeight) { return false; }
    return true;
}


// Certifique-se de que a função validateImage esteja disponível (se estiver em config.php, ok)
// Se não, você precisará copiá-la de setup_profile.php para este arquivo ou para config.php
// (preferencialmente em config.php para reutilização).
// Exemplo da função validateImage (se precisar):
/*
function validateImage($file, $maxSizeMB = 40, $minWidth = 100, $minHeight = 100, $maxWidth = 5000, $maxHeight = 5000) {
    if (!is_uploaded_file($file['tmp_name'])) { return false; }
    $maxSizeBytes = $maxSizeMB * 1024 * 1024;
    if ($file['size'] > $maxSizeBytes) { return false; }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);
    if (!in_array($mimeType, $allowedMimeTypes)) { return false; }
    $imageSize = getimagesize($file['tmp_name']);
    if (!$imageSize) { return false; }
    list($width, $height) = $imageSize;
    if ($width < $minWidth || $height < $minHeight || $width > $maxWidth || $height > $maxHeight) { return false; }
    return true;
}
*/
?>