<?php
session_start();
include '../config.php'; // Inclui config.php

if (!isset($_SESSION['idUsuario_setup'])) {
    header("Location: ../Cadastro/Cadastro.php");
    exit();
}

// A função validateImage permanece a mesma
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

// A função rollbackRegistration permanece a mesma
function rollbackRegistration($conn, $userId)
{
    $sql = "DELETE FROM tblUsuario WHERE idUsuario = ?";
    $params = array($userId);
    sqlsrv_query($conn, $sql, $params);

    // Limpa a sessão
    unset($_SESSION['idUsuario_setup']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $biografia = $_POST["biografia"] ?? '';
        $userId = $_SESSION['idUsuario_setup'];

        $fotoUsuarioStream = null;
        // Processa foto de perfil
        if (isset($_FILES["fotoUsuario"]) && $_FILES["fotoUsuario"]["error"] == UPLOAD_ERR_OK) {
            if (!validateImage($_FILES["fotoUsuario"])) {
                throw new Exception("A foto de perfil deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoUsuarioStream = fopen($_FILES["fotoUsuario"]["tmp_name"], 'rb'); // Abre como stream binário
            if ($fotoUsuarioStream === false) {
                throw new Exception("Erro ao abrir arquivo de imagem de perfil.");
            }
        }

        $fotoCapaStream = null;
        // Processa foto de capa
        if (isset($_FILES["fotoCapa"]) && $_FILES["fotoCapa"]["error"] == UPLOAD_ERR_OK) {
            if (!validateImage($_FILES["fotoCapa"])) {
                throw new Exception("A foto de capa deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoCapaStream = fopen($_FILES["fotoCapa"]["tmp_name"], 'rb'); // Abre como stream binário
            if ($fotoCapaStream === false) {
                throw new Exception("Erro ao abrir arquivo de imagem de capa.");
            }
        }

        // Construir a query de atualização dinamicamente
        $updateFields = ["bio_usuario = ?"];
        $params = [$biografia];

        if ($fotoUsuarioStream !== null) {
            $updateFields[] = "fotoUsuario = ?";
            // Passar o stream com o tipo SQLSRV_SQLTYPE_VARBINARY('MAX') para garantir tratamento binário
            $params[] = [$fotoUsuarioStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('MAX')];
        }

        if ($fotoCapaStream !== null) {
            $updateFields[] = "fotoCapa = ?";
            // Passar o stream com o tipo SQLSRV_SQLTYPE_VARBINARY('MAX') para garantir tratamento binário
            $params[] = [$fotoCapaStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('MAX')];
        }

        $sql = "UPDATE tblUsuario SET " . implode(", ", $updateFields) . " WHERE idUsuario = ?";
        $params[] = $userId; // Adiciona o ID do usuário como o último parâmetro para a cláusula WHERE

        $stmt = sqlsrv_query($conn, $sql, $params);

        // Fechar streams após a execução da query
        if ($fotoUsuarioStream !== null) {
            fclose($fotoUsuarioStream);
        }
        if ($fotoCapaStream !== null) {
            fclose($fotoCapaStream);
        }

        if ($stmt) {
            $_SESSION["usuario_id"] = $userId;
            unset($_SESSION['idUsuario_setup']);
            header("Location: ../Feed/Feed.php");
            exit();
        } else {
            throw new Exception("Erro ao atualizar perfil: " . print_r(sqlsrv_errors(), true));
        }
    } catch (Exception $e) {
        // Rollback em caso de erro
        if (isset($userId)) { // Garante que $userId está definido
            rollbackRegistration($conn, $userId);
        }

        // Redireciona para página de erro
        header("Location: ../Erro/Error.php?message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Perfil - Versami</title>
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="CSS/SetupProfileStyle.css">
</head>

<body>
    <div class="content">
        <div class="progress-info">
            <p>Etapa 2 de 2: <span>Configurar seu perfil</span></p>
            <p>Estamos quase lá, complete seu perfil para começar a usar o <span>Versami</span>.</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fotoUsuario"><i class="fa-solid fa-image"></i> Foto de Perfil</label>
                <label for="fotoUsuario" class="file-input-label">
                    <i class="fa-solid fa-upload"></i> Selecionar Imagem
                </label>
                <input type="file" id="fotoUsuario" name="fotoUsuario"
                    accept="image/jpeg,image/png,image/gif,image/webp"> <small>Formatos aceitos: JPEG, PNG, GIF, WebP.
                    Tamanho máximo: 40MB. Dimensões: 100x100 a 5000x5000
                    pixels.</small>
                <div class="preview">
                    <img id="previewFoto" src="../Assets/default_profile.png" alt="Pré-visualização da foto de perfil">
                </div>
            </div>

            <div class="form-group">
                <label for="fotoCapa"><i class="fa-solid fa-image"></i> Foto de Capa</label>
                <label for="fotoCapa" class="file-input-label">
                    <i class="fa-solid fa-upload"></i> Selecionar Imagem
                </label>
                <input type="file" id="fotoCapa" name="fotoCapa" accept="image/jpeg,image/png,image/gif,image/webp"
                    onchange="previewImage(this, 'previewCapa')">
                <small>Formatos aceitos: JPEG, PNG, GIF, WebP. Tamanho máximo: 40MB. Dimensões: 100x100 a 5000x5000
                    pixels.</small>
                <div class="preview">
                    <img id="previewCapa" src="../Assets/default_cover.png" alt="Pré-visualização da foto de capa">
                </div>
            </div>

            <div class="form-group">
                <label for="biografia"><i class="fa-solid fa-pencil"></i> Biografia</label>
                <textarea id="biografia" name="biografia" maxlength="255" placeholder="Fale um pouco sobre você..."
                    rows="4"></textarea>
                <small>Máximo de 255 caracteres.</small>
            </div>

            <input type="submit" value="Finalizar Cadastro">
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            const defaultFotoPerfil = '../Assets/default_profile.png'; // URL da imagem padrão de perfil
            const defaultFotoCapa = '../Assets/default_cover.png'; // URL da imagem padrão de capa

            if (file) {
                // Validação client-side básica de tamanho e tipo
                const maxSizeMB = 40;
                const maxSizeBytes = maxSizeMB * 1024 * 1024;

                if (file.size > maxSizeBytes) {
                    alert(`O arquivo é muito grande. Tamanho máximo permitido: ${maxSizeMB}MB`);
                    input.value = ''; // Limpa o input
                    preview.src = (previewId === 'previewFoto') ? defaultFotoPerfil : defaultFotoCapa;
                    return;
                }

                if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/gif') && !file.type.match('image/webp')) {
                    alert('Por favor, selecione um arquivo de imagem (JPEG, PNG, GIF ou WebP)');
                    input.value = '';
                    preview.src = (previewId === 'previewFoto') ? defaultFotoPerfil : defaultFotoCapa;
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        const minWidth = 100, minHeight = 100;
                        const maxWidth = 5000, maxHeight = 5000;

                        if (this.width < minWidth || this.height < minHeight ||
                            this.width > maxWidth || this.height > maxHeight) {
                            alert(`A imagem deve ter entre ${minWidth}x${minHeight} e ${maxWidth}x${maxHeight} pixels.`);
                            input.value = '';
                            preview.src = (previewId === 'previewFoto') ? defaultFotoPerfil : defaultFotoCapa;
                        } else {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        }
                    };
                    img.src = e.target.result;
                }

                reader.readAsDataURL(file);
            } else {
                // Se nenhum arquivo for selecionado, volta para a imagem padrão
                preview.src = (previewId === 'previewFoto') ? defaultFotoPerfil : defaultFotoCapa;
            }
        }

        // Adiciona eventos para mostrar o nome do arquivo selecionado no label
        document.addEventListener('DOMContentLoaded', function () {
            const fileInputs = document.querySelectorAll('input[type="file"]');

            fileInputs.forEach(input => {
                const label = input.previousElementSibling; // O label "Selecionar Imagem"

                input.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        label.innerHTML = `<i class="fa-solid fa-check"></i> ${this.files[0].name}`;
                        label.classList.add('changed'); // Adiciona uma classe para estilização de "selecionado"
                    } else {
                        label.innerHTML = '<i class="fa-solid fa-upload"></i> Selecionar Imagem';
                        label.classList.remove('changed');
                    }
                });
            });
        });
    </script>
</body>

</html>