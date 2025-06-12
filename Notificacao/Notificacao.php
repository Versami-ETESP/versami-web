<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login/login.php");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

// Atualiza notificações para 'visualizada' = 1 quando a página é acessada
$sql_update_visualizada = "UPDATE tblNotificacao SET visualizada = 1 WHERE idUsuario = ?";
sqlsrv_query($conn, $sql_update_visualizada, array($idUsuario));

// Busca notificações
// A query principal busca apenas os dados da notificação.
// Os dados do remetente (ator) e do item associado (post, comentário)
// serão buscados dentro do loop PHP para cada notificação,
// tentando inferir a partir das tabelas de ação (likes, comentários, seguidores).
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
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../Assets/favicon.png" type="favicon" />
    <link rel="stylesheet" href="CSS/StyleNotificacoes.css">
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
                        <li onclick="location.href='../Explorar/Explorar.php'">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='../Blog/BlogUsuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='Notificacao.php'" class="active">
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
                <h2>Notificações</h2>
                <div class="notificacoes-list">
                    <?php if (sqlsrv_has_rows($stmt)): ?>
                        <?php while ($notificacao = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                            $redirect_url = '#'; // Fallback padrão
                            $actor_name_from_message = ''; // Nome do ator será extraído da mensagem
                            $actor_photo_url = 'Assets/padrao.png'; // Imagem padrão do ator
                    
                            $actor_id = null; // ID do usuário que fez a ação
                            $related_post_id = null; // ID do post relacionado (para redirecionamento)
                    
                            // Tentar inferir o ID do ator e do item relacionado com base no tipo de notificação
                            if ($notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_POST) {
                                // Exemplo de mensagem: "Fulano curtiu sua publicação"
                                $parts = explode(' curtiu sua publicação', $notificacao['mensagem']);
                                if (isset($parts[0])) {
                                    $actor_name_from_message = trim($parts[0]);
                                }

                                // Buscar o ID do usuário que curtiu o post e o ID do post
                                // Esta query é heurística: busca a última curtida de um post do destinatário.
                                // Idealmente, 'tblNotificacao' teria um 'idPublicacao' referenciando o post exato.
                                $sql_actor_info = "SELECT TOP 1 TLP.idUsuario AS actor_id, TLP.idPublicacao AS post_id
                                                   FROM tblLikesPorPost TLP
                                                   JOIN tblPublicacao P ON TLP.idPublicacao = P.idPublicacao
                                                   WHERE P.idUsuario = ? -- O dono do post (destinatário da notificação)
                                                   ORDER BY TLP.dataLike DESC"; // Ordena pelo mais recente
                                $stmt_actor_info = sqlsrv_query($conn, $sql_actor_info, array($notificacao['idUsuario']));
                                if ($stmt_actor_info && $row_actor_info = sqlsrv_fetch_array($stmt_actor_info, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id = $row_actor_info['actor_id'];
                                    $related_post_id = $row_actor_info['post_id'];
                                    $redirect_url = '../Post/PostDetails.php?id=' . $related_post_id;
                                }

                            } elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_COMENTARIO) {
                                // Exemplo de mensagem: "Fulano curtiu seu comentário"
                                $parts = explode(' curtiu seu comentário', $notificacao['mensagem']);
                                if (isset($parts[0])) {
                                    $actor_name_from_message = trim($parts[0]);
                                }

                                // Buscar o ID do usuário que curtiu o comentário e o ID do post do comentário
                                // Heurística: busca a última curtida de comentário feita em um comentário do destinatário.
                                $sql_actor_info = "SELECT TOP 1 TLC.idUsuario AS actor_id, TC.idPublicacao AS post_id
                                                   FROM tblLikesPorComentario TLC
                                                   JOIN tblComentario TC ON TLC.idComentario = TC.idComentario
                                                   WHERE TC.idUsuario = ? -- O dono do comentário (destinatário da notificação)
                                                   ORDER BY TLC.dataLike DESC";
                                $stmt_actor_info = sqlsrv_query($conn, $sql_actor_info, array($notificacao['idUsuario']));
                                if ($stmt_actor_info && $row_actor_info = sqlsrv_fetch_array($stmt_actor_info, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id = $row_actor_info['actor_id'];
                                    $related_post_id = $row_actor_info['post_id'];
                                    $redirect_url = '../Post/PostDetails.php?id=' . $related_post_id; // Redireciona para o post do comentário
                                }

                            } elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_COMENTARIO) {
                                // Exemplo de mensagem: "Fulano comentou: ..."
                                $parts = explode(' comentou: ', $notificacao['mensagem']);
                                if (isset($parts[0])) {
                                    $actor_name_from_message = trim($parts[0]);
                                }

                                // Buscar o ID do usuário que comentou e o ID do post do comentário
                                // Heurística: busca o último comentário feito em um post do destinatário.
                                $sql_actor_info = "SELECT TOP 1 TC.idUsuario AS actor_id, TC.idPublicacao AS post_id
                                                   FROM tblComentario TC
                                                   JOIN tblPublicacao P ON TC.idPublicacao = P.idPublicacao
                                                   WHERE P.idUsuario = ? -- O dono do post (destinatário da notificação)
                                                   ORDER BY TC.data_coment DESC";
                                $stmt_actor_info = sqlsrv_query($conn, $sql_actor_info, array($notificacao['idUsuario']));
                                if ($stmt_actor_info && $row_actor_info = sqlsrv_fetch_array($stmt_actor_info, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id = $row_actor_info['actor_id'];
                                    $related_post_id = $row_actor_info['post_id'];
                                    $redirect_url = '../Post/PostDetails.php?id=' . $related_post_id;
                                }

                            } elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_SEGUIMENTO) {
                                // Exemplo de mensagem: "Fulano começou a te seguir"
                                $parts = explode(' começou a te seguir', $notificacao['mensagem']);
                                if (isset($parts[0])) {
                                    $actor_name_from_message = trim($parts[0]);
                                }

                                // Buscar o ID do usuário que seguiu
                                // Heurística: busca o último seguidor do destinatário.
                                $sql_actor_info = "SELECT TOP 1 idSeguidor AS actor_id FROM tblSeguidores WHERE idSeguido = ? ORDER BY idSeguidor DESC";
                                $stmt_actor_info = sqlsrv_query($conn, $sql_actor_info, array($notificacao['idUsuario']));
                                if ($stmt_actor_info && $row_actor_info = sqlsrv_fetch_array($stmt_actor_info, SQLSRV_FETCH_ASSOC)) {
                                    $actor_id = $row_actor_info['actor_id'];
                                    $redirect_url = '../ProfileView/ProfileView.php?id=' . $row_actor_info['actor_id']; // Redireciona para o perfil do seguidor
                                }
                            }

                            // Fetch actor's photo if actor_id was found
                            if ($actor_id) {
                                $sql_actor_photo = "SELECT fotoUsuario FROM tblUsuario WHERE idUsuario = ?";
                                $stmt_actor_photo = sqlsrv_query($conn, $sql_actor_photo, array($actor_id));
                                if ($stmt_actor_photo && $row_actor_photo = sqlsrv_fetch_array($stmt_actor_photo, SQLSRV_FETCH_ASSOC)) {
                                    $actor_photo_url = displayImage($row_actor_photo['fotoUsuario']);
                                }
                            }
                            ?>
                            <div class="notificacao" onclick="window.location.href='<?= htmlspecialchars($redirect_url) ?>'">
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
                                        <?= htmlspecialchars(substr($notificacao['mensagem'], strlen($actor_name_from_message) + 1)) ?>
                                    </p>
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
    <div id="toastNotification" class="toast-notification"></div>
    <button class="menu-btn" id="menuBtn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="overlay" id="overlay"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
</body>

</html>