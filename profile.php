<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: Login/login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

// Função para converter varbinary em base64
function binaryToBase64($binaryData)
{
    if ($binaryData === null || empty($binaryData)) {
        return '';
    }
    return 'data:image/jpeg;base64,' . base64_encode($binaryData);
}

// Busca dados do usuário logado
$sql_usuario = "SELECT 
    u.*,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = u.idUsuario) as seguidores,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguidor = u.idUsuario) as seguindo
FROM tblUsuario u
WHERE u.idUsuario = ?";
$params_usuario = array($usuario_id);
$result_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);
$usuario = sqlsrv_fetch_array($result_usuario, SQLSRV_FETCH_ASSOC);

// Converter imagens para base64
$fotoUsuarioBase64 = binaryToBase64($usuario['fotoUsuario']);
$fotoCapaBase64 = binaryToBase64($usuario['fotoCapa']);

// Contagem de seguidores/seguindo
$sql_seguidores = "SELECT 
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguido = ?) as seguidores,
    (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguidor = ?) as seguindo";
$params_seguidores = array($usuario_id, $usuario_id);
$result_seguidores = sqlsrv_query($conn, $sql_seguidores, $params_seguidores);
$contadores = sqlsrv_fetch_array($result_seguidores, SQLSRV_FETCH_ASSOC);

// Buscar posts do usuário (com tratamento de data)
$sql_posts = "SELECT 
    p.*, 
    l.nomeLivro, l.idLivro,
    (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as likes
FROM tblPublicacao p
LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
WHERE p.idUsuario = ?
ORDER BY p.dataPublic DESC";

$params_posts = array($usuario_id);
$result_posts = sqlsrv_query($conn, $sql_posts, $params_posts);

// Buscar livros favoritos do usuário
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
    <title>Perfil</title>
    <link rel="stylesheet" href="css/style-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="header-links">
        <a href="feed.php">Feed</a>
        <a href="explorar.php">Explorar</a>
        <!-- Botão de Logout -->
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>
    </div>
    <div class="profile-container">
        <!-- Cabeçalho do perfil -->
        <div class="profile-header">
            <img src="<?= $fotoCapaBase64 ?: 'imagens/capa-padrao.jpg' ?>" class="cover-photo" alt="Capa do perfil">
            <img src="<?= $fotoUsuarioBase64 ?: 'imagens/perfil-padrao.jpg' ?>" class="profile-photo"
                alt="Foto do perfil">
            <h1><?= htmlspecialchars($usuario['nome'] ?? '') ?></h1>
            <p>@<?= htmlspecialchars($usuario['arroba_usuario'] ?? '') ?></p>
            <p class="bio"><?= htmlspecialchars($usuario['bio_usuario'] ?? '') ?></p>
            <div class="stats">
                <span><?= $contadores['seguidores'] ?? 0 ?> seguidores</span>
                <span><?= $contadores['seguindo'] ?? 0 ?> seguindo</span>
            </div>
        </div>

        <!-- Posts do usuário -->
        <div class="posts-container">
            <?php while ($post = sqlsrv_fetch_array($result_posts, SQLSRV_FETCH_ASSOC)): ?>
                <div class="post">
                    <div class="post-content">
                        <?= htmlspecialchars($post['conteudo'] ?? 'Post sem texto') ?>
                    </div>
                    <div class="post-meta">
                        <?php
                        // Tratamento seguro para datas
                        if ($post['dataPublic'] !== null) {
                            $data = $post['dataPublic']->format('d/m/Y H:i');
                            echo "<span>Postado em: $data</span>";
                        }
                        ?>
                        <div class="likes">
                            <span class="like-count"><?= $post['likes'] ?? 0 ?></span> curtidas
                        </div>
                    </div>
                    <div class="content-right">
                        <?php if (isset($post['usuario_id']) && $post['usuario_id'] == $_SESSION["usuario_id"]): ?>
                            <!-- Ícone de excluir -->
                            <i class="fa-solid fa-trash delete-post" data-id="<?= $post['id'] ?>"></i>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <!-- Seção de Livros Favoritos -->
        <div class="favorite-books">
            <h2>Livros Favoritos</h2>
            <div class="books-grid">
                <?php while ($livro = sqlsrv_fetch_array($result_favoritos, SQLSRV_FETCH_ASSOC)): ?>
                    <div class="book-item">
                        <?php if (!empty($livro['imgCapa'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($livro['imgCapa']) ?>" alt="Capa do livro"
                                class="book-cover">
                        <?php else: ?>
                            <div class="no-cover">
                                <i class="fa-solid fa-book"></i>
                            </div>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($livro['nomeLivro']) ?></h3>
                        <p><?= htmlspecialchars($livro['autor']) ?></p>
                        <a href="livro.php?id=<?= $livro['idLivro'] ?>" class="view-book">Ver livro</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <script src="js/theme-switcher.js"></script>
</body>

</html>