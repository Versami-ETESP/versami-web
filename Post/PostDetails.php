<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
include '../config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../Login/Login.php");
    exit;
}

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    header("Location: ../Feed/Feed.php");
    exit;
}

// Na consulta SQL do post, adicione:
$sql_post = "SELECT 
        p.*, 
        u.idUsuario as autor_id, u.nome, u.arroba_usuario, u.fotoUsuario,
        l.idLivro, l.nomeLivro, l.imgCapa as livroCapa, l.descLivro,
        a.nomeAutor as autor,
        (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao) as total_likes,
        (SELECT COUNT(*) FROM tblLikesPorPost WHERE idPublicacao = p.idPublicacao AND idUsuario = ?) as usuario_curtiu,
        (SELECT COUNT(*) FROM tblSeguidores WHERE idSeguidor = ? AND idSeguido = u.idUsuario) as seguindo,
        (SELECT COUNT(*) FROM tblLivrosFavoritos WHERE idLivro = l.idLivro AND idUsuario = ?) as livro_favoritado
    FROM tblPublicacao p
    LEFT JOIN tblUsuario u ON p.idUsuario = u.idUsuario
    LEFT JOIN tblLivro l ON p.idLivro = l.idLivro
    LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
    WHERE p.idPublicacao = ?";

// Atualize os parâmetros para incluir o ID do usuário novamente
$params_post = array($_SESSION["usuario_id"], $_SESSION["usuario_id"], $_SESSION["usuario_id"], $post_id);
$stmt_post = sqlsrv_query($conn, $sql_post, $params_post);
if ($stmt_post === false) {
    die(print_r(sqlsrv_errors(), true));
}

$post = sqlsrv_fetch_array($stmt_post, SQLSRV_FETCH_ASSOC);

if (!$post) {
    header("Location: ../Feed/Feed.php");
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
    <link rel="stylesheet" href="CSS/PostDetails.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="content">
        <div class="header-menu">
            <div class="sidebar" id="sidebar">
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
                            $sql_count = "SELECT COUNT(*) as total FROM tblNotificacao WHERE idUsuario = ? AND visualizada = 0";
                            $stmt_count = sqlsrv_query($conn, $sql_count, array($_SESSION["usuario_id"]));
                            $count = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC);
                            if ($count && $count['total'] > 0): ?>
                                <span class="notification-badge"><?= $count['total'] ?></span>
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
                <div class="review-header">
                    <a href="feed.php" class="back-arrow">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2>Review</h2>
                </div>
                <div class="post-details-container">
                    <div class="post-header">
                        <img src="<?= displayImage($post['fotoUsuario']) ?>" alt="Foto do usuário" class="user-avatar">
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
                        <div class="post-action-buttons">
                            <?php if ($post['autor_id'] == $_SESSION["usuario_id"]): ?>
                                <div class="post-menu-item delete"
                                    onclick="showDeleteConfirmation(<?= $post['idPublicacao'] ?>)">
                                    <i class="fas fa-trash"></i>
                                    <span>Excluir Review</span>
                                </div>
                            <?php else: ?>
                                <div class="post-menu-item" onclick="denunciarPost(<?= $post['idPublicacao'] ?>)">
                                    <i class="fas fa-flag"></i>
                                    <span>Denunciar</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="post-content">
                        <?= transformURLsIntoLinks($post['conteudo']) ?>
                    </div>

                    <?php if (!empty($post['idLivro'])): ?>
                        <div class="attached-bookF">
                            <?php if (!empty($post['livroCapa'])): ?>
                                <img src="<?= displayImage($post['livroCapa']) ?>" alt="Capa do livro" class="book-cover">
                            <?php else: ?>
                                <div class="no-book-cover">
                                    <i class="fa-solid fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="book-info">
                                <h3><?= htmlspecialchars($post['nomeLivro']) ?></h3>
                                <?php if (!empty($post['autor'])): ?>
                                    <p class="book-author"><?= htmlspecialchars($post['autor']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($post['descLivro'])): ?>
                                    <p class="book-description">
                                        <?= htmlspecialchars(convertToUtf8($post['descLivro'])) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="book-actions">
                                    <button class="favorite-btn <?= $post['livro_favoritado'] ? 'favorited' : '' ?>"
                                        data-book-id="<?= $post['idLivro'] ?>"
                                        onclick="toggleBookFavorite(this, <?= $post['idLivro'] ?>)">
                                        <i class="<?= $post['livro_favoritado'] ? 'fas' : 'far' ?> fa-heart"></i>
                                        <?= $post['livro_favoritado'] ? 'Favoritado' : 'Favoritar' ?>
                                    </button>
                                    <a href="../Livro/Livro.php?id=<?= $post['idLivro'] ?>" class="view-book-btn">
                                        Ver livro
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

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
                        <form method="POST" action="../Functions/comentar.php" class="comment-form">
                            <input type="hidden" name="post_id" value="<?= $post['idPublicacao'] ?>">
                            <input type="text" name="comentario" placeholder="Escreva um comentário..."
                                class="comment-input" required>
                            <button type="submit" class="comment-button">Comentar</button>
                        </form>

                        <div class="comments-list">
                            <?php while ($comentario = sqlsrv_fetch_array($comentarios, SQLSRV_FETCH_ASSOC)): ?>
                                <div class="comment">
                                    <img src="<?= displayImage($comentario['fotoUsuario']) ?>" alt="Foto do usuário"
                                        class="comment-avatar">
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <strong><?= htmlspecialchars($comentario['nome']) ?></strong>
                                            <span>@<?= htmlspecialchars($comentario['arroba_usuario']) ?></span>
                                            <span class="comment-time">
                                                <?= $comentario['data_coment']->format('d/m/Y H:i') ?>
                                            </span>
                                        </div>
                                        <p><?= htmlspecialchars($comentario['comentario']) ?></p>
                                        <div class="comment-actions">
                                            <button type="button"
                                                class="like-comment-btn <?= ($comentario['usuario_curtiu'] > 0) ? 'likedComment' : '' ?>"
                                                onclick="curtirComentario(<?= $comentario['idComentario'] ?>, this)">
                                                <i
                                                    class="<?= ($comentario['usuario_curtiu'] > 0) ? 'fas' : 'far' ?> fa-heart"></i>
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

    <div class="modal-overlay" id="confirmationModalOverlay">
        <div class="modal-content">
            <h2>Confirmar Denúncia</h2>
            <p>Você tem certeza que deseja denunciar esta publicação?</p>
            <div class="modal-actions">
                <button id="cancelDenounceBtn" class="modal-btn cancel">Cancelar</button>
                <button id="confirmDenounceBtn" class="modal-btn confirm">Denunciar</button>
            </div>
        </div>
    </div>

    <div id="toastNotification" class="toast-notification"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        // Função para mostrar uma notificação toast
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

        // Função para exibir o modal de confirmação para DENÚNCIA
        function denunciarPost(postId) {
            console.log(`denunciarPost chamado para post_id: ${postId}`);
            showConfirmationModal(
                postId,
                'denounce', // Tipo de ação
                'Confirmar Denúncia',
                'Você tem certeza que deseja denunciar esta publicação?',
                'Denunciar',
                'cancelDenounceBtn',
                'confirmDenounceBtn'
            );
        }

        // Função para exibir o modal de confirmação para EXCLUSÃO
        function showDeleteConfirmation(postId) {
            console.log(`showDeleteConfirmation chamado para post_id: ${postId}`);
            showConfirmationModal(
                postId,
                'delete', // Tipo de ação
                'Confirmar Exclusão',
                'Tem certeza que deseja excluir esta publicação? Esta ação é irreversível!',
                'Excluir',
                'cancelDeleteBtn', // IDs dos botões no modal para esta ação
                'confirmDeleteBtn'
            );
        }

        // Função GENÉRICA para exibir o modal de confirmação
        function showConfirmationModal(postId, actionType, title, message, confirmText, cancelBtnId, confirmBtnId) {
            const modalOverlay = document.getElementById('confirmationModalOverlay');
            const modalTitle = modalOverlay.querySelector('h2');
            const modalMessage = modalOverlay.querySelector('p');
            const confirmButton = modalOverlay.querySelector('#confirmDenounceBtn'); // Este ID será sobrescrito
            const cancelButton = modalOverlay.querySelector('#cancelDenounceBtn'); // Este ID será sobrescrito

            if (!modalOverlay || !modalTitle || !modalMessage || !confirmButton || !cancelButton) {
                console.error("Um ou mais elementos do modal de confirmação não foram encontrados!");
                return;
            }

            // Atualiza o conteúdo do modal
            modalTitle.textContent = title;
            modalMessage.textContent = message;

            // Reconfigura o botão de confirmação
            confirmButton.textContent = confirmText;
            confirmButton.className = `modal-btn confirm ${actionType}`; // Adiciona classe para estilização e tipo de ação

            // Remove listeners antigos e adiciona novos
            // Criar novas funções para remover e adicionar listeners de forma segura
            confirmButton.onclick = null; // Remove qualquer listener inline antigo
            cancelButton.onclick = null; // Remove qualquer listener inline antigo

            // Adiciona listener com base no tipo de ação
            confirmButton.onclick = function () {
                confirmAction(postId, actionType);
            };
            cancelButton.onclick = function () {
                cancelAction(); // Função genérica para cancelar
            };

            // Atribui IDs temporários para referências específicas dentro do modal (opcional, para CSS)
            // Se você precisar de estilos muito específicos por tipo de ação para os botões do modal,
            // pode adicionar classes ou IDs aqui e ajustá-los no CSS.
            // Por exemplo:
            // confirmButton.id = confirmBtnId;
            // cancelButton.id = cancelBtnId;


            modalOverlay.setAttribute('data-post-id', postId);
            modalOverlay.setAttribute('data-action-type', actionType);
            modalOverlay.classList.add('active'); // Exibe o modal
            console.log(`Modal de confirmação exibido para ação: ${actionType}, Post ID: ${postId}`);
        }

        // Função GENÉRICA para confirmar a ação (denúncia ou exclusão)
        function confirmAction(postId, actionType) {
            console.log(`confirmAction chamado para Post ID: ${postId}, Tipo: ${actionType}`);
            const modalOverlay = document.getElementById('confirmationModalOverlay');
            modalOverlay.classList.remove('active'); // Esconde o modal imediatamente
            modalOverlay.removeAttribute('data-post-id');
            modalOverlay.removeAttribute('data-action-type');

            if (!postId) {
                console.warn("ID do post não encontrado para a ação.");
                showToast("Erro: ID da publicação não encontrado.", 'error');
                return;
            }

            let url = '';
            let data = {};
            let successMessage = '';
            let errorMessage = '';
            let redirectUrl = null; // Para redirecionar em caso de sucesso (exclusão)

            if (actionType === 'denounce') {
                url = '../Functions/denunciar_post.php';
                data = { post_id: postId };
                successMessage = 'Denúncia registrada com sucesso!';
                errorMessage = 'Erro ao registrar a denúncia.';
            } else if (actionType === 'delete') {
                url = '../Functions/excluir_post.php';
                data = { idPublicacao: postId };
                successMessage = 'Publicação e dados relacionados excluídos com sucesso!';
                errorMessage = 'Erro ao excluir a publicação.';
                redirectUrl = 'feed.php'; // Redireciona para o feed após exclusão
            } else {
                console.error("Tipo de ação desconhecido: " + actionType);
                showToast("Erro interno: tipo de ação inválido.", 'error');
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    console.log(`Resposta AJAX para ${actionType} recebida:`, response);
                    if (response.success) {
                        showToast(response.message || successMessage, 'success');
                        if (redirectUrl) {
                            setTimeout(() => {
                                window.location.href = redirectUrl;
                            }, 1500); // Atraso para o toast ser visível
                        }
                    } else {
                        showToast(response.error || errorMessage, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(`Erro na requisição AJAX para ${actionType}:`, status, error, xhr.responseText);
                    let details = xhr.responseJSON ? xhr.responseJSON.error : error;
                    showToast(`${errorMessage} Detalhes: ${details}`, 'error');
                }
            });
        }

        // Função genérica para cancelar a ação e fechar o modal
        function cancelAction() {
            console.log("cancelAction chamada.");
            const modalOverlay = document.getElementById('confirmationModalOverlay');
            if (modalOverlay) {
                modalOverlay.classList.remove('active');
                modalOverlay.removeAttribute('data-post-id');
                modalOverlay.removeAttribute('data-action-type');
                console.log("Modal de confirmação fechado.");
            }
        }
    </script>
</body>

</html>