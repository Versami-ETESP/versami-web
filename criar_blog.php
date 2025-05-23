<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['conteudo'];
    $imagem = null;

    if (!empty($_FILES['imagem']['tmp_name'])) {
        $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
    }

    $sql = "INSERT INTO tblBlogPost (titulo, conteudo, dataPost, imagem, idAdmin) 
            VALUES (?, ?, GETDATE(), ?, ?)";
    
    // Assume que o admin está logado (precisa de verificação adicional)
    $admin_id = $_SESSION["usuario_id"]; // Ou session específica para admin
    
    $stmt = sqlsrv_query($conn, $sql, array($titulo, $conteudo, $imagem, $admin_id));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Criar Post - Versami</title>
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            min-height: 300px;
        }

        button {
            background: #1DA1F2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Criar Novo Post</h1>
        <?php if (isset($erro)): ?>
            <div style="color: red;"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label for="conteudo">Conteúdo:</label>
                <textarea id="conteudo" name="conteudo" required></textarea>
            </div>

            <div class="form-group">
                <label for="imagem">Imagem (opcional):</label>
                <input type="file" id="imagem" name="imagem">
            </div>

            <button type="submit">Publicar Post</button>
        </form>
    </div>
</body>

</html>