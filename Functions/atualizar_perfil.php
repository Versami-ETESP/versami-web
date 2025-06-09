<?php
session_start();
include '../config.php'; // Inclui config.php

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
        
        $fotoUsuarioBin = null; // Será os dados binários se upload, senão NULL
        $fotoCapaBin = null;   // Será os dados binários se upload, senão NULL

        // --- Lógica para FOTO DE PERFIL ---
        // Se o arquivo foi enviado, validamos e pegamos o conteúdo binário.
        if (!empty($_FILES["fotoUsuario"]["tmp_name"]) && $_FILES["fotoUsuario"]["error"] == UPLOAD_ERR_OK) {
            if (!validateImage($_FILES["fotoUsuario"])) {
                throw new Exception("A foto de perfil deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoUsuarioBin = file_get_contents($_FILES["fotoUsuario"]["tmp_name"]);
        } else if (isset($_POST['remove_fotoUsuario']) && $_POST['remove_fotoUsuario'] == '1') {
            // Se o checkbox/sinal de "remover" for enviado, definimos explicitamente como NULL
            $fotoUsuarioBin = NULL; 
        } else {
            // Se nada foi enviado e não há sinal para remover, mantém o valor atual no BD
            // Precisamos saber se o usuário está querendo manter a foto atual ou se ele simplesmente não carregou uma nova
            // A melhor forma é se o frontend passar um sinal para "manter a atual" ou "remover"
            // Por enquanto, vamos manter o que já está no BD se não houver upload E não houver "sinal de remoção"
            // Para isso, faremos a lógica de UPDATE no SQL de forma mais inteligente.
        }

        // --- Lógica para FOTO DE CAPA ---
        if (!empty($_FILES["fotoCapa"]["tmp_name"]) && $_FILES["fotoCapa"]["error"] == UPLOAD_ERR_OK) {
            if (!validateImage($_FILES["fotoCapa"])) {
                throw new Exception("A foto de capa deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoCapaBin = file_get_contents($_FILES["fotoCapa"]["tmp_name"]);
        } else if (isset($_POST['remove_fotoCapa']) && $_POST['remove_fotoCapa'] == '1') {
            // Se o checkbox/sinal de "remover" for enviado
            $fotoCapaBin = NULL;
        } else {
            // Se nada foi enviado e não há sinal para remover, mantém o valor atual no BD
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
        // Se $fotoUsuarioBin for um dado binário (upload), atualiza.
        // Se $fotoUsuarioBin for NULL (sinal de remoção), atualiza para NULL.
        // Se não for NULL (não houve upload nem remoção), NÃO inclui no UPDATE para manter o valor atual.
        if (isset($_FILES["fotoUsuario"]) && $_FILES["fotoUsuario"]["error"] == UPLOAD_ERR_NO_FILE && !(isset($_POST['remove_fotoUsuario']) && $_POST['remove_fotoUsuario'] == '1')) {
            // Não houve upload e não houve sinal para remover, mantém a foto atual do BD. Não adiciona ao UPDATE.
        } else {
            $updateFields[] = "fotoUsuario = CONVERT(varbinary(max), ?)";
            $params[] = $fotoUsuarioBin; // Já é binário ou NULL
        }

        // --- Atualização condicional para fotoCapa ---
        if (isset($_FILES["fotoCapa"]) && $_FILES["fotoCapa"]["error"] == UPLOAD_ERR_NO_FILE && !(isset($_POST['remove_fotoCapa']) && $_POST['remove_fotoCapa'] == '1')) {
            // Não houve upload e não houve sinal para remover, mantém a capa atual do BD. Não adiciona ao UPDATE.
        } else {
            $updateFields[] = "fotoCapa = CONVERT(varbinary(max), ?)";
            $params[] = $fotoCapaBin; // Já é binário ou NULL
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
}
?>