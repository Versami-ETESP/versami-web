<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
include '../config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Busca todos os posts do blog com informações do autor
$sql_posts = "SELECT b.idBlogPost, b.titulo, LEFT(b.conteudo, 150) AS resumo, b.dataPost, 
              b.imgPost,
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
    <link rel="stylesheet" href="CSS/BlogStyle.css">
</head>

<body>
    <div class="content">
        <div class="header-menu">
            <div id="sidebar">
                <div class="top-content-sidebar">
                    <img src="../Assets/logoVersamiBlue.png" alt="Versami" />
                    <ul>
                        <li onclick="location.href='../Feed/Feed.php'">
                            <i class="fa-solid fa-house"></i> Home
                        </li>
                        <li onclick="location.href='../Explorar/Explorar.php'">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='BlogUsuarios.php'" class="active">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='../Notificacao/Notificacao.php'">
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
                        <li onclick="location.href='../Profile/Profile.php'">
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
            <div class="blog-container">
                <div class="posts-list">
                    <h2>Últimas Notícias</h2>
                    <?php if (sqlsrv_has_rows($result_posts)): ?>
                        <?php while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)): ?>
                            <div class="post-preview" data-post-id="<?= $post['idBlogPost'] ?>" onclick="loadBlogPost(<?= $post['idBlogPost'] ?>)">
                                <img src="data:image/jpeg;base64,<?=
                                    isset($post['imgPost']) ? base64_encode($post['imgPost']) :
                                    base64_encode(file_get_contents('Assets/blog_placeholder.png')) ?>"
                                    alt="<?= nl2br(htmlspecialchars(convertToUtf8($post['titulo']))) ?>">
                                <div>
                                    <h3><?= nl2br(htmlspecialchars(convertToUtf8($post['titulo']))) ?></h3>
                                    <p><?= nl2br(htmlspecialchars(convertToUtf8($post['resumo']))) ?>...</p>
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
                        <h1><?= nl2br(htmlspecialchars(convertToUtf8($post_selecionado['titulo']))) ?></h1>
                        <p class="meta">
                            Publicado em <?= $post_selecionado['dataPost']->format('d/m/Y \à\s H:i') ?>
                            por <?= htmlspecialchars($post_selecionado['autor']) ?>
                        </p>

                        <?php if (!empty($post_selecionado['imgPost'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($post_selecionado['imgPost']) ?>"
                                alt="<?= htmlspecialchars(convertToUtf8($post_selecionado['titulo'])) ?>">
                        <?php else: ?>
                            <img src="Assets/blog_placeholder.png" alt="<?= htmlspecialchars(convertToUtf8($post_selecionado['titulo'])) ?>">
                        <?php endif; ?>

                        <div class="contentBlog ">
                             <?= nl2br(transformURLsIntoLinks(convertToUtf8($post_selecionado['conteudo']))) ?>
                        </div>

                        <div class="author-blog">
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
    <div id="toastNotification" class="toast-notification"></div> <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
    function loadBlogPost(postId) {
        // Show a loading indicator
        $('.post-full').html('<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><h2>Carregando Notícia...</h2><p>Por favor, aguarde.</p></div>');

        $.ajax({
            url: 'get_blog_post_content.php', // CORRIGIDO: Removido 'Blog/'
            method: 'GET',
            data: { id: postId },
            success: function(response) {
                $('.post-full').html(response);
                
                // Update URL using history.pushState
                const newUrl = window.location.pathname + '?id=' + postId;
                history.pushState({ id: postId }, '', newUrl);

                // Highlight the selected post in the list
                $('.post-preview').removeClass('active-post');
                $(`.post-preview[data-post-id="${postId}"]`).addClass('active-post');

                // Ensure scroll to top of content if needed, but not necessarily of the page
                // This targets the .post-full div itself.
                $('.post-full').scrollTop(0);

            },
            error: function(xhr, status, error) {
                console.error("Erro ao carregar post:", status, error, xhr.responseText);
                $('.post-full').html('<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><h2>Erro ao carregar notícia</h2><p>Não foi possível carregar o conteúdo da notícia. Tente novamente.</p></div>');
            }
        });
    }

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const postId = urlParams.get('id');
        
        // If an ID is present in the URL on initial page load, ensure the correct post is highlighted.
        // The PHP already renders the content, so we just need to highlight the preview.
        if (postId) {
            $(`.post-preview[data-post-id="${postId}"]`).addClass('active-post');
        } else {
            // If no post is selected on initial load, default to showing the first post (optional)
            // Or keep the "Selecione uma notícia" message. The current PHP handles the latter.
            // If you want to load the first post automatically:
            // const firstPostId = $('.post-preview').first().data('post-id');
            // if (firstPostId) {
            //     loadBlogPost(firstPostId);
            // }
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(event) {
            const currentUrlParams = new URLSearchParams(window.location.search);
            const currentPostId = currentUrlParams.get('id');
            if (currentPostId) {
                loadBlogPost(currentPostId);
            } else {
                // If popping back to a state without an ID, revert to initial empty state or first post
                $('.post-full').html('<div class="empty-state"><i class="fa-solid fa-newspaper"></i><h2>Selecione uma notícia</h2><p>Clique em uma das notícias ao lado para visualizar o conteúdo completo.</p></div>');
                $('.post-preview').removeClass('active-post');
            }
        });
    });
    </script>
</body>

</html>