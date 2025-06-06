<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  try {
    // Sanitização e validação
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $arroba = filter_input(INPUT_POST, 'arroba_usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma'] ?? '';
    $data_raw = filter_input(INPUT_POST, 'data_nasc', FILTER_SANITIZE_STRING);
    $data_nasc = date("Y-m-d", strtotime($data_raw));

    // Novos campos para Pergunta Secreta
    $idPergunta = filter_input(INPUT_POST, 'pergunta_secreta', FILTER_VALIDATE_INT);
    $respostaSecreta = filter_input(INPUT_POST, 'resposta_secreta', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validação
    if (!$nome || !$arroba || !$email || !$senha || !$confirma || !$data_nasc || !$idPergunta || !$respostaSecreta) {
      throw new Exception("Todos os campos devem ser preenchidos corretamente, incluindo a pergunta e resposta secreta.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new Exception("E-mail inválido.");
    }

    if ($senha !== $confirma) {
      throw new Exception("As senhas não coincidem.");
    }

    // Verifica idade mínima (13 anos)
    $dataAtual = new DateTime();
    $dataNascimento = new DateTime($data_nasc);
    $idade = $dataAtual->diff($dataNascimento)->y;

    if ($idade < 13) {
      throw new Exception("Você deve ter pelo menos 13 anos para se cadastrar.");
    }

    // Criptografa a senha (SHA-256)
    $senhaHash = hash("sha256", $senha);

    // Criptografa a resposta secreta (SHA-256 para segurança)
    $respostaSecretaHash = hash("sha256", $respostaSecreta);

    // Verifica se o e-mail ou arroba já está cadastrado
    $checkUser = "SELECT COUNT(*) AS total FROM tblUsuario WHERE email = ? OR arroba_usuario = ?";
    $paramsCheck = [$email, $arroba];
    $stmtCheck = sqlsrv_query($conn, $checkUser, $paramsCheck);

    if ($stmtCheck === false) {
      throw new Exception("Erro ao verificar usuário. Por favor, tente novamente.");
    }

    $row = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
    if ($row['total'] > 0) {
      throw new Exception("E-mail ou @usuário já cadastrado.");
    }

    // Carrega imagens padrão como binário
    $fotoPadrao = file_exists(FOTO_PADRAO_PATH) ? file_get_contents(FOTO_PADRAO_PATH) : null;
    $capaPadrao = file_exists(CAPA_PADRAO_PATH) ? file_get_contents(CAPA_PADRAO_PATH) : null;

    // Inserção no banco, incluindo pergunta e resposta secreta
    $sql = "INSERT INTO tblUsuario (nome, data_nasc, arroba_usuario, email, senha, fotoUsuario, fotoCapa, bio_usuario, idPergunta, resposta)
                VALUES (?, ?, ?, ?, ?, CONVERT(varbinary(max), ?), CONVERT(varbinary(max), ?), '', ?, ?);
                SELECT SCOPE_IDENTITY() AS idUsuario;";

    $params = [
      $nome,
      $data_nasc,
      $arroba,
      $email,
      $senhaHash,
      $fotoPadrao,
      $capaPadrao,
      $idPergunta,
      $respostaSecretaHash // Armazena a resposta criptografada
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
      if (sqlsrv_next_result($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row) {
          $_SESSION['idUsuario_setup'] = $row['idUsuario'];
          $_SESSION['email_usuario'] = $email;
          header("Location: ../setup_profile.php");
          exit();
        }
      }
      throw new Exception("Erro ao obter ID do usuário. Por favor, tente novamente.");
    } else {
      $errors = sqlsrv_errors();
      $errorMessage = "Erro ao criar conta: ";
      foreach ($errors as $error) {
        $errorMessage .= "SQLSTATE: " . $error['SQLSTATE'] . ", code: " . $error['code'] . " - " . $error['message'];
      }
      throw new Exception($errorMessage);
    }
  } catch (Exception $e) {
    header("Location: ../error.php?message=" . urlencode($e->getMessage()));
    exit();
  }
}

// Obter as perguntas secretas do banco de dados para popular o dropdown
$perguntas = [];
$sql_perguntas = "SELECT idPergunta, pergunta FROM tblPerguntaSecreta ORDER BY idPergunta";
$result_perguntas = sqlsrv_query($conn, $sql_perguntas);

if ($result_perguntas) {
  while ($row = sqlsrv_fetch_array($result_perguntas, SQLSRV_FETCH_ASSOC)) {
    $perguntas[] = $row;
  }
} else {
  // Logar ou exibir erro se não conseguir buscar as perguntas
  error_log("Erro ao buscar perguntas secretas: " . print_r(sqlsrv_errors(), true));
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
        <div class="envelope">
          <i class="material-icons-outlined required">help_outline</i>
          <select name="pergunta_secreta" id="pergunta_secreta" class="entrada" required>
            <option value="">Selecione uma pergunta secreta</option>
            <?php foreach ($perguntas as $pergunta): ?>
              <option value="<?= htmlspecialchars($pergunta['idPergunta']) ?>">
                <?= htmlspecialchars($pergunta['pergunta']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="envelope">
          <i class="material-icons-outlined required">vpn_key</i>
          <input type="text" name="resposta_secreta" id="resposta_secreta" maxlength="255" class="entrada"
            placeholder="Sua resposta secreta" required>
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