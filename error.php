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
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        
        .error-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        
        .error-icon {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .error-message {
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }
        
        .btn-retry {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-retry:hover {
            background-color: #2980b9;
        }
        
        .logo {
            margin-bottom: 20px;
        }
        
        .logo img {
            height: 40px;
        }
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
        <a href="Cadastro/cadastro.php" class="btn-retry">Tentar novamente</a>
    </div>

    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
</body>
</html>