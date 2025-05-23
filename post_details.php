<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    header("Location: feed.php");
    exit;
}

// Busca o post completo com todas as informações necessárias
$sql_post = "SELECT 
    p.*, 
    u.nome, u.arroba_usuario, u.fotoUsuario,
    l.nomeLivro, l.imgCapa as livroCapa,
    a.nomeAutor as autor,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as total_likes,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu
FROM tblPublicacao p
JOIN tblUsuario u ON p.idUsuario = u.idUsuario
LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
WHERE p.idPublicacao = ?";

$params_post = array($_SESSION["usuario_id"], $_SESSION["usuario_id"], $post_id);
$stmt_post = sqlsrv_query($conn, $sql_post, $params_post);
$post = sqlsrv_fetch_array($stmt_post, SQLSRV_FETCH_ASSOC);

if (!$post) {
    header("Location: feed.php");
    exit;
}

// Busca comentários com informações adicionais
$sql_comentarios = "SELECT 
    c.*, 
    u.nome, 
    u.arroba_usuario, 
    u.fotoUsuario,
    (SELECT COUNT(*) FROM tblLikesPorComentario WHERE idComentario = c.idComentario) as total_likes,
    (SELECT COUNT(*) FROM tblLikesPorComentario WHERE idComentario = c.idComentario AND idUsuario = ?) as usuario_curtiu
FROM tblComentario c
JOIN tblUsuario u ON c.idUsuario = u.idUsuario
WHERE c.idPublicacao = ?
ORDER BY c.data_coment ASC";

$params_comentarios = array($_SESSION["usuario_id"], $post_id);
$comentarios = sqlsrv_query($conn, $sql_comentarios, $params_comentarios);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicação - Versami</title>
    <link rel="stylesheet" href="style_post_details.css">
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
                        <li onclick="location.href='blogusuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='notificacao.php'">
                            <i class="fa-solid fa-bell"></i> Notificações
                            <?php
                            // Contar notificações não lidas
                            $sql_count = "SELECT COUNT(*) as total FROM tblNotificacao 
                            WHERE idUsuario = ? AND visualizada = 0";
                            $stmt_count = sqlsrv_query($conn, $sql_count, array($_SESSION["usuario_id"]));
                            $count = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC);
                            if ($count && $count['total'] > 0): ?>
                                <span class="notification-badge"><?= $count['total'] ?></span>
                            <?php endif; ?>
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
                        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="principal-content">
            <div class="user">
                <div class="review-header">
                    <a href="feed.php" class="back-arrow" onclick="location.reload()">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2>Review</h2>
                </div>
                <div class="post-details-container">
                    <div class="post-header">
                        <img src="<?= htmlspecialchars($post['fotoUsuario']) ?>" alt="Foto do usuário"
                            class="user-avatar">
                        <div class="user-info">
                            <h2><?= htmlspecialchars($post['nome']) ?></h2>
                            <p>@<?= htmlspecialchars($post['arroba_usuario']) ?></p>
                        </div>
                        <?php if ($post['autor_id'] != $_SESSION["usuario_id"]): ?>
                            <div class="user-info-follow">
                                <button class="follow-btn <?= $post['seguindo'] ? 'following' : '' ?>"
                                    onclick="seguirUsuario(<?= $post['autor_id'] ?>, this)">
                                    <?= $post['seguindo'] ? 'Deixar de seguir' : 'Seguir' ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <?= transformURLsIntoLinks($post['conteudo']) ?>
                        <?php if (!empty($post['imagem'])): ?>
                            <img src="<?= htmlspecialchars($post['imagem']) ?>" class="post-image">
                        <?php endif; ?>
                    </div>
                    <div class="post-actions">
                        <button type="button" class="like-btn <?= ($post['usuario_curtiu'] > 0) ? 'liked' : '' ?>"
                            onclick="curtir(<?= $post['idPublicacao'] ?>, this)">
                            <i class="<?= ($post['usuario_curtiu'] > 0) ? 'fas' : 'far' ?> fa-heart"></i>
                            <span class="like-count"><?= $post['total_likes'] ?></span>
                        </button>
                        <span class="post-time">
                            <?= $post['dataPublic']->format('d/m/Y H:i') ?>
                        </span>
                    </div>
                    <div class="comments-section">
                        <h3>Comentários</h3>
                        <form method="POST" action="comentar.php" class="comment-form">
                            <input type="hidden" name="post_id" value="<?= $post['idPublicacao'] ?>">
                            <input type="text" name="comentario" placeholder="Escreva um comentário..."
                                class="comment-input" required>
                            <button type="submit" class="comment-button">Comentar</button>
                        </form>

                        <div class="comments-list">
                            <?php while ($comentario = sqlsrv_fetch_array($comentarios, SQLSRV_FETCH_ASSOC)): ?>
                                <div class="comment">
                                    <img src="<?= htmlspecialchars($comentario['fotoUsuario']) ?>" alt="Foto do usuário"
                                        class="comment-avatar">
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <strong><?= htmlspecialchars($comentario['nome']) ?></strong>
                                            <span>@<?= htmlspecialchars($comentario['arroba_usuario']) ?></span>
                                            <span class="comment-time">
                                                <?= $comentario['data_coment']->format('d/m/Y H:i') ?>
                                            </span>
                                        </div>
                                        <?= transformURLsIntoLinks($comentario['comentario']) ?>
                                        <div class="comment-actions">
                                            <button type="button"
                                                class="like-comment-btn <?= ($comentario['usuario_curtiu'] > 0) ? 'likedComment' : '' ?>"
                                                onclick="curtirComentario(<?= $comentario['idComentario'] ?>, this)">
                                                <i
                                                    class="<?= ($comentario['usuario_curtiu'] > 0) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                                <span class="like-comment-count"><?= $comentario['total_likes'] ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
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
    <script src="js/theme-switcher.js"></script>
    <script src="js/script.js"></script>
</body>

</html>