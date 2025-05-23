<?php
include 'config.php';

if(isset($_GET['id'])) {
    $sql = "SELECT 
        b.*,
        a.nome as autor,
        a.fotoUsuario as autorFoto
    FROM tblBlogPost b
    JOIN tblAdmin a ON b.idAdmin = a.idAdmin
    WHERE b.idBlogPost = ?";
    
    $stmt = sqlsrv_query($conn, $sql, array($_GET['id']));
    
    if ($post = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Converter imagem varbinary para exibição
        $imagem = $post['imagem'] ? 
            'data:image/jpeg;base64,'.base64_encode($post['imagem']) : 
            'Assets/blog_placeholder.png';
        
        echo json_encode([
            'titulo' => htmlspecialchars($post['titulo']),
            'conteudo' => nl2br(htmlspecialchars($post['conteudo'])),
            'imagem' => $imagem,
            'autor' => htmlspecialchars($post['autor']),
            'data' => $post['dataPost']->format('d/m/Y')
        ]);
    }
}
?>