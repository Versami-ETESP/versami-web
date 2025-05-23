<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Marcar notificações como visualizadas
$sql_marcar = "UPDATE tblNotificacao SET visualizada = 1 
               WHERE idUsuario = ? AND visualizada = 0";
sqlsrv_query($conn, $sql_marcar, array($_SESSION["usuario_id"]));

// Buscar notificações com tipo
$sql_notificacoes = "SELECT 
    n.idNotificacao, n.mensagem, n.dataNotificacao, n.visualizada,
    tn.descTipo as tipo,
    u.fotoUsuario
FROM tblNotificacao n
JOIN tblTipoNotificacao tn ON n.tipoNotificacao = tn.idTipoNotificacao
LEFT JOIN tblUsuario u ON n.idAdmin IS NULL -- Ajuste conforme sua lógica
WHERE n.idUsuario = ?
ORDER BY n.dataNotificacao DESC";

$result = sqlsrv_query($conn, $sql_notificacoes, array($_SESSION["usuario_id"]));
?>
<!-- HTML atualizado para mostrar ícones diferentes por tipo -->

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Versami</title>
    <link rel="stylesheet" href="css/style_notificacoes.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="content">
        <div class="header-menu">
            <button class="menu-btn" id="menuBtn" onclick="toggleMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
            <div class="sidebar" id="sidebar">
                <div class="top-content-sidebar">
                    <img src="Assets/logoVersamiBlue.png" alt="Versami" />
                    <ul>
                        <li onclick="location.href='feed.php'">
                            <i class="fa-solid fa-house"></i> Home
                        </li>
                        <li onclick="location.href='explorar.php'">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='blogusuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='notificacao.php'" class="active">
                            <i class="fa-solid fa-bell"></i> Notificações
                            <?php
                            // Contar notificações não lidas
                            $sql_count = "SELECT COUNT(*) as total FROM tblNotificacao 
                            WHERE idUsuario = ? AND visualizada = 0";
                            $stmt_count = sqlsrv_query($conn, $sql_count, array($_SESSION["usuario_id"]));
                            $count = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC);
                            if ($count && $count['total'] > 0): ?>
                                <span class="notification-badge"><?= $count['total'] ?></span>
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
        </div>
        <div class="principal-content">
            <div class="user">
                <h2>Notificações</h2>
                <div class="notificacoes-list">
                    <?php if (sqlsrv_has_rows($result)): ?>
                        <?php while ($notificacao = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
                            <div class="notificacao <?= $notificacao['visualizada'] ? '' : 'nova' ?>"
                                onclick="window.location.href='<?= $notificacao['tipoNotificacao'] == 'seguidor' ? 'profile_view.php?id=' . $notificacao['idReferencia'] : 'post_details.php?id=' . $notificacao['idReferencia'] ?>'">
                                <div class="notificacao-icon">
                                    <div class="notificacao-icon-badge">
                                        <?php if ($notificacao['tipoNotificacao'] == 'curtida'): ?>
                                            <i class="fas fa-heart"></i>
                                        <?php elseif ($notificacao['tipoNotificacao'] == 'comentario'): ?>
                                            <i class="fas fa-comment"></i>
                                        <?php elseif ($notificacao['tipoNotificacao'] == 'seguidor'): ?>
                                            <i class="fas fa-user-plus"></i>
                                        <?php endif; ?>
                                    </div>
                                    <img src="<?= htmlspecialchars($notificacao['fotoRemetente']) ?>" alt="Foto do usuário"
                                        class="notificacao-user-photo">
                                </div>
                                <div class="notificacao-content">
                                    <p>
                                        <?php
                                        if ($notificacao['tipoNotificacao'] == 'comentario') {
                                            // Divide a mensagem para destacar o comentário
                                            $partes = explode('comentou:', $notificacao['mensagem']);
                                            echo htmlspecialchars($partes[0] . 'comentou: ');
                                            echo '<strong>' . htmlspecialchars($partes[1]) . '</strong>';
                                        } else {
                                            echo htmlspecialchars($notificacao['mensagem']);
                                        }
                                        ?>
                                    </p>
                                    <span class="notificacao-time">
                                        <?= $notificacao['dataNotificacao']->format('d/m/Y H:i') ?>
                                    </span>
                                </div>
                                <?php if ($notificacao['tipoNotificacao'] == 'seguidor'): ?>
                                    <i class="fas fa-chevron-right notificacao-arrow"></i>
                                <?php endif; ?>
                                <?php if ($notificacao['tipoNotificacao'] == 'curtida' || $notificacao['tipoNotificacao'] == 'comentario'): ?>
                                    <i class="fas fa-chevron-right notificacao-arrow"></i>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="sem-notificacoes">Você não tem notificações</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="popup-overlay">
        <div class="popup">
            <div class="btn-top-content">
                <div class="btn-close-content">
                    <button class="btn-close"><i class="fa-solid fa-x"></i></button>
                </div>
                <h2>Criar Review</h2>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <textarea name="conteudo" maxlength="380" id="review-content" rows="7" cols="7"
                    placeholder="Qual foi seu ultimo livro lído?"></textarea>
                <div id="previewDiv">
                    <div class="contentPreview">
                        <img id="previewImg" src="" alt="Prévia da imagem">
                    </div>
                </div>
                <div class="icons-content">
                    <div class="icons-left-content">
                        <div class="icon-class">
                            <label for="inputImagem" id="idIconeImagem"><i id="iconeImagem"
                                    class="fa-regular fa-image"></i></label>
                            <input type="file" id="inputImagem" name="imagem" onchange="mostrarImagemSelecionada()"><br>
                        </div>
                        <div class="icon-class">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="icon-class">
                            <i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                    <div class="icons-right-content">
                        <input class="btn-submit" type="submit" id="publicarPost" value="Postar">
                    </div>
            </form>
        </div>
        <div id="postMessage"></div>
    </div>
    <script src="js/theme-switcher.js"></script>
</body>

</html>