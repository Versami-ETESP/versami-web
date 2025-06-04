<?php
session_start();
include 'config.php';

if (!isset($_GET['username'])) {
  header("Location: index.php");
  exit();
}

$username = $_GET['username'];

// Buscar dados do usuário pelo nome de usuário
$query = "SELECT * FROM users WHERE username = ?";
$params = array($username);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false || !$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
  echo "Usuário não encontrado.";
  exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="keywords" content="Livros, Rede Social, Avaliações" />
  <meta name="description"
    content="Versami, sua rede social para conectar leitores de todos os gêneros literários. Aqui, você pode avaliar, descobrir e compartilhar livros, criando uma comunidade engajada de apaixonados por leitura." />
  <meta name="author" content="Julia Maria, Matheus Canesso, Thamiris Fernandes, Ygor Silva" />
  <link rel="shortcut icon" href="../../Assets/favicon.png" type="favicon" />
  <link rel="icon" href="../Assets/iconVersami.png" />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=library_books" />
  <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="StyleProfileUser.css" />
  <title>Perfil de <?php echo htmlspecialchars($user['fullname']); ?></title>
</head>

<body>
  <div class="content">
    <div class="header-menu">
      <button class="menu-btn" id="menuBtn" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
      </button>
      <!-- <div class="overlay" id="overlay" onclick="toggleMenu()"></div> -->
      <div class="sidebar" id="sidebar">
        <div class="top-content-sidebar">
          <img src="Assets/logoVersamiBlue.png" alt="Versami" />
          <ul>
            <li onclick="location.href='../../Feed/HTML/Index.html'">
              <i class="fa-solid fa-house"></i> Home
            </li>
            <li onclick="location.href='explorar.php'" class="active">
              <i class="fa-solid fa-magnifying-glass"></i> Explore
            </li>
            <li onclick="location.href='../../Blog-Usuarios/HTML/BlogUsuarios.html'">
              <i class="fa-solid fa-newspaper"></i> Blog
            </li>
            <li onclick="location.href='../../Notificacoes/HTML/Notificacao.html'">
              <i class="fa-solid fa-bell"></i> Notificação
            </li>
            <li onclick="location.href='profile.php'">
              <i class="fa-solid fa-user"></i> Perfil
            </li>
          </ul>
          <div class="button" onclick="abrirModalPopUp()">
            <i class="fa-solid fa-pen"></i> Avaliação
          </div>
        </div>
        <div class="button-content">
          <div class="buttonOff">
            <a href="../../BD/logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
          </div>
        </div>
      </div>
    </div>
    <div class="principal-content">
      <section class="user">
        <div class="capa-perfil">
          <img id="fotoCapa" src="uploads/<?php echo htmlspecialchars($user['cover_pic']); ?>" class="cover">
        </div>
        <div class="content-user-perfil">
          <div class="foto-perfil">
            <img id="fotoPerfil" src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-pic"
              onclick="expandImage()">
          </div>
          <div class="info-perfil">
            <div class="nomes-perfil">
              <h1 class="nome-perfil" id="userNome"><?php echo htmlspecialchars($user['fullname']); ?></h1>
              <h4 class="arroba-perfil" id="userArroba">@<?php echo htmlspecialchars($user['username']); ?></h4>
            </div>
          </div>
        </div>
        <div class="bio-perfil">
          <div class="bio-content-perfil">
            <h5><?php echo htmlspecialchars($user['bio']); ?></h5>
          </div>
        </div>
      </section>
      <section class="info-contagem">
        <div class="contagem">
          <div class="contagem-entrada">
            <i class="fa-solid fa-calendar"></i>
            <h4>Entrou em <span>2025</span></h4>
          </div>
          <div class="contagem-item">
            <p>[0]</p>
            <h4>Seguindo</h4>
          </div>
          <div class="contagem-item">
            <p>[0]</p>
            <h4>Seguidores</h4>
          </div>
          <div class="contagem-item">
            <p>[0]</p>
            <h4>Leituras</h4>
          </div>
        </div>
      </section>
      <section class="posts" id="posts">
        <div class="tabs">
          <button class="tab active" onclick="changeTab(event, 'reviews')">
            Reviews
          </button>
          <button class="tab" onclick="changeTab(event, 'favorites')">
            Livros favoritos
          </button>
        </div>
      </section>
    </div>
    <div class="popup-overlay">
      <div class="popup">
        <div class="btn-top-content">
          <div class="btn-close-content">
            <button class="btn-close"><i class="fa-solid fa-x"></i></button>
          </div>
          <h2>Criar Review</h2>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <textarea name="texto" maxlength="380" id="review-content" rows="7" cols="7"></textarea><br>
          <div id="previewDiv">
            <div class="contentPreview">
              <img id="previewImg" src="" alt="Prévia da imagem">
            </div>
          </div>
          <div class="icons-content">
            <div class="icons-left-content">
              <div class="icon-class">
                <label for="inputImagem" id="idIconeImagem"><i id="iconeImagem" class="fa-regular fa-image"></i></label>
                <input type="file" id="inputImagem" name="imagem" onchange="mostrarImagemSelecionada()"><br>
              </div>
              <div class="icon-class">
                <i class="fa-solid fa-book"></i>
              </div>
              <div class="icon-class">
                <i class="fa-solid fa-star"></i>
              </div>
            </div>
            <div class="icons-right-content">
              <input class="btn-submit" type="submit" id="publicarPost" value="Postar">
            </div>
        </form>
      </div>
    </div>
  </div>
  <div class="overlay" id="overlay" onclick="closeImage(event)">
    <span class="close-btn" onclick="closeImage(event)">&times;</span>
    <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Imagem Expandida"
      onclick="event.stopPropagation()">
  </div>

  <script src="http://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script type="text/javascript" src="ScriptProfileUser.js"></script>
  <script type="text/javascript" src="ScriptGeral.js"></script>
</body>

</html>