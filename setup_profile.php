<?php
session_start();
include 'config.php';

if (!isset($_SESSION['idUsuario_setup'])) {
    header("Location: Cadastro/cadastro.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $biografia = $_POST["biografia"] ?? '';
    
    // Processa upload das imagens
    $fotoUsuario = null;
    $fotoCapa = null;
    
    if (!empty($_FILES["fotoUsuario"]["tmp_name"]) && is_uploaded_file($_FILES["fotoUsuario"]["tmp_name"])) {
        $fotoUsuario = file_get_contents($_FILES["fotoUsuario"]["tmp_name"]);
    }
    
    if (!empty($_FILES["fotoCapa"]["tmp_name"]) && is_uploaded_file($_FILES["fotoCapa"]["tmp_name"])) {
        $fotoCapa = file_get_contents($_FILES["fotoCapa"]["tmp_name"]);
    }
    
    // Consulta SQL corrigida com CONVERT explícito
    $sql = "UPDATE tblUsuario 
            SET bio_usuario = ?,
                fotoUsuario = CASE 
                    WHEN ? IS NOT NULL THEN CONVERT(varbinary(max), ?)
                    ELSE fotoUsuario 
                END,
                fotoCapa = CASE 
                    WHEN ? IS NOT NULL THEN CONVERT(varbinary(max), ?)
                    ELSE fotoCapa 
                END
            WHERE idUsuario = ?";
    
    $params = array(
        $biografia,
        $fotoUsuario, $fotoUsuario,
        $fotoCapa, $fotoCapa,
        $_SESSION['idUsuario_setup']
    );
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        $_SESSION["usuario_id"] = $_SESSION['idUsuario_setup'];
        unset($_SESSION['idUsuario_setup']);
        header("Location: feed.php");
        exit();
    } else {
        die("Erro ao atualizar perfil: " . print_r(sqlsrv_errors(), true));
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
    <link rel="stylesheet" href="css/style-setup-profile.css">
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>
<body>
    <div class="content">
        <div class="progress-info">
            <p>Etapa 2 de 2: <span>Configurar seu perfil</span></p>
            <p>Estamos quase lá, complete seu perfil para começar a usar o <span>Versami</span>.</p>
        </div>
                
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fotoUsuario"><i class="fa-solid fa-image"></i></label>
                <label for="fotoUsuario">Foto de Perfil</label>
                <input type="file" id="fotoUsuario" name="fotoUsuario" accept="image/*" onchange="previewImage(this, 'previewFoto')">
                <div class="preview">
                    <img id="previewFoto" src="<?php echo FOTO_PADRAO_PATH; ?>" style="display: block;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="fotoCapa"><i class="fa-solid fa-image"></i></label>
                <label for="fotoCapa">Foto de Capa</label>
                <input type="file" id="fotoCapa" name="fotoCapa" accept="image/*" onchange="previewImage(this, 'previewCapa')">
                <div class="preview">
                    <img id="previewCapa" src="<?php echo CAPA_PADRAO_PATH; ?>" style="display: block;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="biografia"><i class="fa-solid fa-book"></i></label>
                <label for="biografia"></i> Biografia</label>
                <textarea id="biografia" name="biografia" placeholder="Conte um pouco sobre você..."></textarea>
            </div>
            
            <input type="submit" value="Finalizar Cadastro">
        </form>
    </div>
</body>
</html>