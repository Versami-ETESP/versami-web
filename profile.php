<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$user = $_SESSION['user'];

// Inicializar variáveis para seguidores e seguindo
$total_seguidores = 0;
$total_seguindo = 0;

// Buscar o total de seguidores
$sql_seguidores = "SELECT COUNT(*) AS total_seguidores FROM Seguidores WHERE seguido_id = ?";
$params_seguidores = array($user);
$result_seguidores = sqlsrv_query($conn, $sql_seguidores, $params_seguidores);

if ($result_seguidores && sqlsrv_has_rows($result_seguidores)) {
  $row_seguidores = sqlsrv_fetch_array($result_seguidores, SQLSRV_FETCH_ASSOC);
  $total_seguidores = $row_seguidores['total_seguidores'];
}

// Buscar o total de usuários que o usuário logado está seguindo
$sql_seguindo = "SELECT COUNT(*) AS total_seguindo FROM Seguidores WHERE seguidor_id = ?";
$params_seguindo = array($user);
$result_seguindo = sqlsrv_query($conn, $sql_seguindo, $params_seguindo);

if ($result_seguindo && sqlsrv_has_rows($result_seguindo)) {
  $row_seguindo = sqlsrv_fetch_array($result_seguindo, SQLSRV_FETCH_ASSOC);
  $total_seguindo = $row_seguindo['total_seguindo'];
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
  <link rel="stylesheet" href="StyleProfile.css" />
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
            <li onclick="location.href='feed.php'">
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
            <li onclick="location.href='profile.html'" class="active">
              <i class="fa-solid fa-user"></i> Perfil
            </li>
          </ul>
          <div class="button" onclick="abrirModalPopUp()">
            <i class="fa-solid fa-pen"></i> Avaliação
          </div>
        </div>
        <div class="button-content">
          <div class="buttonOff">
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
          </div>
        </div>
      </div>
    </div>
    <div class="principal-content">
      <div class="user">
        <div class="capa-perfil">
          <img id="fotoCapa" src="uploads/<?php echo htmlspecialchars($user['cover_pic']); ?>" class="cover">
        </div>
        <div class="content-user-perfil">
          <div class="foto-perfil">
            <img id="fotoPerfil" src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>"
              class="profile-pic">
          </div>
          <div class="info-perfil">
            <div class="nomes-perfil">
              <h1 class="nome-perfil" id="userNome"><?php echo htmlspecialchars($user['fullname']); ?></h1>
              <h4 class="arroba-perfil" id="userArroba">@<?php echo htmlspecialchars($user['username']); ?></h4>
            </div>
            <button class="buton-editar-perfil" type="button" onclick="abrirModalPopUpEdicao()">
              Editar Perfil
            </button>
          </div>
        </div>
        <div class="bio-perfil">
          <div class="bio-content-perfil">
            <h5><?php echo htmlspecialchars($user['bio']); ?></h5>
          </div>
        </div>
      </div>
      <div class="info-contagem">
        <div class="contagem">
          <div class="contagem-entrada">
            <i class="fa-solid fa-calendar"></i>
            <h4>Entrou em <span>2025</span></h4>
          </div>
          <div class="contagem-item">
            <p><?= $total_seguindo ?></p>
            <h4>Seguindo</h4>
          </div>
          <div class="contagem-item">
            <p><?= $total_seguidores ?></p>
            <h4>Seguidores</h4>
          </div>
          <div class="contagem-item">
            <p>[0]</p>
            <h4>Reviews</h4>
          </div>
        </div>
      </div>
      <div class="posts" id="posts">
        <div class="tabs-container">
          <div class="tabs">
            <div class="tab active" onclick="changeTab(0)">Reviews</div>
            <div class="tab" onclick="changeTab(1)">Livros Favoritos</div>
          </div>
          <div class="content-container">
            <div class="contentPosts active">
              <div class="containerContent">
                
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
      <div class="popup-editar-overlay">
        <div class="popup-editar">
          <div class="btn-close-content">
            <button class="btn-closeEdicao">
              <i class="fa-solid fa-x"></i>
            </button>
          </div>
          <form id="formEdicao" method="POST" enctype="multipart/form-data" style="display: block">
            <div class="content-form">
              <div class="content-form-imgs">
                <div class="content-profile-item">
                  <label>Imagem de Capa:</label>
                  <label class="picture" for="picture__input" tabindex="0">
                    <span class="picture__image">Escolha uma imagem</span>
                  </label>

                  <input type="file" name="picture__input" id="picture__input" />
                </div>

                <div class="content-profile-item">
                  <label>Foto de Perfil:</label>
                  <label class="picture_perfil" for="picture__input_perfil" tabindex="0">
                    <span class="picture__image_perfil"></span>
                  </label>
                  <input type="file" name="picture__input_perfil" id="picture__input_perfil" />
                </div>
              </div>

              <div class="content-profile-item">
                <label>Nome:</label>
                <input class="input-nome" type="text" name="nome" placeholder="Nome" />
              </div>

              <div class="content-profile-item">
                <label>Nome de Usuário</label>
                <div class="content-user-arroba">
                  <span>@</span>
                  <input class="input-arroba" type="text" name="nomedeusuario" placeholder="Nome de Usuário" />
                </div>
              </div>

              <div class="content-profile-item">
                <label>Biografia</label>
                <textarea name="Biografia" class="input-biografia" rows="3" cols="3" maxlength="180"
                  id="input-biografia"></textarea>
              </div>

              <button type="submit" id="btnEditProfile">Salvar Alterações</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="http://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="ScriptGeral.js"></script>
    <script type="text/javascript" src="ScriptProfile.js"></script>
</body>

</html>