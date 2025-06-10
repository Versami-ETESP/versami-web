<?php
session_start();
include '../config.php'; // Inclui config.php, que agora contém validateImage()

header('Content-Type: application/json'); // Garante que a resposta seja JSON

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Usuário não autenticado."]);
    exit;
}

$userId = $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nome = $_POST["nome"] ?? null;
        $biografia = $_POST["biografia"] ?? null;
        
        $fotoUsuarioStream = null;
        $fotoCapaStream = null;   
        
        // --- Lógica para FOTO DE PERFIL ---
        if (isset($_FILES["fotoUsuario"]) && $_FILES["fotoUsuario"]["error"] == UPLOAD_ERR_OK) {
            if (!validateImage($_FILES["fotoUsuario"])) { // validateImage() agora está em config.php
                throw new Exception("A foto de perfil deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoUsuarioStream = fopen($_FILES["fotoUsuario"]["tmp_name"], 'rb'); // Abre como stream binário
            if ($fotoUsuarioStream === false) {
                throw new Exception("Erro ao abrir arquivo de imagem de perfil.");
            }
        } else if (isset($_POST['remove_fotoUsuario']) && $_POST['remove_fotoUsuario'] == '1') {
            // Se o checkbox/sinal de "remover" for enviado, definimos explicitamente como NULL
            $fotoUsuarioStream = NULL; 
        }

        // --- Lógica para FOTO DE CAPA ---
        if (isset($_FILES["fotoCapa"]) && $_FILES["fotoCapa"]["error"] == UPLOAD_ERR_OK) {
            if (!validateImage($_FILES["fotoCapa"])) { // validateImage() agora está em config.php
                throw new Exception("A foto de capa deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoCapaStream = fopen($_FILES["fotoCapa"]["tmp_name"], 'rb'); // Abre como stream binário
            if ($fotoCapaStream === false) {
                throw new Exception("Erro ao abrir arquivo de imagem de capa.");
            }
        } else if (isset($_POST['remove_fotoCapa']) && $_POST['remove_fotoCapa'] == '1') {
            // Se o checkbox/sinal de "remover" for enviado
            $fotoCapaStream = NULL;
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
        
        // --- Atualização condicional para fotoUsuario ---
        // Se $fotoUsuarioStream não é estritamente null (i.e., foi uploaded ou marcado para remover)
        // Note: UPLOAD_ERR_NO_FILE means no file was selected. In that case, we want to skip updating the column,
        // unless remove_fotoUsuario was checked.
        $hasFotoUsuarioChange = (isset($_FILES["fotoUsuario"]) && $_FILES["fotoUsuario"]["error"] == UPLOAD_ERR_OK) || (isset($_POST['remove_fotoUsuario']) && $_POST['remove_fotoUsuario'] == '1');

        if ($hasFotoUsuarioChange) {
            $updateFields[] = "fotoUsuario = ?";
            // If it's a stream (uploaded) or NULL (removed), pass with explicit type
            $params[] = ($fotoUsuarioStream === NULL) ? NULL : [$fotoUsuarioStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('MAX')];
        }

        // --- Atualização condicional para fotoCapa ---
        $hasFotoCapaChange = (isset($_FILES["fotoCapa"]) && $_FILES["fotoCapa"]["error"] == UPLOAD_ERR_OK) || (isset($_POST['remove_fotoCapa']) && $_POST['remove_fotoCapa'] == '1');

        if ($hasFotoCapaChange) {
            $updateFields[] = "fotoCapa = ?";
            // If it's a stream (uploaded) or NULL (removed), pass with explicit type
            $params[] = ($fotoCapaStream === NULL) ? NULL : [$fotoCapaStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('MAX')];
        }

        if (empty($updateFields)) {
            echo json_encode(["success" => true, "message" => "Nenhuma alteração foi feita."]);
            exit;
        }

        $sql = "UPDATE tblUsuario SET " . implode(", ", $updateFields) . " WHERE idUsuario = ?";
        $params[] = $userId; // Adiciona o ID do usuário como o último parâmetro para a cláusula WHERE

        $stmt = sqlsrv_query($conn, $sql, $params);

        // Fechar streams após a execução da query
        if (is_resource($fotoUsuarioStream)) { // Ensure it's a resource before closing
            fclose($fotoUsuarioStream);
        }
        if (is_resource($fotoCapaStream)) { // Ensure it's a resource before closing
            fclose($fotoCapaStream);
        }

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            error_log("Erro no UPDATE de perfil: " . print_r($errors, true));
            throw new Exception("Erro ao atualizar o perfil no banco de dados.");
        }

        echo json_encode(["success" => true, "message" => "Perfil atualizado com sucesso!"]);

    } catch (Exception $e) {
        error_log("Exceção em atualizar_perfil.php: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>