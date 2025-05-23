<?php
session_start();
include 'config.php';

// Busca todos os posts do blog
$sql_posts = "SELECT idBlogPost, titulo, LEFT(conteudo, 100) AS resumo, dataPost 
              FROM tblBlogPost 
              ORDER BY dataPost DESC";
$result_posts = sqlsrv_query($conn, $sql_posts);

// Verifica se foi selecionado um post específico
$post_selecionado = null;
if (isset($_GET['id'])) {
    $sql_post = "SELECT * FROM tblBlogPost WHERE idBlogPost = ?";
    $params = array($_GET['id']);
    $stmt = sqlsrv_query($conn, $sql_post, $params);
    $post_selecionado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Blog - Versami</title>
    <link rel="stylesheet" href="css/style-blog-usuario.css">
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
                        <li onclick="location.href='explorar.php'">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='blog_usuarios.php'" class="active">
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
                <div class="blog-container">
                    <div class="posts-list">
                        <h2>Últimas Notícias</h2>
                        <?php while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)): ?>
                            <div class="post-preview" onclick="window.location='?id=<?= $post['idBlogPost'] ?>'">
                                <img src="Assets/blog_placeholder.png" alt="<?= htmlspecialchars($post['titulo']) ?>">
                                <div>
                                    <h3><?= htmlspecialchars($post['titulo']) ?></h3>
                                    <p><?= htmlspecialchars($post['resumo']) ?>...</p>
                                    <small><?= $post['dataPost']->format('d/m/Y') ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="post-full">
                        <?php if ($post_selecionado): ?>
                            <h1><?= htmlspecialchars($post_selecionado['titulo']) ?></h1>
                            <p><em><?= $post_selecionado['dataPost']->format('d/m/Y H:i') ?></em></p>
                            <img src="Assets/blog_placeholder.png"
                                alt="<?= htmlspecialchars($post_selecionado['titulo']) ?>">
                            <div><?= nl2br(htmlspecialchars($post_selecionado['conteudo'])) ?></div>
                        <?php else: ?>
                            <h2>Selecione uma notícia para ler</h2>
                            <p>Clique em uma das notícias ao lado para visualizar o conteúdo completo.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/theme-switcher.js"></script>
    <script src="js/script.js"></script>
</body>

</html>