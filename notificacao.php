<?php
session_start();
require_once 'conexao.php';

// Definições dos tipos de notificação
define('NOTIFICACAO_CURTIDA_POST', 1);
define('NOTIFICACAO_COMENTARIO', 2);
define('NOTIFICACAO_SEGUIMENTO', 3);

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION['idUsuario'];

$sql = "SELECT n.*, 
        u.nome, 
        u.arroba_usuario, 
        u.foto_perfil
        FROM tblNotificacao n
        JOIN tblUsuario u ON n.idAdmin = u.idUsuario
        WHERE n.idUsuario = ? 
        AND (n.tipoNotificacao = ? OR n.tipoNotificacao = ? OR n.tipoNotificacao = ?)
        ORDER BY n.dataNotificacao DESC";

$params = array($idUsuario, NOTIFICACAO_CURTIDA_POST, NOTIFICACAO_COMENTARIO, NOTIFICACAO_SEGUIMENTO);
$stmt = sqlsrv_query($conn, $sql, $params);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Versami</title>
    <link rel="stylesheet" href="test/style_notificacoes.css">
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="content">
        <div class="header-menu">
            <!-- Barra de navegação -->
            <div id="sidebar">
                <div class="top-content-sidebar">
                    <img src="Assets/logoVersamiBlue.png" alt="Versami" />
                    <ul>
                        <li onclick="location.href='feed.php'" class="active">
                            <i class="fa-solid fa-house"></i> Home
                        </li>
                        <li onclick="location.href='explorar.php'">
                            <i class="fa-solid fa-magnifying-glass"></i> Explore
                        </li>
                        <li onclick="location.href='blog_usuarios.php'">
                            <i class="fa-solid fa-newspaper"></i> Blog
                        </li>
                        <li onclick="location.href='notificacao.php'">
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
                        <li onclick="location.href='cadastrar_livro.php'">
                            <i class="fa-solid fa-newspaper"></i> Cadastrar Livro
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
                    <?php if (sqlsrv_has_rows($result)): ?>
                        <?php while ($notificacao = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
                            <div class="notificacao <?= $notificacao['visualizada'] ? '' : 'nova' ?>"
                                onclick="window.location.href='<?= 
                                    $notificacao['tipoNotificacao'] == NOTIFICACAO_SEGUIMENTO ? 
                                    'profile_view.php?id=' . $notificacao['idRemetente'] : 
                                    'post_details.php?id=' . $notificacao['idPublicacao']
                                ?>'">
                                <div class="notificacao-icon">
                                    <?php if ($notificacao['tipoNotificacao'] == NOTIFICACAO_CURTIDA_POST): ?>
                                        <i class="fas fa-heart" style="color: #ff4d4d;"></i>
                                    <?php elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_COMENTARIO): ?>
                                        <i class="fas fa-comment" style="color: #4d94ff;"></i>
                                    <?php elseif ($notificacao['tipoNotificacao'] == NOTIFICACAO_SEGUIMENTO): ?>
                                        <i class="fas fa-user-plus" style="color: #4dff88;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="notificacao-user-photo">
                                    <?php if (!empty($notificacao['fotoRemetente'])): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($notificacao['fotoRemetente']) ?>" 
                                             alt="<?= htmlspecialchars($notificacao['nomeRemetente']) ?>">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" style="font-size: 40px; color: #ccc;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="notificacao-content">
                                    <p><strong><?= htmlspecialchars($notificacao['nomeRemetente']) ?></strong> 
                                    <?= htmlspecialchars($notificacao['mensagem']) ?></p>
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
</body>
</html>