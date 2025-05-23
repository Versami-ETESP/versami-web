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
    <link rel="stylesheet" href="style-profile-view.css">
</head>

<body>
    <div class="content">
        <div class="header-menu">
            <button class="menu-btn" id="menuBtn" onclick="toggleMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="sidebar" id="sidebar">
                <div class="top-content-sidebar">
                    <img src="Assets/logoVersamiBlue.png" alt="Versami" />
                    <ul>
                        <li onclick="location.href='feed.php'">
                            <i class="fa-solid fa-house"></i> Home
                        </li>
                        <li onclick="location.href='explorar.php'" class="active">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='BlogUsuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='notificacao.php'">
                            <i class="fa-solid fa-bell"></i> Notificação
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
                        <a href="../../BD/logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="principal-content">
            <div class="user">
                <div class="capa-perfil">
                    <img id="fotoCapa" src="<?= htmlspecialchars($usuario['fotoCapa']) ?>" class="capa-perfil">
                </div>
                <div class="content-user-perfil">
                    <div class="foto-perfil">
                        <img id="fotoPerfil" src="<?= htmlspecialchars($usuario['fotoUsuario']) ?>" class="profile-pic"
                            onclick="expandImage()">
                    </div>
                    <div class="side-perfil">
                        <div class="top-perfil">
                            <div class="info-perfil">
                                <div class="nomes-perfil">
                                    <h1 class="nome-perfil" id="userNome"><?= htmlspecialchars($usuario['nome']) ?></h1>
                                    <p class="arroba-perfil" id="userArroba">
                                        @<?= htmlspecialchars($usuario['arroba_usuario']) ?></p>
                                </div>
                            </div>
                            <div class="bio-perfil">
                                <div class="bio-content-perfil">
                                    <p><?= htmlspecialchars($usuario['bio_usuario']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="botao-perfil">
                            <?php if ($perfil_id != $_SESSION["usuario_id"]): ?>
                                <button id="seguir-btn" class="seguir-btn <?= $seguindo ? 'seguindo' : '' ?>"
                                    data-id="<?= $perfil_id ?>">
                                    <?= $seguindo ? 'Deixar de Seguir' : 'Seguir' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="info-contagem">
                <div class="contagem">
                    <div class="contagem-entrada">
                        <i class="fa-solid fa-calendar"></i>
                        <h4>Entrou em <span>2025</span></h4>
                    </div>
                    <div class="contagem-item">
                        <p><?= $contadores['total_seguindo'] ?? 0 ?></p>
                        <h4>Seguindo</h4>
                    </div>
                    <div class="contagem-item">
                        <p><?= $contadores['total_seguidores'] ?? 0 ?></p>
                        <h4>Seguidores</h4>
                    </div>
                    <div class="contagem-item">
                        <p>[0]</p>
                        <h4>Leituras</h4>
                    </div>
                </div>
            </div>
            <section class="posts" id="posts">
                <div class="tabs-container">
                    <div class="tabs">
                        <div class="tab active" onclick="changeTab(0)">Reviews</div>
                        <div class="tab" onclick="changeTab(1)">Livros Favoritos</div>
                    </div>
                    <div class="content-container">
                        <div class="contentPosts active">
                            <div class="containerContent">
                                <?php if (!$has_posts): ?>
                                    <div class="no-posts-message">
                                        <p><?= htmlspecialchars($usuario['nome']) ?> ainda não publicou nenhuma Review.</p>
                                    </div>
                                <?php else: ?>
                                    <?php while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)): ?>
                                        <div class="post">
                                            <div class="content-info-post">
                                                <div class="content-info-left">
                                                    <img src="<?= htmlspecialchars($post['fotoUsuario']) ?>"
                                                        alt="Foto de perfil" class="profile-pic-post" width="65" height="65">
                                                    <div class="content-info-nomes">
                                                        <p class="nome-usuario"><?= htmlspecialchars($post['nome']) ?></p>
                                                        <p class="arroba-usuario">
                                                            @<?= htmlspecialchars($post['arroba_usuario']) ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <p><?= htmlspecialchars($post['texto']) ?></p>
                                            <?php if (!empty($post['imagem'])): ?>
                                                <img src="<?= htmlspecialchars($post['imagem']) ?>" width="200">
                                            <?php endif; ?>
                                            <div class="actions">
                                                <button type="button" class="like-btn"
                                                    onclick="curtir(<?= $post['id'] ?>, this)">
                                                    <i
                                                        class="<?= isset($post['curtiu']) && $post['curtiu'] > 0 ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                                    <span
                                                        class="like-count"><?= isset($post['curtidas']) ? $post['curtidas'] : 0 ?></span>
                                                </button>
                                            </div>

                                            <!-- Seção de comentários -->
                                            <div class="comment-section">
                                                <form method="POST" action="comentar.php">
                                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                    <input type="text" name="comentario" placeholder="Escreva um comentário..."
                                                        required>
                                                    <button type="submit" class="comment-btn">Comentar</button>
                                                </form>

                                                <?php
                                                $sql_comentarios = "SELECT C.idComentario, C.comentario, C.data_coment, U.idUsuario, U.nome, U.arroba_usuario, U.fotoUsuario,
                                                (SELECT COUNT(*) FROM tblLikesPorComentario WHERE idComentario = C.idComentario) AS totalLikes,
                                                (SELECT COUNT(*) FROM tblLikesPorComentario WHERE idUsuario = ? AND idComentario = C.idComentario) AS curtiu
                                                FROM tblComentario C 
                                                JOIN tblUsuario U ON C.idUsuario = U.idUsuario 
                                                WHERE C.idPublicacao = ? 
                                                ORDER BY C.data_coment DESC";
                                                $params_comentarios = array($_SESSION["usuario_id"], $post['id']);
                                                $comentarios = sqlsrv_query($conn, $sql_comentarios, $params_comentarios);

                                                if (!$comentarios) {
                                                    die("Erro ao buscar comentários: " . print_r(sqlsrv_errors(), true));
                                                }

                                                while ($comentario = sqlsrv_fetch_array($comentarios, SQLSRV_FETCH_ASSOC)): ?>
                                                    <div class="comment">
                                                        <img src="<?= htmlspecialchars($comentario['fotoUsuario']) ?>"
                                                            alt="Foto de perfil" class="profile-pic-comment">
                                                        <strong><?= htmlspecialchars($comentario['nome']) ?>
                                                            (@<?= htmlspecialchars($comentario['arroba_usuario']) ?>):</strong>
                                                        <?= htmlspecialchars($comentario['comentario']) ?>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="contentPosts">
                            <div class="containerContent">
                                <p>Conteúdo de Livros Favoritos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/theme-switcher.js"></script>
        <script src="js/script.js"></script>
</body>

</html>