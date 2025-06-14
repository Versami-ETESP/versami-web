<?php
session_start();
include '../config.php'; // Inclui o arquivo de configuração

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}

$perfil_id = $_GET["id"] ?? null;

if (!$perfil_id) {
    die("Usuário não encontrado.");
}

// Converte binary para base64 (essa função já deve estar em config.php)
// function displayImage($binaryData) { ... }

// Busca dados do perfil do usuário que está sendo visualizado
$sql_usuario = "SELECT
    u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario, u.fotoCapa, u.bio_usuario, u.data_nasc,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = u.idUsuario) as total_seguidores,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguidor = u.idUsuario) as total_seguindo,
    (SELECT COUNT(*) FROM tblPublicacao WHERE idUsuario = u.idUsuario) as total_posts_reviews
FROM tblUsuario u
WHERE u.idUsuario = ?";
$params_usuario = array($perfil_id);
$result_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);

if ($result_usuario === false) {
    die("Erro ao buscar dados do usuário: " . print_r(sqlsrv_errors(), true));
}

$usuario = sqlsrv_fetch_array($result_usuario, SQLSRV_FETCH_ASSOC);
if (!$usuario) {
    die("Erro ao carregar perfil: Usuário não encontrado.");
}

// Convertendo imagens para base64
$fotoUsuarioBase64 = displayImage($usuario['fotoUsuario']);
$fotoCapaBase64 = displayImage($usuario['fotoCapa']);

// Verifica se o usuário logado segue o perfil que está sendo visualizado
$seguindo_perfil_atual = 0;
if ($_SESSION["usuario_id"] != $perfil_id) { // Só verifica se não é o próprio perfil
    $sql_check_seguindo = "SELECT COUNT(*) AS is_following FROM tblSeguidores
                           WHERE idSeguidor = ? AND idSeguido = ?";
    $params_check_seguindo = array($_SESSION["usuario_id"], $perfil_id);
    $result_check_seguindo = sqlsrv_query($conn, $sql_check_seguindo, $params_check_seguindo);
    if ($result_check_seguindo) {
        $row_check_seguindo = sqlsrv_fetch_array($result_check_seguindo, SQLSRV_FETCH_ASSOC);
        $seguindo_perfil_atual = $row_check_seguindo ? $row_check_seguindo['is_following'] : 0;
    }
}


// Busca posts (reviews) do perfil que está sendo visualizado
$sql_posts = "SELECT
    p.idPublicacao, p.conteudo, p.dataPublic,
    u.idUsuario as autor_id, u.nome as autor_nome, u.arroba_usuario as autor_arroba, u.fotoUsuario as autor_foto,
    l.idLivro, l.nomeLivro, l.imgCapa, l.descLivro,
    a.nomeAutor as nomeAutor,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as total_likes,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu,
    (SELECT COUNT(*) FROM tblComentario WHERE idPublicacao = p.idPublicacao) as total_comentarios
FROM tblPublicacao p
JOIN tblUsuario u ON p.idUsuario = u.idUsuario
LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
WHERE p.idUsuario = ?
ORDER BY p.dataPublic DESC";
$params_posts = array($_SESSION["usuario_id"], $perfil_id); // Primeiro ? é para usuario_curtiu, segundo para o autor do post
$result_posts = sqlsrv_query($conn, $sql_posts, $params_posts);

// Busca livros favoritos do perfil que está sendo visualizado
$sql_favoritos = "SELECT
    l.idLivro, l.nomeLivro, l.imgCapa, l.descLivro,
    a.nomeAutor as autor,
    g.nomeGenero as genero,
    (SELECT COUNT(*) FROM tblLivrosFavoritos WHERE idLivro = l.idLivro) as total_favoritos_livro,
    (SELECT 1 FROM tblLivrosFavoritos WHERE idLivro = l.idLivro AND idUsuario = ?) as favoritado_pelo_logado
FROM tblLivrosFavoritos f
JOIN tblLivro l ON f.idLivro = l.idLivro
LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
LEFT JOIN tblGenero g ON l.idGenero = g.idGenero
WHERE f.idUsuario = ?
ORDER BY l.nomeLivro";
$params_favoritos = array($_SESSION["usuario_id"], $perfil_id); // Primeiro ? para favoritado_pelo_logado, segundo para o usuário do perfil
$result_favoritos = sqlsrv_query($conn, $sql_favoritos, $params_favoritos);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($usuario['nome']) ?></title>
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../Assets/favicon.png" type="favicon" />
    <link rel="stylesheet" href="CSS/ProfileViewStyle.css">
    <link rel="stylesheet" href="../Profile/CSS/StyleProfile.css">
</head>

<body>
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
                    <li onclick="location.href='../Blog/BlogUsuarios.php'">
                        <i class="fa-solid fa-newspaper"></i> Blog
                    </li>
                    <li onclick="location.href='../Notificacao/Notificacao.php'">
                        <i class="fa-solid fa-bell"></i> Notificações
                        <?php
                        $total_notificacoes = contarNotificacoesNaoLidas($conn, $_SESSION["usuario_id"]);
                        if ($total_notificacoes > 0): ?>
                            <span class="notification-badge"><?= $total_notificacoes ?></span>
                        <?php endif; ?>
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
                    <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="<?= $fotoCapaBase64 ?: '../Assets/default_cover.png' ?>" class="cover-photo"
                        alt="Capa do perfil">
                </div>

                <div class="profile-main-info">
                    <div class="profile-photo-container">
                        <img src="<?= $fotoUsuarioBase64 ?: '../Assets/default_profile.png' ?>" class="profile-photo"
                            alt="Foto do perfil">
                    </div>
                    <div class="profile-text-info">
                        <div class="profile-text-user">
                            <h1 class="profile-name"><?= htmlspecialchars($usuario['nome'] ?? '') ?></h1>
                            <p class="profile-username">@<?= htmlspecialchars($usuario['arroba_usuario'] ?? '') ?></p>
                        </div>
                        <div class="profile-text-bio">
                            <p class="profile-bio">
                                <?= htmlspecialchars($usuario['bio_usuario'] ?? 'Nenhuma biografia definida.') ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($perfil_id == $_SESSION["usuario_id"]): ?>
                        <button class="edit-profile-btn"
                            onclick="window.location.href='../SetupProfile/SetupProfile.php'">Editar
                            Perfil</button>
                    <?php else: ?>
                        <button class="follow-btn <?= $seguindo_perfil_atual ? 'following' : '' ?>"
                            onclick="seguirUsuario(<?= $perfil_id ?>, this)">
                            <?= $seguindo_perfil_atual ? 'Deixar de seguir' : 'Seguir' ?>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="profile-stats-container">
                    <div class="profile-stat">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Entrou em <strong>2025</strong></span>
                    </div>
                    <div class="profile-stat">
                        <strong><?= $usuario['total_seguindo'] ?? 0 ?></strong> Seguindo
                    </div>
                    <div class="profile-stat">
                        <strong><?= $usuario['total_seguidores'] ?? 0 ?></strong> Seguidores
                    </div>
                    <div class="profile-stat">
                        <strong><?= $usuario['total_posts_reviews'] ?? 0 ?></strong> Reviews
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
                                onclick="window.location.href='../Post/PostDetails.php?id=<?= $post['idPublicacao'] ?>'">
                                <div class="post-header">
                                    <img src="<?= displayImage($post['autor_foto']) ?>" alt="Foto do usuário"
                                        class="post-user-avatar">
                                    <div class="post-user-info">
                                        <h3><?= htmlspecialchars($post['autor_nome']) ?></h3>
                                        <p>@<?= htmlspecialchars($post['autor_arroba']) ?></p>
                                    </div>
                                    <div class="post-action-buttons">
                                        <?php if ($post['autor_id'] == $_SESSION["usuario_id"]): ?>
                                            <div class="post-menu-item delete"
                                                onclick="showDeleteConfirmation(<?= $post['idPublicacao'] ?>); event.stopPropagation();">
                                                <i class="fas fa-trash"></i>
                                                <span>Excluir Review</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <?= transformURLsIntoLinks($post['conteudo'] ?? 'Post sem texto') ?>
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
                                            <?php if (!empty($post['descLivro'])): ?>
                                                <div class="book-description">
                                                    <p><?= htmlspecialchars(convertToUtf8($post['descLivro'])) ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
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
                                        onclick="event.stopPropagation(); window.location.href='../Post/PostDetails.php?id=<?= $post['idPublicacao'] ?>';">
                                        <i class="far fa-comment"></i>
                                        <span><?= $post['total_comentarios'] ?></span>
                                    </button>
                                </div>
                                <?php
                                // Busca os 2 comentários mais recentes para este post
                                $sql_comentarios_post = "SELECT TOP 2 C.comentario, U.nome, U.arroba_usuario, U.fotoUsuario FROM tblComentario C JOIN tblUsuario U ON C.idUsuario = U.idUsuario WHERE C.idPublicacao = ? ORDER BY C.data_coment DESC";
                                $params_comentarios_post = array($post['idPublicacao']);
                                $comentarios_post = sqlsrv_query($conn, $sql_comentarios_post, $params_comentarios_post);
                                if ($comentarios_post && sqlsrv_has_rows($comentarios_post)):
                                    ?>
                                    <div class="comments-list">
                                        <?php while ($comentario_item = sqlsrv_fetch_array($comentarios_post, SQLSRV_FETCH_ASSOC)): ?>
                                            <div class="comment-item">
                                                <img src="<?= displayImage($comentario_item['fotoUsuario']) ?>" alt="Foto de perfil"
                                                    class="comment-user-avatar">
                                                <span
                                                    class="comment-text"><strong><?= htmlspecialchars($comentario_item['nome']) ?></strong>
                                                    <?= htmlspecialchars($comentario_item['comentario']) ?></span>
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
                    <?php if ($result_favoritos && sqlsrv_has_rows($result_favoritos)): ?>
                        <div class="favorite-books-grid">
                            <?php while ($livro = sqlsrv_fetch_array($result_favoritos, SQLSRV_FETCH_ASSOC)): ?>
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
                                        <?php if (!empty($livro['descLivro'])): ?>
                                            <div class="book-description">
                                                <p><?= htmlspecialchars(convertToUtf8($livro['descLivro'])) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        <div class="book-stats">
                                            <span class="book-favorites">
                                                <i class="fa-solid fa-heart"></i>
                                                <?= $livro['total_favoritos_livro'] ?? 0 ?>
                                            </span>
                                            <span
                                                class="book-genre"><?= htmlspecialchars($livro['genero'] ?? 'Gênero') ?></span>
                                        </div>
                                        <div class="book-actions">
                                            <a href="Livro/Livro.php?id=<?= $livro['idLivro'] ?>" class="view-btn">
                                                Ver detalhes
                                            </a>
                                            <button
                                                class="favorite-btn <?= $livro['favoritado_pelo_logado'] ? 'favorited' : '' ?>"
                                                data-book-id="<?= $livro['idLivro'] ?>"
                                                onclick="toggleFavorite(this, <?= $livro['idLivro'] ?>)">
                                                <i class="<?= $livro['favoritado_pelo_logado'] ? 'fas' : 'far' ?> fa-heart"></i>
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

    <div id="toastNotification" class="toast-notification"></div>

    <button class="menu-btn" id="menuBtn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="overlay" id="overlay"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        function showProfileTab(tabName) {
            // Remove 'active' de todas as abas e seções de conteúdo
            document
                .querySelectorAll(".profile-tab")
                .forEach((tab) => tab.classList.remove("active"));
            document
                .querySelectorAll(".profile-posts-section, .favorite-books-section")
                .forEach((section) => section.classList.remove("active"));

            // Adiciona 'active' à aba clicada e à seção de conteúdo correspondente
            if (tabName === "posts") {
                document
                    .querySelector(".profile-tabs .profile-tab:nth-child(1)")
                    .classList.add("active");
                document.getElementById("profile-posts-section").classList.add("active");
            } else if (tabName === "favorites") {
                document
                    .querySelector(".profile-tabs .profile-tab:nth-child(2)")
                    .classList.add("active");
                document
                    .getElementById("profile-favorites-section")
                    .classList.add("active");
            }
        }

        // Exibe a seção de reviews por padrão ao carregar a página
        document.addEventListener("DOMContentLoaded", () => {
            showProfileTab("posts");
        });

        // Popup para criar review (seção existente)
        function abrirModalPopUp() {
            document.getElementById("reviewPopupOverlay").style.display = "flex";
        }

        document
            .querySelector(".popup-overlay .btn-close")
            .addEventListener("click", function () {
                document.getElementById("reviewPopupOverlay").style.display = "none";
            });

        document
            .getElementById("reviewPopupOverlay")
            .addEventListener("click", function (event) {
                if (event.target === this) {
                    this.style.display = "none";
                }
            });

    </script>
</body>

</html>