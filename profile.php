<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

// Função para converter varbinary em base64 (já deve estar em config.php)
// function binaryToBase64($binaryData)
// {
//     if ($binaryData === null || empty($binaryData)) {
//         return '';
//     }
//     return 'data:image/jpeg;base64,' . base64_encode($binaryData);
// }

// Busca dados do usuário logado
$sql_usuario = "SELECT
    u.*,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = u.idUsuario) as seguidores,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguidor = u.idUsuario) as seguindo,
    (SELECT COUNT(*) FROM tblPublicacao WHERE idUsuario = u.idUsuario) as total_posts
FROM tblUsuario u
WHERE u.idUsuario = ?";
$params_usuario = array($usuario_id);
$result_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);
$usuario = sqlsrv_fetch_array($result_usuario, SQLSRV_FETCH_ASSOC);

// Converter imagens para base64
$fotoUsuarioBase64 = displayImage($usuario['fotoUsuario']);
$fotoCapaBase64 = displayImage($usuario['fotoCapa']);

// Contagem de seguidores/seguindo (já incluída na query $sql_usuario)
$contadores = [
    'seguidores' => $usuario['seguidores'],
    'seguindo' => $usuario['seguindo'],
    'total_posts' => $usuario['total_posts'] // Adicionado
];

// Busca posts do usuário (com tratamento de data, livro anexado, likes e comentários)
$sql_posts = "SELECT
    p.idPublicacao, p.conteudo, p.dataPublic,
    l.idLivro, l.nomeLivro, l.imgCapa, l.descLivro,
    a.nomeAutor,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as total_likes,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu,
    (SELECT COUNT(*) FROM tblComentario WHERE idPublicacao = p.idPublicacao) as total_comentarios
FROM tblPublicacao p
LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
WHERE p.idUsuario = ?
ORDER BY p.dataPublic DESC";

$params_posts = array($_SESSION["usuario_id"], $usuario_id);
$result_posts = sqlsrv_query($conn, $sql_posts, $params_posts);

// Busca livros favoritos do usuário
$sql_favoritos = "SELECT
    l.idLivro, l.nomeLivro, l.imgCapa, l.descLivro,
    a.nomeAutor as autor
FROM tblLivrosFavoritos f
JOIN tblLivro l ON f.idLivro = l.idLivro
JOIN tblAutor a ON l.idAutor = a.idAutor
WHERE f.idUsuario = ?";

$params_favoritos = array($usuario_id);
$result_favoritos = sqlsrv_query($conn, $sql_favoritos, $params_favoritos);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="Profile/CSS/styleProfile.css">
</head>

<body>
    <div class="header-menu">
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
                    <li onclick="location.href='blog_usuarios.php'">
                        <i class="fa-solid fa-newspaper"></i> Blog
                    </li>
                    <li onclick="location.href='notificacao.php'">
                        <i class="fa-solid fa-bell"></i> Notificações
                        <?php
                        $total_notificacoes = contarNotificacoesNaoLidas($conn, $_SESSION["usuario_id"]);
                        if ($total_notificacoes > 0): ?>
                            <span class="notification-badge"><?= $total_notificacoes ?></span>
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

        <div class="content">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="<?= $fotoCapaBase64 ?: 'Assets/padraoCapa.png' ?>" class="cover-photo"
                        alt="Capa do perfil">
                </div>

                <div class="profile-main-info">
                    <div class="profile-photo-container">
                        <img src="<?= $fotoUsuarioBase64 ?: 'Assets/padrao.png' ?>" class="profile-photo"
                            alt="Foto do perfil">
                    </div>
                    <div class="profile-text-info">
                        <h1 class="profile-name"><?= htmlspecialchars($usuario['nome'] ?? '') ?></h1>
                        <p class="profile-username">@<?= htmlspecialchars($usuario['arroba_usuario'] ?? '') ?></p>
                    </div>
                    <button class="edit-profile-btn">Editar Perfil</button>
                </div>

                <p class="profile-bio"><?= htmlspecialchars($usuario['bio_usuario'] ?? 'Nenhuma biografia definida.') ?>
                </p>

                <div class="profile-stats-container">
                    <div class="profile-stat">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Entrou em <strong>2025</strong></span>
                    </div>
                    <div class="profile-stat">
                        <strong><?= $contadores['seguindo'] ?? 0 ?></strong> Seguindo
                    </div>
                    <div class="profile-stat">
                        <strong><?= $contadores['seguidores'] ?? 0 ?></strong> Seguidores
                    </div>
                    <div class="profile-stat">
                        <strong><?= $contadores['total_posts'] ?? 0 ?></strong> Reviews
                    </div>
                </div>

                <div class="profile-tabs-container">
                    <div class="profile-tabs">
                        <div class="profile-tab active" onclick="showProfileTab('posts')">Reviews</div>
                        <div class="profile-tab" onclick="showProfileTab('favorites')">Livros Favoritos</div>
                    </div>
                </div>

                <div id="profile-posts-section" class="profile-posts-section active">
                    <?php if (sqlsrv_has_rows($result_posts)): ?>
                        <?php while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)): ?>
                            <div class="post-card"
                                onclick="window.location.href='post_details.php?id=<?= $post['idPublicacao'] ?>'">
                                <div class="post-header">
                                    <img src="<?= $fotoUsuarioBase64 ?>" alt="Foto do usuário" class="post-user-avatar">
                                    <div class="post-user-info">
                                        <h3><?= htmlspecialchars($usuario['nome']) ?></h3>
                                        <p>@<?= htmlspecialchars($usuario['arroba_usuario']) ?></p>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <?= htmlspecialchars($post['conteudo'] ?? 'Post sem texto') ?>
                                </div>
                                <?php if (!empty($post['idLivro'])): ?>
                                    <div class="attached-book-profile">
                                        <?php if (!empty($post['imgCapa'])): ?>
                                            <img src="data:image/jpeg;base64,<?= base64_encode($post['imgCapa']) ?>"
                                                alt="Capa do livro">
                                        <?php else: ?>
                                            <div class="no-cover">
                                                <i class="fa-solid fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="book-info">
                                            <div class="book-title"><?= htmlspecialchars($post['nomeLivro']) ?></div>
                                            <div class="book-author"><?= htmlspecialchars($post['nomeAutor']) ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="post-actions">
                                    <button class="post-action-btn <?= ($post['usuario_curtiu'] > 0) ? 'liked' : '' ?>"
                                        onclick="event.stopPropagation(); curtir(<?= $post['idPublicacao'] ?>, this);">
                                        <i class="<?= ($post['usuario_curtiu'] > 0) ? 'fas' : 'far' ?> fa-heart"></i>
                                        <span><?= $post['total_likes'] ?></span>
                                    </button>
                                    <button class="post-action-btn"
                                        onclick="event.stopPropagation(); window.location.href='post_details.php?id=<?= $post['idPublicacao'] ?>';">
                                        <i class="far fa-comment"></i>
                                        <span><?= $post['total_comentarios'] ?></span>
                                    </button>
                                </div>
                                <?php
                                $sql_comentarios = "SELECT TOP 2 C.comentario, U.nome, U.arroba_usuario FROM tblComentario C JOIN tblUsuario U ON C.idUsuario = U.idUsuario WHERE C.idPublicacao = ? ORDER BY C.data_coment DESC";
                                $params_comentarios = array($post['idPublicacao']);
                                $comentarios = sqlsrv_query($conn, $sql_comentarios, $params_comentarios);
                                if (sqlsrv_has_rows($comentarios)):
                                    ?>
                                    <div class="comments-list">
                                        <?php while ($comentario = sqlsrv_fetch_array($comentarios, SQLSRV_FETCH_ASSOC)): ?>
                                            <div class="comment-item">
                                                <img src="<?= displayImage($usuario['fotoUsuario']) ?>" alt="Foto de perfil"
                                                    class="comment-user-avatar">
                                                <span class="comment-text"><strong><?= htmlspecialchars($comentario['nome']) ?></strong>
                                                    <?= htmlspecialchars($comentario['comentario']) ?></span>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-posts">
                            <p>Nenhuma review publicada ainda.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="profile-favorites-section" class="favorite-books-section">
                    <?php if (sqlsrv_has_rows($result_favoritos)): ?>
                        <div class="favorite-books-grid">
                            <?php while ($livro = sqlsrv_fetch_array($result_favoritos, SQLSRV_FETCH_ASSOC)):
                                // Supondo que 'favoritado' na consulta de favoritos indica se o usuário logado favoritou este livro
                                $isFavoritedByCurrentUser = $livro['favoritado'] ?? 0;
                                ?>
                                <div class="book-item">
                                    <div class="book-cover-container">
                                        <?php if (!empty($livro['imgCapa'])): ?>
                                            <img src="data:image/jpeg;base64,<?= base64_encode($livro['imgCapa']) ?>"
                                                alt="Capa do livro" class="book-cover">
                                        <?php else: ?>
                                            <div class="no-cover">
                                                <i class="fa-solid fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="book-info">
                                        <h3 class="book-title"><?= htmlspecialchars($livro['nomeLivro']) ?></h3>
                                        <p class="book-author"><?= htmlspecialchars($livro['autor']) ?></p>
                                        <div class="book-stats">
                                            <span class="book-favorites">
                                                <i class="fa-solid fa-heart"></i>
                                                0 </span>
                                            <span
                                                class="book-genre"><?= htmlspecialchars($livro['genero'] ?? 'Gênero') ?></span>
                                        </div>
                                        <div class="book-actions">
                                            <a href="livro.php?id=<?= $livro['idLivro'] ?>" class="view-btn">
                                                Ver detalhes
                                            </a>
                                            <button class="favorite-btn <?= $isFavoritedByCurrentUser ? 'favorited' : '' ?>"
                                                data-book-id="<?= $livro['idLivro'] ?>"
                                                onclick="toggleFavorite(this, <?= $livro['idLivro'] ?>)">
                                                <i class="<?= $isFavoritedByCurrentUser ? 'fas' : 'far' ?> fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-posts">
                            <p>Nenhum livro favoritado ainda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        function showProfileTab(tabName) {
            // Remove 'active' de todas as abas e seções de conteúdo
            document.querySelectorAll('.profile-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.profile-posts-section, .favorite-books-section').forEach(section => section.classList.remove('active'));

            // Adiciona 'active' à aba clicada e à seção de conteúdo correspondente
            if (tabName === 'posts') {
                document.querySelector('.profile-tabs .profile-tab:nth-child(1)').classList.add('active');
                document.getElementById('profile-posts-section').classList.add('active');
            } else if (tabName === 'favorites') {
                document.querySelector('.profile-tabs .profile-tab:nth-child(2)').classList.add('active');
                document.getElementById('profile-favorites-section').classList.add('active');
            }
        }

        // Exibe a seção de reviews por padrão ao carregar a página
        document.addEventListener('DOMContentLoaded', () => {
            showProfileTab('posts');
        });

        // Popup para criar review (seção existente)
        function abrirModalPopUp() {
            document.getElementById('reviewPopupOverlay').style.display = 'flex';
        }

        document.querySelector('.popup-overlay .btn-close').addEventListener('click', function () {
            document.getElementById('reviewPopupOverlay').style.display = 'none';
        });

        document.getElementById('reviewPopupOverlay').addEventListener('click', function (event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
</body>

</html>