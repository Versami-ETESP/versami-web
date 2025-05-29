<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}
$sql_busca = "SELECT 
    u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = u.idUsuario) as seguidores
FROM tblUsuario u
WHERE u.nome LIKE ? OR u.arroba_usuario LIKE ?";

// Busca de livros (nova estrutura)
$sql_livros = "SELECT 
    l.idLivro, l.nomeLivro, l.imgCapa,
    a.nomeAutor as autor,
    g.nomeGenero as genero
FROM tblLivro l
JOIN tblAutor a ON l.idAutor = a.idAutor
JOIN tblGenero g ON l.idGenero = g.idGenero
WHERE l.nomeLivro LIKE ? OR a.nomeAutor LIKE ?";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar - Versami</title>
    <link rel="stylesheet" href="style-explore.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
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
                        <li onclick="location.href='explorar.php'" class="active">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='blog_usuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='notificacao.php'">
                            <i class="fa-solid fa-bell"></i> Notificação
                        </li>
                        <li onclick="location.href='profile.php'">
                            <i class="fa-solid fa-user"></i> Perfil
                        </li>
                        <li onclick="location.href='criar_blog.php'">
                            <i class="fa-solid fa-print"></i> Criar Blog
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
                <h2>Explorar</h2>
                <div class="buscar-content">
                    <input type="text" class="input-buscar" id="buscar_usuario" placeholder="Pesquisar por nome ou @usuário" autofocus>
                    <div id="resultados"></div>
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
                <textarea name="conteudo" maxlength="380" id="review-content" rows="7" cols="7"
                    placeholder="Qual foi seu ultimo livro lído?"></textarea>
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
        <div id="postMessage"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/theme-switcher.js"></script>
    <script src="js/script.js"></script>
</body>

</html>