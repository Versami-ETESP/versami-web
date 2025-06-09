<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $arroba = $_POST["arroba_usuario"];
    $data_nasc = date("Y-m-d", strtotime($_POST["data_nasc"]));
    $email = $_POST["email"];
    //$senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $senha = hash("sha256",$_POST["senha"]);

    // Verifica se usuário ou email já existe
    $checkUser = "SELECT COUNT(*) AS total FROM tblUsuario WHERE email = ? OR arroba_usuario = ?";
    $paramsCheck = array($email, $arroba);
    $stmtCheck = sqlsrv_query($conn, $checkUser, $paramsCheck);
    
    if ($stmtCheck === false) {
        die("Erro ao verificar usuário: " . print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
    if ($row['total'] > 0) {
        die("Erro: E-mail ou @usuário já cadastrado.");
    }

    // Carrega imagens padrão como binário
    $fotoPadrao = file_exists(FOTO_PADRAO_PATH) ? file_get_contents(FOTO_PADRAO_PATH) : null;
    $capaPadrao = file_exists(CAPA_PADRAO_PATH) ? file_get_contents(CAPA_PADRAO_PATH) : null;

    // SQL modificado para usar CONVERT explicitamente
    $sql = "INSERT INTO tblUsuario (nome, data_nasc, arroba_usuario, email, senha, fotoUsuario, fotoCapa, bio_usuario) 
            VALUES (?, ?, ?, ?, ?, CONVERT(varbinary(max), ?), CONVERT(varbinary(max), ?), ?);
            SELECT SCOPE_IDENTITY() AS idUsuario;";
    
    $params = array(
        $nome, 
        $data_nasc, 
        $arroba, 
        $email, 
        $senha, 
        $fotoPadrao, 
        $capaPadrao, 
        ''
    );
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        // Move para o próximo resultado (que contém o SCOPE_IDENTITY)
        if (sqlsrv_next_result($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($row) {
                $_SESSION['idUsuario_setup'] = $row['idUsuario'];
                $_SESSION['email_usuario'] = $email;
                header("Location: ../SetupProfile/SetupProfile.php");
                exit();
            }
        }
        die("Erro ao obter ID do usuário: não foi possível recuperar o SCOPE_IDENTITY");
    } else {
        $errors = sqlsrv_errors();
        $errorMessage = "Erro ao criar conta: ";
        foreach ($errors as $error) {
            $errorMessage .= "SQLSTATE: " . $error['SQLSTATE'] . ", code: " . $error['code'] . " - " . $error['message'];
        }
        die($errorMessage);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="Livros, Rede Social, Avaliações" />
  <meta name="description"
    content="Versami, sua rede social para conectar leitores de todos os gêneros literários. Aqui, você pode avaliar, descobrir e compartilhar livros, criando uma comunidade engajada de apaixonados por leitura." />
  <meta name="author" content="Julia Maria, Matheus Canesso, Thamiris Fernandes, Ygor Silva" />
  <link rel="shortcut icon" href="../../Assets/favicon.png" type="favicon" />
  <link rel="icon" href="../Assets/iconVersami.png">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

  <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
  <script src="http://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script type="text/javascript" src="../JS/user-login.js"></script>
  <link rel="stylesheet" href="CSS/style.css">
  <link rel="stylesheet" href="CSS/Style-headerfooter.css">
  <title>Versami | Crie sua conta</title>
</head>

<body>
  <header class="glass">
    <nav>
      <div class="logo">
        <img src="../Assets/logoVersamiBlue.png" alt="Logo Versami" />
      </div>
      <ul class="nav-links">
        <li><a href="../Index/index.php" id="inicio-link" class="active">Início</a></li>
        <li><a href="../Sobre/sobre.php" id="sobre-link">Sobre nós</a></li>
        <li><a href="../Blog/blog.php" id="blog-link">Blog</a></li>
        <li><a href="../Contato/contato.php" id="contato-link">Contato</a></li>
      </ul>
      <div class="user-icon">
        <span class="material-icons-outlined"><a href="../../Login/HTML/login.html"> account_circle </a></span>
      </div>
    </nav>
  </header>
  <h1 class="tituloPrinc">Descubra agora a <span class="versami">Versami!</span></h1>
  <main>
    <div class="msg">
      <h1 class="msgTitulo">Bem vindo(a)
        <br>de volta!
      </h1>
      <p class="msgTexto">Acesse agora sua conta e
        <br>avalie seus livros favoritos!
      </p>
      <a class="btnLogin" href="../Login/login.php">Entrar <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="principal">
      <h2 class="titulo1">Criar Conta</h2>
      <span id="alerta">Mensagem</span>
      <form id="form" class="form" autocomplete="off" method="POST" enctype="multipart/form-data">
        <div class="envelope">
          <i class="material-icons-outlined required">person</i>
          <input type="text" name="nome" id="nome" maxlength="50" class="entrada" placeholder="Nome completo" required>
        </div>
        <div class="envelope">
          <i class="material-icons-outlined required">alternate_email</i>
          <input type="text" name="arroba_usuario" id="arroba_usuario" class="entrada" placeholder="Nome de usuário"
            required>
        </div>
        <div class="envelope">
          <i class="material-icons-outlined required">calendar_today</i>
          <input type="date" name="data_nasc" id="data_nasc" placeholder="Data de Nascimento" required>
        </div>
        <div class="envelope">
          <i class="material-icons-outlined required">mail</i>
          <input type="email" name="email" id="email" maxlength="80" class="entrada" placeholder="E-mail" required>
        </div>
        <div class="envelope">
          <i class="material-icons-outlined required">lock</i>
          <input type="password" name="senha" id="senha" class="entrada" placeholder="Digite sua senha" required>
        </div>
        <div class="envelope">
          <i class="material-icons-outlined required">lock</i>
          <input type="password" name="confirma" id="confirma" class="entrada" placeholder="Confirme sua senha">
        </div>
        <input type="submit" value="Criar conta" id="enviar" class="btnPersonalizar">
      </form>
    </div>
  </main>
  <div class="carregando" id="carregando"></div>

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
        <p>2024 | Versami Corporation &copy; </p>
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
</body>
</html>