<?php
session_start();
include '../config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../Login/login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

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
    <link rel="stylesheet" href="CSS/ProfilleStyle.css">
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
                    <li onclick="location.href='Profile.php'" class="active">
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
                                <?= htmlspecialchars($usuario['bio_usuario'] ?? 'Nenhuma biografia definida.') ?></p>
                        </div>
                    </div>
                    <button class="edit-profile-btn">
                        <i class="fa-solid fa-user-gear"></i>
                    </button>
                </div>
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
                                onclick="window.location.href='../Post/PostDetails.php?id=<?= $post['idPublicacao'] ?>'">
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
                                        onclick="event.stopPropagation(); window.location.href='../Post/PostDetails.php?id=<?= $post['idPublicacao'] ?>';">
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
                                                alt="Capa do livro" class="book-cover-favorite">
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
                                            <a href="../Livro/Livro.php?id=<?= $livro['idLivro'] ?>" class="view-btn">
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

    <div class="popup-overlay" id="reviewPopupOverlay">
        <div class="popup">
            <div class="btn-top-content">
                <div class="btn-close-content">
                    <button class="btn-close"><i class="fa-solid fa-x"></i></button>
                </div>
                <h2>Criar Review</h2>
            </div>
            <form method="POST" id="postForm" action="Profile.php" enctype="multipart/form-data">
                <input type="hidden" name="idUsuario" value="<?= htmlspecialchars($usuario_id) ?>">
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

    <div class="popup-overlay" id="editProfilePopupOverlay">
        <div class="popup">
            <div class="btn-top-content">
                <div class="btn-close-content">
                    <button class="btn-close" id="closeEditProfilePopup"><i class="fa-solid fa-x"></i></button>
                </div>
                <h2>Editar Perfil</h2>
                <button class="btn-submit" id="saveProfileChanges">Salvar</button>
            </div>
            <form id="editProfileForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="idUsuario" value="<?= htmlspecialchars($usuario['idUsuario'] ?? '') ?>">

                <div class="form-group">
                    <label for="edit_nome">Nome</label>
                    <input type="text" id="edit_nome" name="nome" maxlength="50"
                        value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="edit_arroba_usuario">Nome de Usuário (@)</label>
                    <input type="text" id="edit_arroba_usuario" name="arroba_usuario"
                        value="<?= htmlspecialchars($usuario['arroba_usuario'] ?? '') ?>" readonly
                        title="Nome de usuário não pode ser alterado">
                </div>

                <div class="form-group file-input-group">
                    <label for="edit_fotoUsuario"><i class="fa-solid fa-image"></i> Foto de Perfil</label>
                    <label for="edit_fotoUsuario" class="file-input-label">
                        <i class="fa-solid fa-upload"></i> Selecionar Foto
                    </label>
                    <input type="file" id="edit_fotoUsuario" name="fotoUsuario"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        onchange="previewImage(this, 'previewEditFotoUsuario')">
                    <small>Formatos aceitos: JPEG, PNG, GIF, WebP. Tamanho máximo: 40MB. Dimensões: 100x100 a 5000x5000
                        pixels.</small>
                    <div class="preview-container">
                        <img id="previewEditFotoUsuario" src="<?= $fotoUsuarioBase64 ?: 'Assets/padrao.png' ?>"
                            alt="Pré-visualização da foto de perfil" class="preview-circle">
                    </div>
                    <div class="remove-image-checkbox">
                        <input type="checkbox" id="remove_fotoUsuario" name="remove_fotoUsuario" value="1">
                        <label for="remove_fotoUsuario">Remover foto de perfil</label>
                    </div>
                </div>

                <div class="form-group file-input-group">
                    <label for="edit_fotoCapa"><i class="fa-solid fa-image"></i> Foto de Capa</label>
                    <label for="edit_fotoCapa" class="file-input-label">
                        <i class="fa-solid fa-upload"></i> Selecionar Capa
                    </label>
                    <input type="file" id="edit_fotoCapa" name="fotoCapa"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        onchange="previewImage(this, 'previewEditFotoCapa')">
                    <small>Formatos aceitos: JPEG, PNG, GIF, WebP. Tamanho máximo: 40MB. Dimensões: 100x100 a 5000x5000
                        pixels.</small>
                    <div class="preview-container">
                        <img id="previewEditFotoCapa" src="<?= $fotoCapaBase64 ?: 'Assets/padraoCapa.png' ?>"
                            alt="Pré-visualização da foto de capa" class="preview-rect">
                    </div>
                    <div class="remove-image-checkbox">
                        <input type="checkbox" id="remove_fotoCapa" name="remove_fotoCapa" value="1">
                        <label for="remove_fotoCapa">Remover foto de capa</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_biografia">Biografia</label>
                    <textarea id="edit_biografia" name="biografia" maxlength="255"
                        placeholder="Fale um pouco sobre você..."
                        rows="4"><?= htmlspecialchars($usuario['bio_usuario'] ?? '') ?></textarea>
                    <small>Máximo de 255 caracteres.</small>
                </div>

            </form>
        </div>
    </div>

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

        // Função para mostrar uma notificação toast (copiada do post_details.php)
        function showToast(message, type = 'success') {
            console.log(`Showing toast: ${message} (${type})`);
            const toast = document.getElementById("toastNotification");
            if (!toast) {
                console.error("Toast notification element not found!");
                return;
            }
            toast.className = "toast-notification show " + type; // Adiciona 'show' e a classe do tipo
            toast.textContent = message;

            setTimeout(function () {
                toast.className = toast.className.replace("show", "");
            }, 3000);
        }
        // --- FUNÇÕES PARA O POPUP DE EDIÇÃO DE PERFIL ---

        // Função para exibir a imagem de pré-visualização (adaptada de setup_profile.php)
        // Dentro do bloco <script> em profile.php

        // Função para exibir a imagem de pré-visualização (AJUSTADA)
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            // As URLs padrão são definidas em config.php e devem ser usadas pelo displayImage
            const defaultFotoPerfilSrc = 'Assets/default_profile.png';
            const defaultFotoCapaSrc = 'Assets/default_cover.png';

            // Obtém o checkbox de remover foto associado
            const removeCheckboxId = (previewId === 'previewEditFotoUsuario') ? 'remove_fotoUsuario' : 'remove_fotoCapa';
            const removeCheckbox = document.getElementById(removeCheckboxId);

            if (file) {
                const maxSizeMB = 40;
                const maxSizeBytes = maxSizeMB * 1024 * 1024;

                if (file.size > maxSizeBytes) {
                    alert(`O arquivo é muito grande. Tamanho máximo permitido: ${maxSizeMB}MB`);
                    input.value = ''; // Limpa o input
                    preview.src = (previewId === 'previewEditFotoUsuario') ? '<?= $fotoUsuarioBase64 ?: "Assets/padrao.png" ?>' : '<?= $fotoCapaBase64 ?: "Assets/padraoCapa.png" ?>'; // Volta para a foto atual ou padrão
                    if (removeCheckbox) removeCheckbox.checked = false; // Desmarca se houver erro
                    return;
                }

                if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/gif') && !file.type.match('image/webp')) {
                    alert('Por favor, selecione um arquivo de imagem (JPEG, PNG, GIF ou WebP)');
                    input.value = '';
                    preview.src = (previewId === 'previewEditFotoUsuario') ? '<?= $fotoUsuarioBase64 ?: "Assets/padrao.png" ?>' : '<?= $fotoCapaBase64 ?: "Assets/padraoCapa.png" ?>'; // Volta para a foto atual ou padrão
                    if (removeCheckbox) removeCheckbox.checked = false; // Desmarca se houver erro
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        const minWidth = 100, minHeight = 100;
                        const maxWidth = 5000, maxHeight = 5000;

                        if (this.width < minWidth || this.height < minHeight ||
                            this.width > maxWidth || this.height > maxHeight) {
                            alert(`A imagem deve ter entre ${minWidth}x${minHeight} e ${maxWidth}x${maxHeight} pixels.`);
                            input.value = '';
                            preview.src = (previewId === 'previewEditFotoUsuario') ? '<?= $fotoUsuarioBase64 ?: "Assets/padrao.png" ?>' : '<?= $fotoCapaBase64 ?: "Assets/padraoCapa.png" ?>';
                            if (removeCheckbox) removeCheckbox.checked = false;
                        } else {
                            preview.src = e.target.result;
                            if (removeCheckbox) removeCheckbox.checked = false; // Desmarca ao selecionar nova foto
                        }
                    };
                    img.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                // Se nenhum arquivo foi selecionado (input limpo), volta para a imagem atual ou padrão
                const currentSrc = (previewId === 'previewEditFotoUsuario') ? '<?= $fotoUsuarioBase64 ?: "Assets/padrao.png" ?>' : '<?= $fotoCapaBase64 ?: "Assets/padraoCapa.png" ?>';
                preview.src = currentSrc;
                // Não desmarca o checkbox de remover aqui, a não ser que o input.value tenha sido explicitamente limpo
            }
            // Atualiza o texto do label do input file (mantido do seu código anterior)
            const label = input.previousElementSibling;
            if (label && label.classList.contains('file-input-label')) {
                if (file) {
                    label.innerHTML = `<i class="fa-solid fa-check"></i> ${file.name}`;
                    label.classList.add('changed');
                } else {
                    label.innerHTML = `<i class="fa-solid fa-upload"></i> Selecionar ${previewId === 'previewEditFotoUsuario' ? 'Foto' : 'Capa'}`;
                    label.classList.remove('changed');
                }
            }
        }

        // NOVO: Adicionar listeners para os checkboxes de remoção de imagem
        document.addEventListener('DOMContentLoaded', function () {
            const removeFotoUsuarioCheckbox = document.getElementById('remove_fotoUsuario');
            const previewEditFotoUsuario = document.getElementById('previewEditFotoUsuario');
            const editFotoUsuarioInput = document.getElementById('edit_fotoUsuario');
            const labelFotoUsuario = editFotoUsuarioInput.previousElementSibling;

            const removeFotoCapaCheckbox = document.getElementById('remove_fotoCapa');
            const previewEditFotoCapa = document.getElementById('previewEditFotoCapa');
            const editFotoCapaInput = document.getElementById('edit_fotoCapa');
            const labelFotoCapa = editFotoCapaInput.previousElementSibling;

            // Função auxiliar para redefinir o input file e o label
            const resetFileInput = (inputElement, labelElement, previewElement, defaultSrc) => {
                inputElement.value = ''; // Limpa o arquivo selecionado
                labelElement.innerHTML = `<i class="fa-solid fa-upload"></i> Selecionar ${inputElement.id === 'edit_fotoUsuario' ? 'Foto' : 'Capa'}`;
                labelElement.classList.remove('changed');
                previewElement.src = defaultSrc; // Exibe a imagem padrão
            };

            if (removeFotoUsuarioCheckbox) {
                removeFotoUsuarioCheckbox.addEventListener('change', function () {
                    const defaultSrc = 'Assets/padrao.png';
                    if (this.checked) {
                        // Se o checkbox está marcado, exibe a imagem padrão no preview e limpa o input de arquivo
                        resetFileInput(editFotoUsuarioInput, labelFotoUsuario, previewEditFotoUsuario, defaultSrc);
                    } else {
                        // Se desmarcado, reverte o preview para a imagem atual do BD (se houver)
                        // A imagem atual do BD será carregada por PHP na inicialização do modal
                        previewEditFotoUsuario.src = '<?= $fotoUsuarioBase64 ?: "Assets/padrao.png" ?>';
                    }
                });
            }

            if (removeFotoCapaCheckbox) {
                removeFotoCapaCheckbox.addEventListener('change', function () {
                    const defaultSrc = 'Assets/padraoCapa.png';
                    if (this.checked) {
                        resetFileInput(editFotoCapaInput, labelFotoCapa, previewEditFotoCapa, defaultSrc);
                    } else {
                        previewEditFotoCapa.src = '<?= $fotoCapaBase64 ?: "Assets/padraoCapa.png" ?>';
                    }
                });
            }
        });

        // Função para abrir o popup de edição de perfil
        function openEditProfileModal() {
            console.log("Abrindo popup de edição de perfil.");
            const popupOverlay = document.getElementById('editProfilePopupOverlay');
            if (popupOverlay) {
                popupOverlay.style.display = 'flex';
                // Resetar previews de imagem caso o usuário cancele e abra novamente
                document.getElementById('previewEditFotoUsuario').src = '<?= $fotoUsuarioBase64 ?: 'Assets/padrao.png' ?>';
                document.getElementById('previewEditFotoCapa').src = '<?= $fotoCapaBase64 ?: 'Assets/padraoCapa.png' ?>';

                // Limpar campos de input file para permitir novas seleções
                document.getElementById('edit_fotoUsuario').value = '';
                document.getElementById('edit_fotoCapa').value = '';
                // Resetar texto dos labels dos inputs file
                document.querySelectorAll('#editProfileForm .file-input-label').forEach(label => {
                    label.classList.remove('changed');
                    if (label.htmlFor === 'edit_fotoUsuario') {
                        label.innerHTML = '<i class="fa-solid fa-upload"></i> Selecionar Foto';
                    } else if (label.htmlFor === 'edit_fotoCapa') {
                        label.innerHTML = '<i class="fa-solid fa-upload"></i> Selecionar Capa';
                    }
                });


            } else {
                console.error("Edit profile popup overlay not found!");
            }
        }

        // Função para fechar o popup de edição de perfil
        function closeEditProfileModal() {
            console.log("Fechando popup de edição de perfil.");
            const popupOverlay = document.getElementById('editProfilePopupOverlay');
            if (popupOverlay) {
                popupOverlay.style.display = 'none';
            }
        }

        // Função para enviar as alterações do perfil via AJAX
        function saveProfileChanges() {
            console.log("Tentando salvar alterações do perfil.");
            const form = document.getElementById('editProfileForm');
            const formData = new FormData(form);

            // Adiciona o nome do usuário logado à FormData (para verificações no backend, se necessário)
            // formData.append('usuario_logado_id', '<?= $_SESSION["usuario_id"] ?>');

            $.ajax({
                url: '../Functions/atualizar_perfil.php', // O script PHP para processar a atualização
                method: 'POST',
                data: formData,
                processData: false, // Necessário para FormData
                contentType: false, // Necessário para FormData
                dataType: 'json',
                success: function (response) {
                    console.log("Resposta de atualização de perfil recebida:", response);
                    if (response.success) {
                        showToast(response.message, 'success');
                        // Recarrega a página para exibir as alterações
                        setTimeout(() => {
                            location.reload();
                        }, 1500); // Atraso para o toast ser visível
                    } else {
                        showToast(response.error || "Erro ao atualizar perfil.", 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erro na requisição AJAX de atualização de perfil:", status, error, xhr.responseText);
                    let details = xhr.responseJSON ? xhr.responseJSON.error : error;
                    showToast(`Erro ao atualizar perfil. Detalhes: ${details}`, 'error');
                }
            });
        }


        // --- LISTENERS DE EVENTOS ---
        document.addEventListener('DOMContentLoaded', function () {
            console.log("DOMContentLoaded acionado em profile.php");

            // Listener para o botão "Editar Perfil"
            const editProfileBtn = document.querySelector('.edit-profile-btn');
            if (editProfileBtn) {
                editProfileBtn.addEventListener('click', openEditProfileModal);
            } else {
                console.error("Botão 'Editar Perfil' NÃO encontrado.");
            }

            // Listener para o botão 'X' de fechar o popup de edição
            const closeEditProfilePopupBtn = document.getElementById('closeEditProfilePopup');
            if (closeEditProfilePopupBtn) {
                closeEditProfilePopupBtn.addEventListener('click', closeEditProfileModal);
            }

            // Listener para fechar o popup ao clicar fora
            const editProfilePopupOverlay = document.getElementById('editProfilePopupOverlay');
            if (editProfilePopupOverlay) {
                editProfilePopupOverlay.addEventListener('click', function (event) {
                    if (event.target === editProfilePopupOverlay) {
                        closeEditProfileModal();    
                    }
                });
            }

            // Listener para o botão "Salvar" dentro do popup de edição
            const saveProfileChangesBtn = document.getElementById('saveProfileChanges');
            if (saveProfileChangesBtn) {
                saveProfileChangesBtn.addEventListener('click', saveProfileChanges);
            } else {
                console.error("Botão 'Salvar' do popup de edição NÃO encontrado.");
            }

            // Garante que a aba de reviews é a padrão ao carregar a página
            showProfileTab('posts');
        });
    </script>
</body>

</html>