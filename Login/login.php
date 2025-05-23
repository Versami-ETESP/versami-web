<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    // Busca usuário com verificação de conta ativa
    $sql = "SELECT idUsuario, nome, senha FROM tblUsuario WHERE email = ?";
    $params = array($email);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($user) {
        // Usuário existe, verifica a senha
        if (password_verify($senha, $user["senha"])) {
            $_SESSION["usuario_id"] = $user["idUsuario"];
            $_SESSION["nome"] = $user["nome"];
            
            // Busca informações completas do usuário
            $sql_info = "SELECT nome, arroba_usuario, fotoUsuario, fotoCapa, bio_usuario 
                         FROM tblUsuario WHERE idUsuario = ?";
            $params_info = array($user["idUsuario"]);
            $stmt_info = sqlsrv_query($conn, $sql_info, $params_info);
            $user_info = sqlsrv_fetch_array($stmt_info, SQLSRV_FETCH_ASSOC);
            
            $_SESSION["user_info"] = $user_info;
            
            header("Location: ../feed.php");
            exit();
        } else {
            // Senha incorreta
            $_SESSION["login_error"] = "Email ou senha incorretos.";
        }
    } else {
        // Usuário não existe
        $_SESSION["login_error"] = "Email ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="Livros, Rede Social, Avaliações" />
    <link rel="icon" type="image/x-icon" href="../Assets/favicon.png" />
    <meta name="description"
        content="Versami, sua rede social para conectar leitores de todos os gêneros literários. Aqui, você pode avaliar, descobrir e compartilhar livros, criando uma comunidade engajada de apaixonados por leitura." />
    <meta name="author" content="Julia Maria, Matheus Canesso, Thamiris Fernandes, Ygor Silva" />
    <link rel="shortcut icon" href="../../Assets/favicon.png" type="favicon" />
    <link rel="icon" href="../Assets/iconVersami.png" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="CSS/style.css" />
    <link rel="stylesheet" href="CSS/Style-headerfooter.css" />
    <title>Versami | Acesse sua conta</title>
    <style>
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            padding: 8px;
            background-color: #ffeeee;
            border-radius: 4px;
            border: 1px solid #ffcccc;
        }
    </style>
</head>

<body>
    <header class="glass">
        <nav>
            <div class="logo">
                <img src="../Assets/logoVersamiBlue.png" alt="Logo Versami" />
            </div>
            <ul class="nav-links">
                <li>
                    <a href="../Index/Index.php" id="inicio-link" class="active">Início</a>
                </li>
                <li>
                    <a href="../Sobre/sobre.php" id="sobre-link">Sobre nós</a>
                </li>
                <li><a href="../Blog/blog.php" id="blog-link">Blog</a></li>
                <li>
                    <a href="../Contato/contato.php" id="contato-link">Contato</a>
                </li>
            </ul>
            <div class="user-icon">
                <span class="material-icons-outlined"><a href="login.html"> account_circle </a></span>
            </div>
        </nav>
    </header>
    <h1 class="tituloPrinc">
        Acesse agora a <span class="versami">Versami!</span>
    </h1>
    <main>
        <div class="principal">
            <h2 class="titulo1">Acessar Conta</h2>
            <span id="alerta"></span>
            <?php if (isset($_SESSION['login_error'])): ?>
                <p class="error-message">
                    <i class="material-icons-outlined" style="vertical-align: middle; font-size: 18px;">error_outline</i>
                    <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                </p>
            <?php endif; ?>
            <form autocomplete="off" class="form" method="POST">
                <div class="envelope">
                    <i class="material-icons-outlined required">alternate_email</i>
                    <input type="email" name="email" id="login2" class="entrada" placeholder="Digite seu email"
                        required />
                </div>
                <div class="envelope">
                    <i class="material-icons-outlined required">lock</i>
                    <input type="password" name="senha" id="senha2" class="entrada" placeholder="Digite sua senha"
                        required />
                </div>
                <input type="submit" value="Entrar" id="enviar" class="btnPersonalizar" />
            </form>
        </div>
        <div class="msg msgindex">
            <h1 class="msgTitulo">Sua primeira vez <br />aqui?</h1>
            <p class="msgTexto">
                Crie agora sua conta e <br />encontre diversos livros!
            </p>
            <a class="btnCadastro" href="../Cadastro/cadastro.php">Criar Conta <i class="fa-solid fa-chevron-right"></i></a>
        </div>
        <div class="carregando" id="carregando"></div>
    </main>
    <footer>
        <div class="footer-content">
            <div class="newsletter">
                <h4>Acompanhe nossa</h4>
                <h1>Newsletter</h1>
                <form>
                    <input type="email" placeholder="Seu email" required />
                    <button type="submit">Inscrever-se</button>
                </form>
            </div>
            <div class="middle">
                <div class="social-image">
                    <img src="../Assets/logoVersami.png" alt="Logo Versami" />
                </div>
                <p class="pRedes">Siga nossas redes sociais</p>
                <div class="social-links">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#"><i class="fa-brands fa-tiktok"></i></a>
                </div>
                <p>2024 | Versami Corporation &copy;</p>
            </div>
            <div class="about">
                <h4>Sobre nós</h4>
                <p>
                    Somos uma rede social voltada para conectar leitores de todos os
                    gêneros literários. Aqui, você pode avaliar, descobrir e
                    compartilhar livros, criando uma comunidade engajada de apaixonados
                    por leitura. Acesse pelo site ou aplicativo Android!
                </p>
            </div>
        </div>
    </footer>
    <script src="../JS/Script.js"></script>
    <script src="http://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../JS/user-login.js"></script>
</body>

</html>