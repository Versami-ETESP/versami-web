<?php
session_start();
include '../config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}

// Postagem de review (mantida para consistência, mas não diretamente relacionada ao explorar)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["conteudo"])) {
    $conteudo = $_POST["conteudo"];
    $idLivro = isset($_POST["idLivro"]) && !empty($_POST["idLivro"]) ? $_POST["idLivro"] : null;

    $sql_insert = "INSERT INTO tblPublicacao (conteudo, idUsuario, idLivro, dataPublic)
                  VALUES (?, ?, ?, GETDATE())";
    $params_insert = array($conteudo, $_SESSION["usuario_id"], $idLivro);
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

    if ($stmt_insert) {
        header("Location: feed.php");
        exit;
    } else {
        die("Erro ao criar postagem: " . print_r(sqlsrv_errors(), true));
    }
}

// Processar busca se houver termo de pesquisa
$termo_busca = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Busca de usuários
$usuarios = [];
if (!empty($termo_busca)) {
    $termo_like = "%$termo_busca%";
    $sql_usuarios = "SELECT
        u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario,
        (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = u.idUsuario) as seguidores,
        (SELECT 1 FROM tblSeguidores WHERE idSeguidor = ? AND idSeguido = u.idUsuario) as segue
    FROM tblUsuario u
    WHERE u.nome LIKE ? OR u.arroba_usuario LIKE ?
    ORDER BY seguidores DESC";

    $params_usuarios = [$_SESSION["usuario_id"], $termo_like, $termo_like];
    $stmt_usuarios = sqlsrv_query($conn, $sql_usuarios, $params_usuarios);

    if ($stmt_usuarios) {
        while ($row = sqlsrv_fetch_array($stmt_usuarios, SQLSRV_FETCH_ASSOC)) {
            $usuarios[] = $row;
        }
    }
}

// Busca de livros
$livros = [];
if (!empty($termo_busca)) {
    $sql_livros = "SELECT
        l.idLivro, l.nomeLivro, l.imgCapa,
        a.nomeAutor as autor,
        g.nomeGenero as genero,
        (SELECT COUNT(*) FROM tblLivrosFavoritos WHERE idLivro = l.idLivro) as favoritos,
        (SELECT 1 FROM tblLivrosFavoritos WHERE idLivro = l.idLivro AND idUsuario = ?) as favoritado
    FROM tblLivro l
    JOIN tblAutor a ON l.idAutor = a.idAutor
    JOIN tblGenero g ON l.idGenero = g.idGenero
    WHERE l.nomeLivro LIKE ? OR a.nomeAutor LIKE ?
    ORDER BY favoritos DESC";

    $params_livros = [$_SESSION["usuario_id"], $termo_like, $termo_like];
    $stmt_livros = sqlsrv_query($conn, $sql_livros, $params_livros);

    if ($stmt_livros) {
        while ($row = sqlsrv_fetch_array($stmt_livros, SQLSRV_FETCH_ASSOC)) {
            $livros[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar - Versami</title>
    <link rel="stylesheet" href="CSS/ExplorarStyle.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                        <li onclick="location.href='Explorar.php'" class="active">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='../Blog/BlogUsuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='../Notificacao/Notificacao.php'">
                            <i class="fa-solid fa-bell"></i> Notificações
                            <?php
                            // Contar notificações não lidas
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
                        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="principal-content">
            <div class="user">
                <h2>Explorar</h2>
                <div class="buscarContent">
                    <form method="GET" action="Explorar.php" class="search-form">
                        <input type="text" class="inputBuscar" name="buscar" id="buscar_usuario"
                            placeholder="Pesquisar por nome, @usuário ou livro"
                            value="<?= htmlspecialchars($termo_busca) ?>" autofocus>
                    </form>

                    <div class="tabs-container">
                        <div class="tabs">
                            <div class="tab active" onclick="changeTab(0)">Pessoas</div>
                            <div class="tab" onclick="changeTab(1)">Livros</div>
                        </div>
                        <div class="content-container">
                            <div class="contentPosts active">
                                <div class="containerContent">
                                    <?php if (!empty($termo_busca)): ?>
                                        <?php if (!empty($usuarios)): ?>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <div class="userCard" onclick="window.location.href='../ProfileView/ProfileView.php?id=<?= $usuario['idUsuario'] ?>'">
                                                    <div class="userInfo">
                                                        <img src="<?= !empty($usuario['fotoUsuario']) ? 'data:image/jpeg;base64,' . base64_encode($usuario['fotoUsuario']) : 'Assets/default-profile.png' ?>"
                                                            alt="Foto do usuário" class="userAvatar">
                                                        <div class="userDetails">
                                                            <h3><?= htmlspecialchars($usuario['nome']) ?></h3>
                                                            <p>@<?= htmlspecialchars($usuario['arroba_usuario']) ?></p>
                                                            <span><?= $usuario['seguidores'] ?> seguidores</span>
                                                        </div>
                                                    </div>
                                                    <?php if ($usuario['idUsuario'] != $_SESSION["usuario_id"]): ?>
                                                        <button class="followBtn <?= $usuario['segue'] ? 'following' : '' ?>"
                                                            onclick="event.stopPropagation(); seguirUsuario(<?= $usuario['idUsuario'] ?>, this);">
                                                            <i class="fas fa-<?= $usuario['segue'] ? 'user-minus' : 'user-plus' ?>"></i>
                                                            <span class="button-text"><?= $usuario['segue'] ? 'Deixar de seguir' : 'Seguir' ?></span>
                                                        </button>
                                                    <?php else: ?>
                                                        <div class="followBtn-placeholder"></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="no-results search-instructions">
                                                <i class="fa-solid fa-user-slash"></i>
                                                <p>Nenhum usuário encontrado</p>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="search-instructions">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <p>Digite um termo para buscar usuários ou livros</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="contentPosts">
                                <div class="containerContent">
                                    <?php if (!empty($termo_busca)): ?>
                                        <?php if (!empty($livros)): ?>
                                            <div class="books-grid">
                                                <?php foreach ($livros as $livro): ?>
                                                    <div class="book-card">
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
                                                                    <?= $livro['favoritos'] ?>
                                                                </span>
                                                                <span
                                                                    class="book-genre"><?= htmlspecialchars($livro['genero']) ?></span>
                                                            </div>
                                                            <div class="book-actions">
                                                                <button class="view-btn"
                                                                    onclick="window.location.href='../Livro/Livro.php?id=<?= $livro['idLivro'] ?>'">
                                                                    Ver detalhes
                                                                </button>
                                                                <button
                                                                    class="favorite-btn <?= $livro['favoritado'] ? 'favorited' : '' ?>"
                                                                    data-book-id="<?= $livro['idLivro'] ?>"
                                                                    onclick="toggleFavorite(this, <?= $livro['idLivro'] ?>)">
                                                                    <i
                                                                        class="<?= $livro['favoritado'] ? 'fas' : 'far' ?> fa-heart"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-results search-instructions">
                                                <i class="fa-solid fa-book-skull"></i>
                                                <p>Nenhum livro encontrado</p>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="search-instructions">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <p>Digite um termo para buscar usuários ou livros</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        function toggleFavorite(button, bookId) {
            const isFavorited = button.classList.contains('favorited');
            const icon = button.querySelector('i');
            const favoriteCount = button.closest('.book-card').querySelector('.book-favorites');

            // Animação
            button.classList.toggle('favorited');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');

            if (isFavorited) {
                // Efeito de desfavoritar
                button.style.backgroundColor = '';
                icon.style.color = '';
            } else {
                // Efeito de favoritar
                button.style.backgroundColor = '#ffebee';
                icon.style.color = '#e0245e';

                // Efeito de pulso
                button.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    button.style.transform = 'scale(1)';
                }, 300);
            }

            // Atualiza contador visualmente
            const currentCount = parseInt(favoriteCount.textContent.trim());
            favoriteCount.textContent = isFavorited ? (currentCount - 1) : (currentCount + 1);

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
                    favoriteCount.textContent = isFavorited ? (currentCount) : (currentCount);
                }
            });
        }
    </script>
</body>

</html>