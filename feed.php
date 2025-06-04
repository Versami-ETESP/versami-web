<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
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
  <link rel="stylesheet" href="StyleFeed.css" />
  <title>Perfil de usuario</title>
</head>

<body>
  <div class="content">
    <div class="header-menu">
      <button class="menu-btn" id="menuBtn" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
      <div class="sidebar" id="sidebar">
        <div class="top-content-sidebar">
          <img src="Assets/logoVersamiBlue.png" alt="Versami" />
          <ul>
            <li onclick="location.href='feed.php'" class="active">
              <i class="fa-solid fa-house"></i> Home
            </li>
            <li onclick="location.href='explorar.php'">
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
      <div class="user">
        <div class="posts" id="posts">
          <div class="tabs-container">
            <img src="Assets/logoVersamiBlue.png" alt="Versami" />
            <div class="tabs">
              <div class="tab active" onclick="changeTab(0)">Reviews</div>
              <div class="tab" onclick="changeTab(1)">Livros Favoritos</div>
            </div>
            <div class="content-container">
              <div class="contentPosts active">
                <div class="containerContent">
                  <p>Conteúdo de Reviews</p>
                </div>
              </div>
              <div class="contentPosts">
                <div class="containerContent">
                  <p>Conteúdo de Livros Favoritos</p>
                </div>
              </div>
            </div>
          </div>
        </div>
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
                  <label for="inputImagem" id="idIconeImagem"><i id="iconeImagem"
                      class="fa-regular fa-image"></i></label>
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
      <div id="postMessage"></div>
    </div>

    <script src="http://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="ScriptGeral.js"></script>
    <script type="text/javascript" src="ScriptFeed.js"></script>
</body>

</html>