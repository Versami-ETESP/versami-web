<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}

$perfil_id = $_GET["id"] ?? null;

if (!$perfil_id) {
    die("Usuário não encontrado.");
}

// Busca dados do perfil com tratamento de erro
$sql_usuario = "SELECT nome, arroba_usuario, fotoUsuario, fotoCapa, bio_usuario
                FROM tblUsuario
                WHERE idUsuario = ?";
$params_usuario = array($perfil_id);
$result_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);

if ($result_usuario === false) {
    die(print_r(sqlsrv_errors(), true));
}

$usuario = sqlsrv_fetch_array($result_usuario, SQLSRV_FETCH_ASSOC);
if (!$usuario) {
    die("Erro ao carregar perfil.");
}

// Inicializa contadores
$contadores = [
    'total_seguidores' => 0,
    'total_seguindo' => 0
];

// Busca contagem de seguidores/seguindo
$sql_contadores = "SELECT
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = ?) AS total_seguidores,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguidor = ?) AS total_seguindo";
$params_contadores = array($perfil_id, $perfil_id);
$result_contadores = sqlsrv_query($conn, $sql_contadores, $params_contadores);

if ($result_contadores) {
    $contadores = sqlsrv_fetch_array($result_contadores, SQLSRV_FETCH_ASSOC) ?: $contadores;
}

// Verifica se o usuário logado segue o perfil
$seguindo = 0;
$sql_seguindo = "SELECT COUNT(*) AS seguindo FROM tblSeguidores
                 WHERE idSeguidor = ? AND idSeguido = ?";
$params_seguindo = array($_SESSION["usuario_id"], $perfil_id);
$result_seguindo = sqlsrv_query($conn, $sql_seguindo, $params_seguindo);

if ($result_seguindo) {
    $row = sqlsrv_fetch_array($result_seguindo, SQLSRV_FETCH_ASSOC);
    $seguindo = $row ? $row['seguindo'] : 0;
}

// Busca posts do perfil
$sql_posts = "SELECT P.idPublicacao as id, P.conteudo as texto, P.dataPublic as data_postagem,
                     U.idUsuario, U.fotoUsuario, U.nome, U.arroba_usuario,
                     (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = P.idPublicacao) AS curtidas,
                     (SELECT COUNT(*) FROM tblLikesPorPost WHERE idUsuario = ? AND idPublicacao = P.idPublicacao) AS curtiu
              FROM tblPublicacao P
              JOIN tblUsuario U ON P.idUsuario = U.idUsuario
              WHERE P.idUsuario = ?
              ORDER BY P.dataPublic DESC";
$params_posts = array($perfil_id, $perfil_id);
$result_posts = sqlsrv_query($conn, $sql_posts, $params_posts);

$has_posts = $result_posts && sqlsrv_has_rows($result_posts);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($usuario['nome']) ?></title>
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="profileview.css">
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

        <div class="content"> <div class="profile-container">
                <div class="profile-header">
                    <img src="<?= $fotoCapaBase64 ?: 'Assets/padraoCapa.png' ?>" class="cover-photo" alt="Capa do perfil">
                </div>

                <div class="profile-main-info">
                    <div class="profile-photo-container">
                        <img src="<?= $fotoUsuarioBase64 ?: 'Assets/padrao.png' ?>" class="profile-photo" alt="Foto do perfil">
                    </div>
                    <div class="profile-text-info">
                        <h1 class="profile-name"><?= htmlspecialchars($usuario['nome'] ?? '') ?></h1>
                        <p class="profile-username">@<?= htmlspecialchars($usuario['arroba_usuario'] ?? '') ?></p>
                    </div>
                    <button class="edit-profile-btn">Editar Perfil</button>
                </div>

                <p class="profile-bio"><?= htmlspecialchars($usuario['bio_usuario'] ?? 'Nenhuma biografia definida.') ?></p>

                <div class="profile-stats-container">
                    <div class="profile-stat">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Entrou em <strong><?= date('Y', strtotime($usuario['data_nasc'] ?? '2024-01-01')) ?></strong></span>
                    </div>
                    <div class="profile-stat">
                        <strong><?= $contadores['seguindo'] ?? 0 ?></strong> Seguindo
                    </div>
                    <div class="profile-stat">
                        <strong><?= $contadores['seguidores'] ?? 0 ?></strong> Seguidores
                    </div>
                    <div class="profile-stat">
                        <strong>[0]</strong> Leituras
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
                            <div class="post-card">
                                <div class="post-content">
                                    <?= htmlspecialchars($post['conteudo'] ?? 'Post sem texto') ?>
                                </div>
                                <div class="post-meta">
                                    <span>
                                        <?php
                                        if ($post['dataPublic'] !== null) {
                                            echo "Postado em: " . $post['dataPublic']->format('d/m/Y H:i');
                                        }
                                        ?>
                                    </span>
                                    <div class="likes">
                                        <i class="fas fa-heart"></i> <?= $post['likes'] ?? 0 ?>
                                    </div>
                                </div>
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
                                            <span class="book-genre"><?= htmlspecialchars($livro['genero'] ?? 'Gênero') ?></span>
                                        </div>
                                        <div class="book-actions">
                                            <a href="livro.php?id=<?= $livro['idLivro'] ?>" class="view-btn">
                                                Ver detalhes
                                            </a>
                                            <button
                                                class="favorite-btn <?= $isFavoritedByCurrentUser ? 'favorited' : '' ?>"
                                                data-book-id="<?= $livro['idLivro'] ?>"
                                                onclick="toggleFavorite(this, <?= $livro['idLivro'] ?>)">
                                                <i
                                                    class="<?= $isFavoritedByCurrentUser ? 'fas' : 'far' ?> fa-heart"></i>
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

        // Função para favoritar/desfavoritar livro (replicada de explorar.php)
        function toggleFavorite(button, bookId) {
            const isFavorited = button.classList.contains('favorited');
            const icon = button.querySelector('i');
            // A contagem de favoritos não é diretamente exibida aqui no card, então a atualização visual é apenas do botão
            // const favoriteCount = button.closest('.book-item').querySelector('.book-favorites'); // Se quiser atualizar a contagem real

            // Animação e atualização visual do botão
            button.classList.toggle('favorited');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');

            if (!isFavorited) { // Se favoritou
                button.style.backgroundColor = '#ffebee';
                icon.style.color = '#e0245e';
                button.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    button.style.transform = 'scale(1)';
                }, 300);
            } else { // Se desfavoritou
                button.style.backgroundColor = 'var(--light-gray)';
                icon.style.color = 'var(--dark-gray)';
            }


            // Chamada AJAX
            $.ajax({
                url: 'toggle_favorite.php',
                method: 'POST',
                data: {
                    book_id: bookId,
                    action: isFavorited ? 'remove' : 'add'
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    // Reverte visualmente em caso de erro
                    button.classList.toggle('favorited');
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    if (!isFavorited) { // Se era para favoritar e falhou
                        button.style.backgroundColor = 'var(--light-gray)';
                        icon.style.color = 'var(--dark-gray)';
                    } else { // Se era para desfavoritar e falhou
                        button.style.backgroundColor = '#ffebee';
                        icon.style.color = '#e0245e';
                    }
                }
            });
        }


        // Popup para criar review (seção existente)
        function abrirModalPopUp() {
            document.getElementById('reviewPopupOverlay').style.display = 'flex';
        }

        document.querySelector('.popup-overlay .btn-close').addEventListener('click', function() {
            document.getElementById('reviewPopupOverlay').style.display = 'none';
        });

        document.getElementById('reviewPopupOverlay').addEventListener('click', function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
</body>

</html>