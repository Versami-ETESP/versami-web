<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
include 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Busca todos os posts do blog com informações do autor
$sql_posts = "SELECT b.idBlogPost, b.titulo, LEFT(b.conteudo, 150) AS resumo, b.dataPost, 
              b.imgPost, -- ADICIONA ESTA LINHA
              a.nome AS autor, a.arroba_usuario AS autor_arroba
              FROM tblBlogPost b
              JOIN tblAdmin a ON b.idAdmin = a.idAdmin
              ORDER BY b.dataPost DESC";    
$result_posts = sqlsrv_query($conn, $sql_posts);

// Verifica se foi selecionado um post específico
$post_selecionado = null;
if (isset($_GET['id'])) {
    $sql_post = "SELECT b.*, a.nome AS autor, a.arroba_usuario AS autor_arroba 
                 FROM tblBlogPost b
                 JOIN tblAdmin a ON b.idAdmin = a.idAdmin
                 WHERE b.idBlogPost = ?";
    $params = array($_GET['id']);
    $stmt = sqlsrv_query($conn, $sql_post, $params);

    if ($stmt) {
        $post_selecionado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Versami</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="test/style-blog-usuario.css">
</head>

<body>
    <div class="content">
        <div class="header-menu">
            <!-- Barra de navegação -->
            <div id="sidebar">
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
                            <i class="fa-solid fa-bell"></i> Notificações
                            <?php
                            // Contar notificações não lidas
                            $sql_count = "SELECT COUNT(*) as total FROM tblNotificacao 
                                WHERE idUsuario = ? AND visualizada = 0";
                            $stmt_count = sqlsrv_query($conn, $sql_count, array($_SESSION["usuario_id"]));
                            if ($stmt_count) {
                                $count = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC);
                                if ($count && $count['total'] > 0): ?>
                                    <span class="notification-badge"><?= $count['total'] ?></span>
                                <?php endif;
                            }
                            ?>
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
                    <div class="buttonTema">
                        <button class="logout-btn" onclick="trocarTema()"><i class="fa-solid fa-palette"></i></button>
                    </div>
                    <div class="buttonOff">
                        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
                    </div>
                </div>
            </div>
        </div>
            <!-- Conteúdo -->
        <div class="principal-content">
            <div class="blog-container">
                <div class="posts-list">
                    <h2>Últimas Notícias</h2>
                    <?php if (sqlsrv_has_rows($result_posts)): ?>
                        <?php while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)): ?>
                            <div class="post-preview" onclick="window.location='?id=<?= $post['idBlogPost'] ?>'">
                                <img src="data:image/jpeg;base64,<?=
                                    isset($post['imgPost']) ? base64_encode($post['imgPost']) :
                                    base64_encode(file_get_contents('Assets/blog_placeholder.png')) ?>"
                                    alt="<?= nl2br(htmlspecialchars(iconv('ISO-8859-1', 'UTF-8//IGNORE', $post['titulo']))) ?>">
                                <div>
                                    <h3><?= nl2br(htmlspecialchars(iconv('ISO-8859-1', 'UTF-8//IGNORE', $post['titulo']))) ?></h3>
                                    <p><?= nl2br(htmlspecialchars(iconv('ISO-8859-1', 'UTF-8//IGNORE', $post['resumo']))) ?>...</p>
                                    <small><?= $post['dataPost']->format('d/m/Y') ?> • Por
                                        <?= htmlspecialchars($post['autor']) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-book-open"></i>
                            <p>Nenhum post encontrado</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="post-full">
                    <?php if ($post_selecionado): ?>
                        <h1><?= nl2br(htmlspecialchars(iconv('ISO-8859-1', 'UTF-8//IGNORE', $post_selecionado['titulo']))) ?></h1>
                        <p class="meta">
                            Publicado em <?= $post_selecionado['dataPost']->format('d/m/Y \à\s H:i') ?>
                            por <?= htmlspecialchars($post_selecionado['autor']) ?>
                        </p>

                        <?php if (!empty($post_selecionado['imgPost'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($post_selecionado['imgPost']) ?>"
                                alt="<?= htmlspecialchars(mb_convert_encoding($post_selecionado['titulo'], 'UTF-8', 'auto')) ?>">
                        <?php else: ?>
                            <img src="Assets/blog_placeholder.png" alt="<?= htmlspecialchars($post_selecionado['titulo']) ?>">
                        <?php endif; ?>

                        <div class="contentBlog ">
                             <?= nl2br(transformURLsIntoLinks(mb_convert_encoding($post_selecionado['conteudo'], 'UTF-8', 'ISO-8859-1'))) ?>
                        </div>

                        <div class="author">
                            <div class="author-info">
                                <h4><?= htmlspecialchars($post_selecionado['autor']) ?></h4>
                                <p>@<?= htmlspecialchars($post_selecionado['autor_arroba']) ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-newspaper"></i>
                            <h2>Selecione uma notícia</h2>
                            <p>Clique em uma das notícias ao lado para visualizar o conteúdo completo.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup para criar review para anexar o livro -->
    <div class="popup-overlay" id="reviewPopupOverlay">
        <div class="popup">
            <div class="btn-top-content">
                <div class="btn-close-content">
                    <button class="btn-close"><i class="fa-solid fa-x"></i></button>
                </div>
                <h2>Criar Review</h2>
            </div>
            <form method="POST" id="postForm">
                <textarea name="conteudo" maxlength="380" id="review-content" rows="7" cols="7"
                    placeholder="Compartilhe seus pensamentos..."></textarea>

                <!-- Área para mostrar o livro selecionado -->
                <div id="selectedBookContainer">
                    <div id="selectedBookCover">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div id="selectedBookInfo"></div>
                    <button type="button" id="removeBookBtn">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                    <input type="hidden" name="idLivro" id="selectedBookId">
                </div>

                <div class="icons-content">
                    <div class="icons-right-content">
                        <input class="btn-submit" type="submit" id="publicarPost" value="Postar">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup de seleção de livros -->
    <div class="popup-overlay" id="bookSelectionPopup">
        <div class="popup">
            <div class="popup-header">
                <h2>Selecione um Livro</h2>
                <button class="btn-close" onclick="closeBookSelection()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="popup-body">
                <input type="text" id="bookSearch" placeholder="Pesquisar por título, autor ou gênero...">
                <div id="booksList"></div>
            </div>
        </div>
    </div>

    <!-- Scripts de JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>