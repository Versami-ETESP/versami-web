<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
include '../../config.php'; // Adjust path as needed based on file location

if (!isset($_SESSION["usuario_id"])) {
    // Optionally redirect or return an error if not logged in
    echo "<div class='empty-state'><i class='fa-solid fa-exclamation-triangle'></i><h2>Erro de Autenticação</h2><p>Faça login para visualizar as notícias.</p></div>";
    exit;
}

$post_selecionado = null;
if (isset($_GET['id'])) {
    $sql_post = "SELECT b.*, a.nome AS autor, a.arroba_usuario AS autor_arroba 
                 FROM tblBlogPost b
                 JOIN tblAdmin a ON b.idAdmin = a.idAdmin
                 WHERE b.idBlogPost = ?";
    $params = array($_GET['id']);
    $stmt = sqlsrv_query($conn, $sql_post, $params);

    if ($stmt) {
        $post_selecionado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
}

if ($post_selecionado) {
    // Output only the HTML for the full post content
    ?>
    <h1><?= nl2br(htmlspecialchars(iconv('ISO-8859-1', 'UTF-8//IGNORE', $post_selecionado['titulo']))) ?></h1>
    <p class="meta">
        Publicado em <?= $post_selecionado['dataPost']->format('d/m/Y \à\s H:i') ?>
        por <?= htmlspecialchars($post_selecionado['autor']) ?>
    </p>

    <?php if (!empty($post_selecionado['imgPost'])): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($post_selecionado['imgPost']) ?>"
            alt="<?= htmlspecialchars(mb_convert_encoding($post_selecionado['titulo'], 'UTF-8', 'auto')) ?>">
    <?php else: ?>
        <img src="Assets/blog_placeholder.png" alt="<?= htmlspecialchars($post_selecionado['titulo']) ?>">
    <?php endif; ?>

    <div class="contentBlog ">
         <?= nl2br(transformURLsIntoLinks(mb_convert_encoding($post_selecionado['conteudo'], 'UTF-8', 'ISO-8859-1'))) ?>
    </div>

    <div class="author">
        <div class="author-info">
            <h4><?= htmlspecialchars($post_selecionado['autor']) ?></h4>
            <p>@<?= htmlspecialchars($post_selecionado['autor_arroba']) ?></p>
        </div>
    </div>
    <?php
} else {
    // If no post is selected or found
    ?>
    <div class="empty-state">
        <i class="fa-solid fa-newspaper"></i>
        <h2>Notícia não encontrada</h2>
        <p>A notícia solicitada não pôde ser encontrada. Selecione uma da lista.</p>
    </div>
    <?php
}
?>