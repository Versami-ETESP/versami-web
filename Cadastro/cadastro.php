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

    // verifica se a data de nascimento foi inserida no formato correto
    $formato = 'Y-m-d';
    $dataFormatada = DateTime::createFromFormat($formato, $_POST['data_nasc']);

    if (!($dataFormatada && $dataFormatada->format($formato) === $_POST['data_nasc'])) {
      throw new Exception("Data de Nascimento no formato incorreto");
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

    // CONVERSÃO PARA UPPERCASE ANTES DE HASHEAR
    $respostaSecreta_uppercase = mb_strtoupper($respostaSecreta, 'UTF-8'); // Use mb_strtoupper para UTF-8
    // Criptografa a resposta secreta (SHA-256 para segurança)
    $respostaSecretaHash = hash("sha256", $respostaSecreta_uppercase); // Use a versão em maiúsculas

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
    // Carrega imagens padrão como streams binários para evitar problemas de codificação
    $fotoPadraoStream = null;
    if (file_exists(FOTO_PADRAO_PATH)) {
        $fotoPadraoStream = fopen('php://memory', 'r+');
        fwrite($fotoPadraoStream, file_get_contents(FOTO_PADRAO_PATH));
        rewind($fotoPadraoStream);
    }

    $capaPadraoStream = null;
    if (file_exists(CAPA_PADRAO_PATH)) {
        $capaPadraoStream = fopen('php://memory', 'r+');
        fwrite($capaPadraoStream, file_get_contents(CAPA_PADRAO_PATH));
        rewind($capaPadraoStream);
    }

    // Inserção no banco, incluindo pergunta e resposta secreta
    // Removido CONVERT(VARBINARY(MAX), ?) do SQL pois o tipo será definido no array de parâmetros
    $sql = "INSERT INTO tblUsuario (nome, data_nasc, arroba_usuario, email, senha, fotoUsuario, fotoCapa, bio_usuario, idPergunta, resposta)
                VALUES (?, ?, ?, ?, ?, ?, ?, '', ?, ?);
                SELECT SCOPE_IDENTITY() AS idUsuario;";

    // Define os parâmetros, especificando o tipo para os campos binários
    $params = array(
        $nome,
        $data_nasc,
        $arroba,
        $email,
        $senhaHash,
        array($fotoPadraoStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max')),
        array($capaPadraoStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max')),
        $idPergunta,
        $respostaSecretaHash // Use o hash da resposta em maiúsculas
    );

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
      if (sqlsrv_next_result($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row) {
          $_SESSION['idUsuario_setup'] = $row['idUsuario'];
          $_SESSION['email_usuario'] = $email;
          header("Location: ../SetupProfile/SetupProfile.php");
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
    header("Location: ../Erro/Error.php?message=" . urlencode($e->getMessage()));
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
  <link rel="stylesheet" href="CSS/CadastroStyle.css">
  <title>Versami | Crie sua conta</title>
</head>

<body>
  <header class="glass">
    <nav>
      <div class="logo">
        <img src="../Assets/logoVersamiBlue.png" alt="Logo Versami" />
      </div>
      <ul class="nav-links">
        <li>
          <a href="../Index/Index.php" id="inicio-link"><i class="fa-solid fa-house"></i></a>
        </li>
        <li>
          <a href="../Sobre/Sobre.php" id="sobre-link"><i class="fa-solid fa-book-open"></i></a>
        </li>
        <li>
          <a href="../Login/Login.php" id="login-link" class="active"><i class="fa-solid fa-user"></i></a>
        </li>
      </ul>
    </nav>
    <div class="glass-gradient-line"></div>
  </header>
  <h1 class="tituloPrincipal">
    Descubra agora a <span>Versami!</span>
  </h1>
  <main class="cadastroMain">
    <div class="loginPrincipal">
      <div class="loginPanel">
        <h1>Já possui conta na <br /><span>Versami?</span></h1>
        <p>
          Entre e avalie<br> seus livros favoritos!
        </p>
        <a class="button loginButton" href="../Login/Login.php">Entrar agora <i class="fa-solid fa-chevron-right"></i></a>
      </div>
    </div>
    <div class="cadastroPrincipal msgindex">
      <h2 class="titulo1">Criar Conta</h2>
      <span id="alerta">Mensagem</span>
      <form id="form" class="login-form" autocomplete="off" method="POST" enctype="multipart/form-data">
        <div class="input-group">
          <i class="material-icons-outlined required">person</i>
          <input type="text" name="nome" id="nome" maxlength="50" class="form-input" placeholder="Nome completo"
            required>
        </div>
        <div class="input-group">
          <i class="material-icons-outlined required">alternate_email</i>
          <input type="text" name="arroba_usuario" id="arroba_usuario" class="form-input" placeholder="Nome de usuário"
            required>
        </div>
        <div class="input-group">
          <i class="material-icons-outlined required">calendar_today</i>
          <input type="date" name="data_nasc" id="data_nasc" placeholder="Data de Nascimento" required>
        </div>
        <div class="input-group">
          <i class="material-icons-outlined required">mail</i>
          <input type="email" name="email" id="email" maxlength="80" class="form-input" placeholder="E-mail" required>
        </div>
        <div class="input-group">
          <i class="material-icons-outlined required">lock</i>
          <input type="password" name="senha" id="senha" class="form-input" placeholder="Digite sua senha" required>
        </div>
        <div class="input-group">
          <i class="material-icons-outlined required">lock</i>
          <input type="password" name="confirma" id="confirma" class="form-input" placeholder="Confirme sua senha">
        </div>
        <div class="input-group">
            <i class="material-icons-outlined required">help_outline</i>
            <select name="pergunta_secreta" id="pergunta_secreta" class="form-input" required>
              <option value="">Selecione uma pergunta secreta</option>
              <?php foreach ($perguntas as $pergunta): ?>
                <option value="<?= htmlspecialchars($pergunta['idPergunta']) ?>">
                  <?= htmlspecialchars($pergunta['pergunta']) ?>
                </option>
              <?php endforeach; ?>
            </select>
        </div>
        <div class="input-group">
            <i class="material-icons-outlined required">vpn_key</i>
            <input type="text" name="resposta_secreta" id="resposta_secreta" maxlength="255" class="form-input"
              placeholder="Sua resposta secreta" required>
        </div>
        <input type="submit" value="Criar conta" id="enviar" class="button">
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