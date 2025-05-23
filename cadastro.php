<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $arroba = $_POST["arroba_usuario"];
    $data_nasc = date("Y-m-d", strtotime($_POST["data_nasc"]));
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    // Verifica se usuário ou email já existe
    $checkUser = "SELECT COUNT(*) AS total FROM tblUsuario WHERE email = ? OR arroba_usuario = ?";
    $paramsCheck = array($email, $arroba);
    $stmtCheck = sqlsrv_query($conn, $checkUser, $paramsCheck);
    $row = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);

    if ($row['total'] > 0) {
        die("Erro: E-mail ou @usuário já cadastrado.");
    }

    // Insere no banco com valores padrão para os campos que serão preenchidos depois
    $sql = "INSERT INTO tblUsuario (nome, data_nasc, arroba_usuario, email, senha, fotoUsuario, fotoCapa, bio_usuario) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Valores padrão para as colunas que serão atualizadas depois
    $fotoPadrao = FOTO_PADRAO;
    $bioPadrao = '';

    $params = array($nome, $data_nasc, $arroba, $email, $senha, $fotoPadrao, $fotoPadrao, $bioPadrao);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        // Pega o ID do usuário recém-criado (forma específica para SQL Server)
        $sql_id = "SELECT idUsuario FROM tblUsuario WHERE email = ?";
        $params_id = array($email);
        $stmt_id = sqlsrv_query($conn, $sql_id, $params_id);
        $row_id = sqlsrv_fetch_array($stmt_id, SQLSRV_FETCH_ASSOC);

        if ($row_id) {
            $idUsuario = $row_id['idUsuario'];

            // Armazena o ID na sessão para usar na próxima página
            $_SESSION['idUsuario_setup'] = $idUsuario;
            $_SESSION['email_usuario'] = $email;

            // Redireciona para a página de setup
            header("Location: setup_profile.php");
            exit();
        } else {
            die("Erro ao obter ID do usuário.");
        }
    } else {
        die("Erro ao cadastrar: " . print_r(sqlsrv_errors(), true));
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Versami</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #1DA1F2;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .error {
            color: red;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <h1>Criar sua conta</h1>
    <form method="POST">
        <div class="form-group">
            <label for="nome">Nome completo:</label>
            <input type="text" id="nome" name="nome" required>
        </div>

        <div class="form-group">
            <label for="arroba_usuario">Nome de usuário (@):</label>
            <input type="text" id="arroba_usuario" name="arroba_usuario" required>
        </div>

        <div class="form-group">
            <label for="data_nasc">Data de nascimento:</label>
            <input type="date" id="data_nasc" name="data_nasc" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>

        <input type="submit" value="Continuar">
    </form>
    <script src="js/script.js"></script>
    <script src="js/theme-switcher.js"></script>
</body>

</html>