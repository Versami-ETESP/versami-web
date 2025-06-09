    <?php
    session_start();
    header('Content-Type: text/html; charset=utf-8');
    include '../config.php';

    // Verificação de login
    if (!isset($_SESSION["usuario_id"])) {
        header("Location: .../login.php");
        exit;
    }

    // Postagem de conteúdo
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

    // Busca posts principais do mais recente ao mais antigo
    $sql_posts = "SELECT
        p.idPublicacao, p.conteudo, p.dataPublic,
        u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario,
        l.idLivro, l.nomeLivro, l.imgCapa, l.descLivro,
        a.nomeAutor as nomeAutor,
        (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as total_likes,
        (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu,
        (SELECT COUNT(*) FROM tblComentario WHERE idPublicacao = p.idPublicacao) as total_comentarios
    FROM tblPublicacao p
    JOIN tblUsuario u ON p.idUsuario = u.idUsuario
    LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
    LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
    ORDER BY p.dataPublic DESC";

    $params_posts = array($_SESSION["usuario_id"]);
    $result_posts = sqlsrv_query($conn, $sql_posts, $params_posts);

    // Verifica se houve erro na consulta
    if ($result_posts === false) {
        die("Erro ao buscar posts: " . print_r(sqlsrv_errors(), true));
    }

    // Busca posts de quem o usuário segue (para a aba "Seguindo")
    $sql_posts_seguindo = "SELECT
        p.idPublicacao, p.conteudo, p.dataPublic,
        u.idUsuario, u.nome, u.arroba_usuario, u.fotoUsuario,
        l.idLivro, l.nomeLivro, l.imgCapa, l.descLivro, -- Adicionado l.imgCapa
        a.nomeAutor as nomeAutor, -- Adicionado a.nomeAutor
        (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as total_likes,
        (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu,
        (SELECT COUNT(*) FROM tblComentario WHERE idPublicacao = p.idPublicacao) as total_comentarios
    FROM tblPublicacao p
    JOIN tblUsuario u ON p.idUsuario = u.idUsuario
    LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
    LEFT JOIN tblAutor a ON l.idAutor = a.idAutor -- Adicionado JOIN com tblAutor
    WHERE p.idUsuario IN (SELECT idSeguido FROM tblSeguidores WHERE idSeguidor = ?)
    ORDER BY p.dataPublic DESC";

    $params_posts_seguindo = array($_SESSION["usuario_id"], $_SESSION["usuario_id"]);
    $result_posts_seguindo = sqlsrv_query($conn, $sql_posts_seguindo, $params_posts_seguindo);

    // Verifica se houve erro na consulta
    if ($result_posts_seguindo === false) {
        die("Erro ao buscar posts de seguindo: " . print_r(sqlsrv_errors(), true));
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Para você - Versami</title>
        <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="CSS/FeedStyle.css">
    </head>

    <body>
        <h2>Para você</h2>
        <div class="content">
            <div class="header-menu">
                <div id="sidebar">
                    <div class="top-content-sidebar">
                        <img src="../Assets/logoVersamiBlue.png" alt="Versami" />
                        <ul>
                            <li onclick="location.href='Feed.php'" class="active">
                                <i class="fa-solid fa-house"></i> Home
                            </li>
                            <li onclick="location.href='../Explorar/Explorar.php'">
                                <i class="fa-solid fa-magnifying-glass"></i> Explore
                            </li>
                            <li onclick="location.href='../Blog/BlogUsuarios.php'">
                                <i class="fa-solid fa-newspaper"></i> Blog
                            </li>
                            <li onclick="location.href='../Notificacao/Notificacao.php'">
                                <div class="notification-icon-container">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                Notificações
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
            </div>
            <div id="principal-content">
                <div class="user">
                    <div class="posts" id="posts">
                        <div class="tabs-container">
                            <img src="../Assets/logoVersamiBlue.png" alt="Versami" />
                            <div class="tabs">
                                <div class="tab active" onclick="changeTab(0)">Para você</div>
                                <div class="tab" onclick="changeTab(1)">Seguindo</div>
                            </div>
                            <div class="content-container">
                                <div class="contentPosts active">
                                    <div class="containerContent">
                                        <?php
                                        // Verifica se há posts
                                        $has_posts = false;
                                        if ($result_posts) {
                                            while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)) {
                                                $has_posts = true;
                                                ?>
                                                <div class="post">
                                                    <div class="content-post">
                                                        <div class="content-original-post">
                                                            <div class="usuario">
                                                                <div class="user-info-container">
                                                                    <img src="<?= displayImage($post['fotoUsuario']) ?>"
                                                                        alt="Foto do usuário" class="user-avatar">
                                                                    <div class="user-details">
                                                                        <h2><?= htmlspecialchars($post['nome']) ?></h2>
                                                                        <p>@<?= htmlspecialchars($post['arroba_usuario']) ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="user-info-follow">
                                                                    <?php if ($post['idUsuario'] != $_SESSION["usuario_id"]): ?>
                                                                        <?php
                                                                        // Consulta para verificar se segue
                                                                        $sql_seguindo = "SELECT 1 FROM tblSeguidores
                                                                                WHERE idSeguidor = ? AND idSeguido = ?";
                                                                        $params_seguindo = [$_SESSION["usuario_id"], $post['idUsuario']];
                                                                        $stmt_seguindo = sqlsrv_query($conn, $sql_seguindo, $params_seguindo);
                                                                        $ja_segue = $stmt_seguindo && sqlsrv_fetch($stmt_seguindo);
                                                                        ?>

                                                                        <button class="follow-btn <?= $ja_segue ? 'following' : '' ?>"
                                                                            data-user-id="<?= $post['idUsuario'] ?>"
                                                                            onclick="seguirUsuario(<?= $post['idUsuario'] ?>, this)">
                                                                            <?= $ja_segue ? 'Deixar de seguir' : 'Seguir' ?>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="post-content" onclick="window.location.href='../Post/PostDetails.php?id=<?= $post['idPublicacao'] ?>'">
                                                                <?= transformURLsIntoLinks($post['conteudo']) ?>
                                                            </div>
                                                            <?php if (!empty($post['idLivro'])): ?>
                                                                <div class="attached-book">
                                                                    <?php if (!empty($post['imgCapa'])): ?>
                                                                        <img src="data:image/jpeg;base64,<?= base64_encode($post['imgCapa']) ?>"
                                                                            alt="Capa do livro" class="bookCoverAttached">
                                                                    <?php else: ?>
                                                                        <div class="no-book-cover">
                                                                            <i class="fa-solid fa-book"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="book-info">
                                                                        <p class="nomeLivroPost"><?= htmlspecialchars($post['nomeLivro']) ?></p>
                                                                        <?php if (!empty($post['nomeAutor'])): ?>
                                                                            <p class="nomeAutorPost">
                                                                                <?= htmlspecialchars($post['nomeAutor']) ?></p>
                                                                        <?php endif; ?>
                                                                        <?php if (!empty($post['descLivro'])): ?>
                                                                            <div class="book-description">
                                                                                <p><?= htmlspecialchars(mb_convert_encoding($post['descLivro'], 'UTF-8', 'ISO-8859-1')) ?></p>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="cont-section">
                                                                <div class="like-section">
                                                                    <button type="button"
                                                                        class="like-btn <?= ($post['usuario_curtiu'] > 0) ? 'liked' : '' ?>"
                                                                        onclick="curtir(<?= $post['idPublicacao'] ?>, this)">
                                                                        <i
                                                                            class="<?= ($post['usuario_curtiu'] > 0) ? 'fas' : 'far' ?> fa-heart"></i>
                                                                        <span class="like-count"><?= $post['total_likes'] ?></span>
                                                                    </button>
                                                                </div>
                                                                <div class="comment-section-count">
                                                                    <span class="comment-count">
                                                                        <i class="far fa-comment"></i>
                                                                        <span><?= $post['total_comentarios'] ?></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="comment-section">
                                                                <form method="POST" action="../Functions/comentar.php" class="comment-form"
                                                                    >
                                                                    <input type="hidden" name="post_id"
                                                                        value="<?= $post['idPublicacao'] ?>">
                                                                    <input type="text" name="comentario"
                                                                        placeholder="Escreva um comentário..." class="comment-input"
                                                                        required>
                                                                    <button type="submit" class="comment-button"
                                                                        >Comentar</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="comments-list">
                                                            <?php
                                                            $sql_comentarios = "SELECT C.idComentario, C.comentario, C.data_coment, U.idUsuario, U.arroba_usuario, U.fotoUsuario,
                                                            (SELECT COUNT(*) FROM tblLikesPorComentario WHERE idComentario = C.idComentario) AS totalLikes
                                                            FROM tblComentario C
                                                            JOIN tblUsuario U ON C.idUsuario = U.idUsuario
                                                            WHERE C.idPublicacao = ?
                                                            ORDER BY C.data_coment ASC";

                                                            $params_comentarios = array($post['idPublicacao']);
                                                            $comentarios = sqlsrv_query($conn, $sql_comentarios, $params_comentarios);

                                                            if ($comentarios) {
                                                                while ($comentario = sqlsrv_fetch_array($comentarios, SQLSRV_FETCH_ASSOC)):
                                                                    $data_comentario = $comentario['data_coment']->format('H:i');

                                                                    // Verifica se o usuário curtiu este comentário
                                                                    $sql_verifica_like = "SELECT 1 FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = ?";
                                                                    $params_like = array($_SESSION["usuario_id"], $comentario['idComentario']);
                                                                    $stmt_like = sqlsrv_query($conn, $sql_verifica_like, $params_like);
                                                                    $ja_curtiu = $stmt_like && sqlsrv_fetch($stmt_like);
                                                                    ?>
                                                                    <div class="comment">
                                                                        <div class="comment-header">
                                                                            <img src="<?= displayImage($comentario['fotoUsuario']) ?>"
                                                                                alt="Foto do usuário" class="user-avatar">
                                                                            <div class="comment-user-info">
                                                                                <div class="comment-user-name">
                                                                                    @<?= htmlspecialchars($comentario['arroba_usuario']) ?>
                                                                                </div>
                                                                                <div class="comment-divisor">
                                                                                    <i class="fa-solid fa-circle"></i>
                                                                                </div>
                                                                                <div class="comment-time">
                                                                                    <?= $data_comentario ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="comment-text">
                                                                            <?= transformURLsIntoLinks($comentario['comentario']) ?>
                                                                        </div>
                                                                        <div class="comment-actions">
                                                                            <button type="button"
                                                                                class="like-comment-btn <?= $ja_curtiu ? 'likedComment' : '' ?>"
                                                                                onclick="curtirComentario(<?= $comentario['idComentario'] ?>, this)">
                                                                                <i class="<?= $ja_curtiu ? 'fas' : 'far' ?> fa-heart"></i>
                                                                                <span
                                                                                    class="like-comment-count"><?= $comentario['totalLikes'] ?? 0 ?></span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile;
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        if (!$has_posts): ?>
                                            <div class="no-posts">
                                                <p>Ainda não tem nenhuma review criada, tente criar uma!</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="contentPosts">
                                    <div class="containerContent">
                                        <?php
                                        // Verifica se há posts na aba "Seguindo"
                                        $has_posts_seguindo = false;
                                        if ($result_posts_seguindo) {
                                            while ($post = sqlsrv_fetch_array($result_posts_seguindo, SQLSRV_FETCH_ASSOC)) {
                                                $has_posts_seguindo = true;
                                                ?>
                                                <div class="post">
                                                    <div class="content-post">
                                                        <div class="content-original-post">
                                                            <div class="usuario">
                                                                <div class="user-info-container">
                                                                    <img src="<?= displayImage($post['fotoUsuario']) ?>"
                                                                        alt="Foto do usuário" class="user-avatar">
                                                                    <div class="user-details">
                                                                        <h2><?= htmlspecialchars($post['nome']) ?></h2>
                                                                        <p>@<?= htmlspecialchars($post['arroba_usuario']) ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="user-info-follow">
                                                                    <?php if ($post['idUsuario'] != $_SESSION["usuario_id"]): ?>
                                                                        <?php
                                                                        $sql_seguindo = "SELECT 1 FROM tblSeguidores
                                                                                WHERE idSeguidor = ? AND idSeguido = ?";
                                                                        $params_seguindo = [$_SESSION["usuario_id"], $post['idUsuario']];
                                                                        $stmt_seguindo = sqlsrv_query($conn, $sql_seguindo, $params_seguindo);
                                                                        $ja_segue = $stmt_seguindo && sqlsrv_fetch($stmt_seguindo);
                                                                        ?>
                                                                        <button class="follow-btn <?= $ja_segue ? 'following' : '' ?>"
                                                                            data-user-id="<?= $post['idUsuario'] ?>"
                                                                            onclick="seguirUsuario(<?= $post['idUsuario'] ?>, this)">
                                                                            <?= $ja_segue ? 'Deixar de seguir' : 'Seguir' ?>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="post-content" onclick="window.location.href='../Post/PostDetails.php?id=<?= $post['idPublicacao'] ?>'">
                                                                <?= transformURLsIntoLinks($post['conteudo']) ?>
                                                            </div>
                                                            <?php if (!empty($post['idLivro'])): ?>
                                                                <div class="attached-book">
                                                                    <?php if (!empty($post['imgCapa'])): ?>
                                                                        <img src="data:image/jpeg;base64,<?= base64_encode($post['imgCapa']) ?>"
                                                                            alt="Capa do livro">
                                                                    <?php else: ?>
                                                                        <div class="no-book-cover">
                                                                            <i class="fa-solid fa-book"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="book-info">
                                                                        <p class="nomeLivroPost">
                                                                            <?= htmlspecialchars($post['nomeLivro']) ?>
                                                                        </p>
                                                                        <?php if (!empty($post['nomeAutor'])): ?>
                                                                            <p class="nomeAutorPost">
                                                                                <?= htmlspecialchars($post['nomeAutor']) ?></p>
                                                                        <?php endif; ?>
                                                                        <?php if (!empty($post['descLivro'])): ?>
                                                                            <div class="book-description">
                                                                                <p><?= htmlspecialchars(mb_convert_encoding($post['descLivro'], 'UTF-8', 'ISO-8859-1')) ?></p>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="cont-section">
                                                                <div class="like-section">
                                                                    <button type="button"
                                                                        class="like-btn <?= ($post['usuario_curtiu'] > 0) ? 'liked' : '' ?>"
                                                                        onclick="curtir(<?= $post['idPublicacao'] ?>, this)">
                                                                        <i
                                                                            class="<?= ($post['usuario_curtiu'] > 0) ? 'fas' : 'far' ?> fa-heart"></i>
                                                                        <span class="like-count"><?= $post['total_likes'] ?></span>
                                                                    </button>
                                                                </div>
                                                                <div class="comment-section-count">
                                                                    <span class="comment-count">
                                                                        <i class="far fa-comment"></i>
                                                                        <span><?= $post['total_comentarios'] ?></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="comment-section">
                                                                <form method="POST" action="../Functions/comentar.php" class="comment-form">
                                                                    <input type="hidden" name="post_id"
                                                                        value="<?= $post['idPublicacao'] ?>">
                                                                    <input type="text" name="comentario"
                                                                        placeholder="Escreva um comentário..." class="comment-input"
                                                                        required>
                                                                    <button type="submit" class="comment-button">Comentar</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="comments-list">
                                                            <?php
                                                            $sql_comentarios = "SELECT C.idComentario, C.comentario, C.data_coment, U.idUsuario, U.arroba_usuario, U.fotoUsuario,
                                                            (SELECT COUNT(*) FROM tblLikesPorComentario WHERE idComentario = C.idComentario) AS totalLikes
                                                            FROM tblComentario C
                                                            JOIN tblUsuario U ON C.idUsuario = U.idUsuario
                                                            WHERE C.idPublicacao = ?
                                                            ORDER BY C.data_coment ASC";

                                                            $params_comentarios = array($post['idPublicacao']);
                                                            $comentarios = sqlsrv_query($conn, $sql_comentarios, $params_comentarios);

                                                            if ($comentarios) {
                                                                while ($comentario = sqlsrv_fetch_array($comentarios, SQLSRV_FETCH_ASSOC)):
                                                                    $data_comentario = $comentario['data_coment']->format('H:i');

                                                                    $sql_verifica_like = "SELECT 1 FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = ?";
                                                                    $params_like = array($_SESSION["usuario_id"], $comentario['idComentario']);
                                                                    $stmt_like = sqlsrv_query($conn, $sql_verifica_like, $params_like);
                                                                    $ja_curtiu = $stmt_like && sqlsrv_fetch($stmt_like);
                                                                    ?>
                                                                    <div class="comment">
                                                                        <div class="comment-header">
                                                                            <img src="<?= displayImage($comentario['fotoUsuario']) ?>"
                                                                                alt="Foto do usuário" class="user-avatar">
                                                                            <div class="comment-user-info">
                                                                                <div class="comment-user-name">
                                                                                    @<?= htmlspecialchars($comentario['arroba_usuario']) ?>
                                                                                </div>
                                                                                <div class="comment-divisor">
                                                                                    <i class="fa-solid fa-circle"></i>
                                                                                </div>
                                                                                <div class="comment-time">
                                                                                    <?= $data_comentario ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="comment-text">
                                                                            <?= htmlspecialchars($comentario['comentario']) ?>
                                                                        </div>
                                                                        <div class="comment-actions">
                                                                            <button type="button"
                                                                                class="like-comment-btn <?= $ja_curtiu ? 'likedComment' : '' ?>"
                                                                                onclick="curtirComentario(<?= $comentario['idComentario'] ?>, this)">
                                                                                <i class="<?= $ja_curtiu ? 'fas' : 'far' ?> fa-heart"></i>
                                                                                <span
                                                                                    class="like-comment-count"><?= $comentario['totalLikes'] ?? 0 ?></span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile;
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        if (!$has_posts_seguindo): ?>
                                            <div class="no-posts">
                                                <p>Você não está seguindo ninguém ainda ou os usuários que você segue não
                                                    postaram nada.</p>
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
    </body>

    </html>