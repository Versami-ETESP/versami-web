<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
include '../config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../Login/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ../Explorar/Explorar.php");
    exit;
}

$livro_id = $_GET['id'];
$usuario_id = $_SESSION["usuario_id"];

// Busca detalhes do livro
$sql_livro = "SELECT 
    l.*, 
    a.nomeAutor, 
    a.descAutor,
    g.nomeGenero,
    (SELECT COUNT(*) FROM tblLivrosFavoritos WHERE idLivro = l.idLivro) as total_favoritos,
    (SELECT 1 FROM tblLivrosFavoritos WHERE idLivro = l.idLivro AND idUsuario = ?) as favoritado
FROM tblLivro l
JOIN tblAutor a ON l.idAutor = a.idAutor
JOIN tblGenero g ON l.idGenero = g.idGenero
WHERE l.idLivro = ?";

$params_livro = array($usuario_id, $livro_id);
$stmt_livro = sqlsrv_query($conn, $sql_livro, $params_livro);

if (!$stmt_livro || sqlsrv_has_rows($stmt_livro) === false) {
    header("Location: ../Explorar/Explorar.php");
    exit;
}

$livro = sqlsrv_fetch_array($stmt_livro, SQLSRV_FETCH_ASSOC);

// Busca usuários que favoritaram
$sql_favoritos = "SELECT 
    u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario
FROM tblLivrosFavoritos f
JOIN tblUsuario u ON f.idUsuario = u.idUsuario
WHERE f.idLivro = ?
ORDER BY u.nome";

$params_favoritos = array($livro_id);
$result_favoritos = sqlsrv_query($conn, $sql_favoritos, $params_favoritos);

// Busca reviews relacionadas ao livro
$sql_reviews = "SELECT 
    p.idPublicacao, p.conteudo, p.dataPublic,
    u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as likes,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu,
    (SELECT COUNT(*) FROM tblComentario WHERE idPublicacao = p.idPublicacao) as total_comentarios
FROM tblPublicacao p
JOIN tblUsuario u ON p.idUsuario = u.idUsuario
WHERE p.idLivro = ?
ORDER BY p.dataPublic DESC";

$params_reviews = array($usuario_id, $livro_id);
$result_reviews = sqlsrv_query($conn, $sql_reviews, $params_reviews);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($livro['nomeLivro']) ?> - Versami</title>
    <link rel="stylesheet" href="CSS/StyleLivro.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../Assets/favicon.png" type="favicon" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="content">
        <div class="header-menu">
            <!-- Barra de navegação -->
            <div id="sidebar">
                <div class="top-content-sidebar">
                    <img src="../Assets/logoVersamiBlue.png" alt="Versami" />
                    <ul>
                        <li onclick="location.href='../Feed/Feed.php'">
                            <i class="fa-solid fa-house"></i> Home
                        </li>
                        <li onclick="location.href='../Explorar/Explorar.php'" class="active">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='../Blog/BlogUsuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='../Notificacao/Notificacao.php'">
                            <i class="fa-solid fa-bell"></i> Notificações
                            <?php
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
                    <div class="buttonTema">
                        <button class="logout-btn" onclick="trocarTema()"><i class="fa-solid fa-palette"></i></button>
                    </div>
                    <div class="buttonOff">
                        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="principal-content">
            <div class="book-detail-container">
                <!-- Cabeçalho do livro -->
                <div class="book-header">
                    <div class="book-cover-container">
                        <?php if (!empty($livro['imgCapa'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($livro['imgCapa']) ?>" alt="Capa do livro"
                                class="book-cover">
                        <?php else: ?>
                            <div class="no-cover">
                                <i class="fa-solid fa-book"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="book-info">
                        <h1><?= htmlspecialchars($livro['nomeLivro']) ?></h1>
                        <p class="author">por <?= htmlspecialchars($livro['nomeAutor']) ?></p>
                        <p class="genre"><?= htmlspecialchars($livro['nomeGenero']) ?></p>

                        <div class="book-actions">
                            <button class="favorite-btn <?= $livro['favoritado'] ? 'favorited' : '' ?>"
                                onclick="toggleFavorite(<?= $livro['idLivro'] ?>)">
                                <i class="<?= $livro['favoritado'] ? 'fas' : 'far' ?> fa-heart"></i>
                                <span id="favorite-count"><?= $livro['total_favoritos'] ?></span> favoritos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Descrição do livro -->
                <div class="book-description">
                    <h2>Descrição</h2>
                    <p><?= htmlspecialchars(mb_convert_encoding($livro['descLivro'], 'UTF-8', 'ISO-8859-1')) ?></p>

                </div>

                <!-- Sobre o autor -->
                <div class="author-section">
                    <h2>Sobre o Autor</h2>
                    <p><?= htmlspecialchars(mb_convert_encoding($livro['descAutor'], 'UTF-8', 'ISO-8859-1')) ?></p>
                </div>

                <!-- Tabs para navegação -->
                <div class="tabs-container">
                    <div class="tabs">
                        <div class="tab active" onclick="changeTab(0)">Reviews</div>
                        <div class="tab" onclick="changeTab(1)">Favoritado por</div>
                    </div>
                    <div class="content-container">
                        <!-- Tab de Reviews -->
                        <div class="content-tab active">
                            <?php if (sqlsrv_has_rows($result_reviews)): ?>
                                <div class="reviews-list">
                                    <?php while ($review = sqlsrv_fetch_array($result_reviews, SQLSRV_FETCH_ASSOC)): ?>
                                        <div class="review-card">
                                            <div class="user-info">
                                                <img src="<?= displayImage($review['fotoUsuario']) ?>" alt="Foto do usuário"
                                                    class="user-avatar">
                                                <div class="user-details">
                                                    <h3><?= htmlspecialchars($review['nome']) ?></h3>
                                                    <p>@<?= htmlspecialchars($review['arroba_usuario']) ?></p>
                                                </div>
                                            </div>
                                            <div class="review-content">
                                                <p><?= nl2br(htmlspecialchars($review['conteudo'])) ?></p>
                                                <div class="review-meta">
                                                    <span class="date">
                                                        <?= $review['dataPublic']->format('d/m/Y H:i') ?>
                                                    </span>
                                                    <div class="review-actions">
                                                        <button class="like-btn <?= $review['usuario_curtiu'] ? 'liked' : '' ?>"
                                                            onclick="curtirReview(<?= $review['idPublicacao'] ?>, this)">
                                                            <i
                                                                class="<?= $review['usuario_curtiu'] ? 'fas' : 'far' ?> fa-heart"></i>
                                                            <span class="like-count"><?= $review['likes'] ?></span>
                                                        </button>
                                                        <a href="../Post/PostDetails.php?id=<?= $review['idPublicacao'] ?>"
                                                            class="comment-btn">
                                                            <i class="far fa-comment"></i>
                                                            <span><?= $review['total_comentarios'] ?></span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-content">
                                    <i class="fa-solid fa-book-open"></i>
                                    <p>Nenhuma review encontrada para este livro</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab de Favoritos -->
                        <div class="content-tab">
                            <?php if (sqlsrv_has_rows($result_favoritos)): ?>
                                <div class="favorites-grid">
                                    <?php while ($usuario = sqlsrv_fetch_array($result_favoritos, SQLSRV_FETCH_ASSOC)): ?>
                                        <div class="user-card">
                                            <img src="<?= displayImage($usuario['fotoUsuario']) ?>" alt="Foto do usuário"
                                                class="user-avatar">
                                            <div class="user-info">
                                                <h3><?= htmlspecialchars($usuario['nome']) ?></h3>
                                                <p>@<?= htmlspecialchars($usuario['arroba_usuario']) ?></p>
                                            </div>
                                            <?php if ($usuario['idUsuario'] != $_SESSION["usuario_id"]): ?>
                                                <button class="follow-btn"
                                                    onclick="seguirUsuario(<?= $usuario['idUsuario'] ?>, this)">
                                                    Seguir
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-content">
                                    <i class="fa-solid fa-heart"></i>
                                    <p>Ninguém favoritou este livro ainda</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup para criar review -->
    <div class="popup-overlay" id="reviewPopupOverlay">
        <div class="popup">
            <div class="btn-top-content">
                <div class="btn-close-content">
                    <button class="btn-close"><i class="fa-solid fa-x"></i></button>
                </div>
                <h2>Criar Review</h2>
            </div>
            <form method="POST" id="postForm" action="../Feed/Feed.php">
                <input type="hidden" name="idLivro" value="<?= $livro_id ?>">
                <textarea name="conteudo" maxlength="380" id="review-content" rows="7" cols="7"
                    placeholder="Compartilhe seus pensamentos sobre este livro..." required></textarea>
                <div class="icons-content">
                    <div class="icons-right-content">
                        <input class="btn-submit" type="submit" id="publicarPost" value="Postar Review">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <button class="menu-btn" id="menuBtn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="overlay" id="overlay"></div>

    <script>
        // Função para favoritar/desfavoritar livro
        function toggleFavorite(livroId) {
            const btn = document.querySelector('.favorite-btn');
            const isFavorited = btn.classList.contains('favorited');
            const countElement = document.getElementById('favorite-count');
            let currentCount = parseInt(countElement.textContent);

            // Atualiza visualmente
            btn.classList.toggle('favorited');
            btn.querySelector('i').classList.toggle('far');
            btn.querySelector('i').classList.toggle('fas');

            if (isFavorited) {
                countElement.textContent = currentCount - 1;
            } else {
                countElement.textContent = currentCount + 1;
                // Efeito de animação
                btn.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    btn.style.transform = 'scale(1)';
                }, 300);
            }

            // Chamada AJAX
            $.ajax({
                url: '../Functions/toggle_favorite.php',
                method: 'POST',
                data: {
                    book_id: livroId,
                    action: isFavorited ? 'remove' : 'add'
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    // Reverte em caso de erro
                    btn.classList.toggle('favorited');
                    btn.querySelector('i').classList.toggle('far');
                    btn.querySelector('i').classList.toggle('fas');
                    countElement.textContent = currentCount;
                }
            });
        }

        // Função para curtir reviews
        function curtirReview(reviewId, element) {
            const isLiked = element.classList.contains('liked');
            const icon = element.querySelector('i');
            const countElement = element.querySelector('.like-count');
            let currentCount = parseInt(countElement.textContent);

            // Atualiza visualmente
            element.classList.toggle('liked');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            countElement.textContent = isLiked ? currentCount - 1 : currentCount + 1;

            // Chamada AJAX
            $.ajax({
                url: '../Functions/curtir.php',
                method: 'POST',
                data: {
                    review_id: reviewId,
                    action: isLiked ? 'unlike' : 'like'
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    // Reverte em caso de erro
                    element.classList.toggle('liked');
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    countElement.textContent = currentCount;
                }
            });
        }

        // Função para alternar entre tabs
        function changeTab(index) {
            const tabs = document.querySelectorAll('.tab');
            const contentTabs = document.querySelectorAll('.content-tab');

            tabs.forEach((tab, i) => {
                if (i === index) {
                    tab.classList.add('active');
                    contentTabs[i].classList.add('active');
                } else {
                    tab.classList.remove('active');
                    contentTabs[i].classList.remove('active');
                }
            });
        }

        // Função para abrir popup de review
        function abrirModalPopUp() {
            document.getElementById('reviewPopupOverlay').style.display = 'flex';
        }

        // Fechar popup quando clicar no X
        document.querySelector('.btn-close').addEventListener('click', function () {
            document.getElementById('reviewPopupOverlay').style.display = 'none';
        });

        // Fechar popup quando clicar fora
        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('reviewPopupOverlay')) {
                document.getElementById('reviewPopupOverlay').style.display = 'none';
            }
        });
    </script>
</body>

</html>