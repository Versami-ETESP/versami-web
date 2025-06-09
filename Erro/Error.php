<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro no Cadastro | Versami</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/StyleError.css">
    <style>
        
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo">
            <img src="Assets/logoVersamiBlue.png" alt="Versami">
        </div>
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Erro no Cadastro</h1>
        <div class="error-message">
            <?php 
            if (isset($_GET['message'])) {
                echo htmlspecialchars(urldecode($_GET['message']));
            } else {
                echo "Ocorreu um erro desconhecido durante o cadastro.";
            }
            ?>
        </div>
        <a href="../Cadastro/cadastro.php" class="btn-retry">Tentar novamente</a>
    </div>

    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
</body>
</html>