<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login/login.php");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

// Atualiza notificações para 'visualizada' = 1 quando a página é acessada
$sql_update_visualizada = "UPDATE tblNotificacao SET visualizada = 1 WHERE idUsuario = ?";
sqlsrv_query($conn, $sql_update_visualizada, array($idUsuario));

// Busca notificações
// A lógica para idPublicacaoAssociada tenta inferir o ID do post/comentário
// da mensagem, o que pode não ser robusto para todos os cenários.
// Uma solução mais robusta exigiria a adição de uma coluna idPublicacao ou idComentario
// na tblNotificacao.
$sql = "SELECT n.*
        FROM tblNotificacao n
        WHERE n.idUsuario = ?
        AND (n.tipoNotificacao = ? OR n.tipoNotificacao = ? OR n.tipoNotificacao = ? OR n.tipoNotificacao = ?)
        ORDER BY n.dataNotificacao DESC";


$params = array(
    $idUsuario,
    NOTIFICACAO_CURTIDA_POST,
    NOTIFICACAO_CURTIDA_COMENTARIO,
    NOTIFICACAO_COMENTARIO,
    NOTIFICACAO_SEGUIMENTO
);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Erro ao buscar notificações: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Versami</title>
    <link rel="stylesheet" href="test/styleNotificacoes.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="content">
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
                        <li onclick="location.href='notificacao.php'" class="active">
                            <div class="notification-icon-container">
                                <i class="fa-solid fa-bell"></i>
                            <?php
                                $total_notificacoes = contarNotificacoesNaoLidas($conn, $_SESSION["usuario_id"]);
                            if ($total_notificacoes > 0): ?>
                                <span class="notification-badge"><?= $total_notificacoes ?></span>
                            <?php endif; ?>
                            </div>
                                Notificações
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
            <div class="user">
                <h2>Notificações</h2>
                <div class="notificacoes-list">
                    <?php if (sqlsrv_has_rows($stmt)): ?>
                        <?php while ($notificacao = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                            $redirect_url = '#'; // Default fallback
                            $actor_name_from_message = explode(' ', $notificacao['mensagem'])[0]; // Extrai o primeiro nome da mensagem
                            $actor_photo_url = 'Assets/padrao.png'; // Imagem padrão para o ator

                            // Tentar obter o ID do ator da ação para buscar a foto de perfil
                            $actor_id_from_action = null;
                            if ($notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_POST) {
                                $sql_actor = "SELECT TLP.idUsuario, P.idPublicacao FROM tblLikesPorPost TLP JOIN tblPublicacao P ON TLP.idPublicacao = P.idPublicacao WHERE P.idUsuario = ? AND TLP.idPublicacao = (SELECT TOP 1 idPublicacao FROM tblPublicacao WHERE idUsuario = ? ORDER BY dataPublic DESC)";
                                $params_actor = array($idUsuario, $idUsuario);
                                $stmt_actor = sqlsrv_query($conn, $sql_actor, $params_actor);
                                if ($stmt_actor && $row_actor = sqlsrv_fetch_array($stmt_actor, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id_from_action = $row_actor['idUsuario'];
                                    $redirect_url = 'post_details.php?id=' . $row_actor['idPublicacao'];
                                }
                            } elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_COMENTARIO) {
                                $sql_actor = "SELECT TLC.idUsuario, C.idPublicacao FROM tblLikesPorComentario TLC JOIN tblComentario C ON TLC.idComentario = C.idComentario WHERE C.idUsuario = ? AND TLC.idComentario = (SELECT TOP 1 idComentario FROM tblComentario WHERE idUsuario = ? ORDER BY data_coment DESC)";
                                $params_actor = array($idUsuario, $idUsuario);
                                $stmt_actor = sqlsrv_query($conn, $sql_actor, $params_actor);
                                if ($stmt_actor && $row_actor = sqlsrv_fetch_array($stmt_actor, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id_from_action = $row_actor['idUsuario'];
                                    $redirect_url = 'post_details.php?id=' . $row_actor['idPublicacao'];
                                }
                            } elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_COMENTARIO) {
                                $sql_actor = "SELECT C.idUsuario, C.idPublicacao FROM tblComentario C WHERE C.idPublicacao = (SELECT TOP 1 idPublicacao FROM tblPublicacao WHERE idUsuario = ? ORDER BY dataPublic DESC) AND C.idUsuario = (SELECT TOP 1 idUsuario FROM tblComentario WHERE idPublicacao = (SELECT TOP 1 idPublicacao FROM tblPublicacao WHERE idUsuario = ? ORDER BY dataPublic DESC) ORDER BY data_coment DESC)";
                                $params_actor = array($idUsuario, $idUsuario);
                                $stmt_actor = sqlsrv_query($conn, $sql_actor, $params_actor);
                                if ($stmt_actor && $row_actor = sqlsrv_fetch_array($stmt_actor, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id_from_action = $row_actor['idUsuario'];
                                    $redirect_url = 'post_details.php?id=' . $row_actor['idPublicacao'];
                                }
                            } elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_SEGUIMENTO) {
                                $sql_actor = "SELECT idSeguidor FROM tblSeguidores WHERE idSeguido = ? AND idSeguidor = (SELECT TOP 1 idSeguidor FROM tblSeguidores WHERE idSeguido = ? ORDER BY idSeguidor DESC)";
                                $params_actor = array($idUsuario, $idUsuario);
                                $stmt_actor = sqlsrv_query($conn, $sql_actor, $params_actor);
                                if ($stmt_actor && $row_actor = sqlsrv_fetch_array($stmt_actor, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id_from_action = $row_actor['idSeguidor'];
                                    $redirect_url = 'profile_view.php?id=' . $row_actor['idSeguidor'];
                                }
                            }

                            // Se o ID do ator foi encontrado, buscar a foto de perfil
                            if ($actor_id_from_action) {
                                $sql_actor_photo = "SELECT fotoUsuario FROM tblUsuario WHERE idUsuario = ?";
                                $stmt_actor_photo = sqlsrv_query($conn, $sql_actor_photo, array($actor_id_from_action));
                                if ($stmt_actor_photo && $row_actor_photo = sqlsrv_fetch_array($stmt_actor_photo, SQLSRV_FETCH_ASSOC)) {
                                    $actor_photo_url = displayImage($row_actor_photo['fotoUsuario']);
                                }
                            }
                            ?>
                            <div class="notificacao"
                                onclick="window.location.href='<?= htmlspecialchars($redirect_url) ?>'">
                                <div class="notificacao-icon">
                                    <?php if ($notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_POST || $notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_COMENTARIO): ?>
                                        <i class="fas fa-heart" style="color: #ff4d4d;"></i>
                                    <?php elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_COMENTARIO): ?>
                                        <i class="fas fa-comment" style="color: #4d94ff;"></i>
                                    <?php elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_SEGUIMENTO): ?>
                                        <i class="fas fa-user-plus" style="color: #4dff88;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="notificacao-user-photo">
                                    <img src="<?= $actor_photo_url ?>" alt="Foto de perfil">
                                </div>
                                <div class="notificacao-content">
                                    <p><strong><?= htmlspecialchars($actor_name_from_message) ?></strong>
                                    <?= htmlspecialchars(substr($notificacao['mensagem'], strlen($actor_name_from_message) + 1)) ?></p>
                                    <span class="notificacao-time">
                                        <?= $notificacao['dataNotificacao']->format('d/m/Y H:i') ?>
                                    </span>
                                </div>
                                <i class="fas fa-chevron-right notificacao-arrow"></i>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="sem-notificacoes">Você não tem notificações</p>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/script-tema.js"></script>
</body>
</html>