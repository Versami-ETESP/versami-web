<?php
session_start();
include 'config.php'; // Inclui config.php

if (!isset($_SESSION['idUsuario_setup'])) {
    header("Location: Cadastro/cadastro.php");
    exit();
}

// A função validateImage permanece a mesma
function validateImage($file, $maxSizeMB = 40, $minWidth = 100, $minHeight = 100, $maxWidth = 5000, $maxHeight = 5000)
{
    // ... (código existente da validateImage) ...
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
        
        $fotoUsuarioBin = null; // Será os dados binários se upload, senão NULL
        $fotoCapaBin = null;   // Será os dados binários se upload, senão NULL

        // Processa foto de perfil
        if (!empty($_FILES["fotoUsuario"]["tmp_name"])) {
            if (!validateImage($_FILES["fotoUsuario"])) {
                throw new Exception("A foto de perfil deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoUsuarioBin = file_get_contents($_FILES["fotoUsuario"]["tmp_name"]);
        }
        // Se o usuário não enviou uma foto de perfil, a coluna ficará NULL no BD.
        // O displayImage em config.php cuidará da imagem padrão.

        // Processa foto de capa
        if (!empty($_FILES["fotoCapa"]["tmp_name"])) {
            if (!validateImage($_FILES["fotoCapa"])) {
                throw new Exception("A foto de capa deve ser uma imagem válida (JPEG, PNG, GIF ou WebP) com tamanho máximo de 40MB e dimensões entre 100x100 e 5000x5000 pixels.");
            }
            $fotoCapaBin = file_get_contents($_FILES["fotoCapa"]["tmp_name"]);
        }
        // Se o usuário não enviou uma foto de capa, a coluna ficará NULL no BD.
        // O displayImage em config.php cuidará da imagem padrão.


        // SQL para atualização de perfil
        // Use CASE WHEN para atualizar a coluna APENAS se um novo binário for fornecido.
        // Se $fotoUsuarioBin ou $fotoCapaBin for NULL, a coluna não é alterada.
        // Se você quiser que o usuário possa "remover" uma foto e voltar para o padrão,
        // precisaria de um checkbox ou botão para isso, que enviaria um sinal para o backend
        // para definir a coluna como NULL explicitamente. Por enquanto, só atualizamos se um novo arquivo for enviado.
        
        $sql = "UPDATE tblUsuario
                SET bio_usuario = ?,
                    fotoUsuario = CASE WHEN ? IS NOT NULL THEN CONVERT(varbinary(max), ?) ELSE fotoUsuario END,
                    fotoCapa = CASE WHEN ? IS NOT NULL THEN CONVERT(varbinary(max), ?) ELSE fotoCapa END
                WHERE idUsuario = ?";

        $params = array(
            $biografia,
            $fotoUsuarioBin, $fotoUsuarioBin, // Parâmetros para fotoUsuario
            $fotoCapaBin, $fotoCapaBin,     // Parâmetros para fotoCapa
            $userId
        );

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            $_SESSION["usuario_id"] = $userId;
            unset($_SESSION['idUsuario_setup']);
            header("Location: feed.php");
            exit();
        } else {
            throw new Exception("Erro ao atualizar perfil: " . print_r(sqlsrv_errors(), true));
        }
    } catch (Exception $e) {
        // Rollback em caso de erro
        rollbackRegistration($conn, $userId);

        // Redireciona para página de erro
        header("Location: Erro/Error.php?message=" . urlencode($e->getMessage()));
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
                    accept="image/jpeg,image/png,image/gif,image/webp"> <small>Formatos aceitos: JPEG, PNG, GIF, WebP. Tamanho máximo: 40MB. Dimensões: 100x100 a 5000x5000
                    pixels.</small>
                <div class="preview">
                    <img id="previewFoto" src="Assets/padrao.png" alt="Pré-visualização da foto de perfil">
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
                    <img id="previewCapa" src="Assets/padraoCapa.png" alt="Pré-visualização da foto de capa">
                </div>
            </div>

            <div class="form-group">
                <label for="biografia"><i class="fa-solid fa-pencil"></i> Biografia</label>
                <textarea id="biografia" name="biografia" maxlength="255" placeholder="Fale um pouco sobre você..." rows="4"></textarea>
                <small>Máximo de 255 caracteres.</small>
            </div>

            <input type="submit" value="Finalizar Cadastro">
        </form>
    </div>
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            const defaultFotoPerfil = 'Assets/padrao.png'; // URL da imagem padrão de perfil
            const defaultFotoCapa = 'Assets/padraoCapa.png'; // URL da imagem padrão de capa

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