<?php
session_start();
include 'config.php'; // Inclui o arquivo de configuração

header('Content-Type: application/json'); // Garante que a resposta seja JSON

$post_id = $_POST["idPublicacao"] ?? null;

// Verifica se o ID do post foi fornecido
if (!$post_id) {
    echo json_encode(["success" => false, "error" => "ID da publicação não fornecido."]);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "error" => "Usuário não autenticado."]);
    exit;
}

$idUsuarioLogado = $_SESSION["usuario_id"];

try {
    // 1. Verifica se o usuário logado é realmente o dono do post
    $sql_verifica = "SELECT 1 FROM tblPublicacao 
                     WHERE idPublicacao = ? AND idUsuario = ?";
    $params_verifica = array($post_id, $idUsuarioLogado);
    $stmt_verifica = sqlsrv_query($conn, $sql_verifica, $params_verifica);

    if ($stmt_verifica === false) {
        error_log("Erro no SELECT de verificação de post em excluir_post.php: " . print_r(sqlsrv_errors(), true));
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "error" => "Erro ao verificar permissões da publicação."]);
        exit;
    }

    if (sqlsrv_has_rows($stmt_verifica)) {
        // O usuário é o dono do post, procede com a exclusão.

        // **PASSO 1: Excluir denúncias relacionadas**
        $sql_delete_denuncias = "DELETE FROM tblDenuncia WHERE idPublicacao = ?";
        $params_delete_denuncias = array($post_id);
        $stmt_delete_denuncias = sqlsrv_query($conn, $sql_delete_denuncias, $params_delete_denuncias);

        if ($stmt_delete_denuncias === false) {
            $errors = sqlsrv_errors();
            error_log("Erro ao excluir denúncias relacionadas em excluir_post.php: " . print_r($errors, true));
            error_log("SQL Query Delete Denuncias: " . $sql_delete_denuncias);
            error_log("SQL Params Delete Denuncias: " . print_r($params_delete_denuncias, true));
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro ao excluir denúncias relacionadas."]);
            exit;
        }

        // **PASSO 2: Excluir likes relacionados ao post**
        $sql_delete_likes_post = "DELETE FROM tblLikesPorPost WHERE idPublicacao = ?";
        $params_delete_likes_post = array($post_id);
        $stmt_delete_likes_post = sqlsrv_query($conn, $sql_delete_likes_post, $params_delete_likes_post);

        if ($stmt_delete_likes_post === false) {
            $errors = sqlsrv_errors();
            error_log("Erro ao excluir likes relacionados ao post em excluir_post.php: " . print_r($errors, true));
            error_log("SQL Query Delete Likes Post: " . $sql_delete_likes_post);
            error_log("SQL Params Delete Likes Post: " . print_r($params_delete_likes_post, true));
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro ao excluir likes relacionados ao post."]);
            exit;
        }
        
        // **PASSO 3: Excluir comentários e seus likes (se não houver ON DELETE CASCADE para likes de comentário)**
        // Primeiro, encontrar e deletar likes dos comentários desse post
        $sql_delete_likes_comentarios = "DELETE FROM tblLikesPorComentario WHERE idComentario IN (SELECT idComentario FROM tblComentario WHERE idPublicacao = ?)";
        $params_delete_likes_comentarios = array($post_id);
        $stmt_delete_likes_comentarios = sqlsrv_query($conn, $sql_delete_likes_comentarios, $params_delete_likes_comentarios);

        if ($stmt_delete_likes_comentarios === false) {
            $errors = sqlsrv_errors();
            error_log("Erro ao excluir likes de comentários relacionados em excluir_post.php: " . print_r($errors, true));
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro ao excluir likes de comentários relacionados."]);
            exit;
        }

        // Depois, excluir os comentários do post
        $sql_delete_comentarios = "DELETE FROM tblComentario WHERE idPublicacao = ?";
        $params_delete_comentarios = array($post_id);
        $stmt_delete_comentarios = sqlsrv_query($conn, $sql_delete_comentarios, $params_delete_comentarios);

        if ($stmt_delete_comentarios === false) {
            $errors = sqlsrv_errors();
            error_log("Erro ao excluir comentários relacionados em excluir_post.php: " . print_r($errors, true));
            error_log("SQL Query Delete Comentarios: " . $sql_delete_comentarios);
            error_log("SQL Params Delete Comentarios: " . print_r($params_delete_comentarios, true));
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro ao excluir comentários relacionados."]);
            exit;
        }

        // **PASSO 4: Finalmente, excluir a publicação principal**
        $sql_delete_post = "DELETE FROM tblPublicacao WHERE idPublicacao = ?";
        $params_delete_post = array($post_id);
        $stmt_delete_post = sqlsrv_query($conn, $sql_delete_post, $params_delete_post);

        if ($stmt_delete_post === false) {
            $errors = sqlsrv_errors();
            error_log("Erro ao excluir publicação principal em excluir_post.php: " . print_r($errors, true));
            error_log("SQL Query Delete Post: " . $sql_delete_post);
            error_log("SQL Params Delete Post: " . print_r($params_delete_post, true));
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro ao excluir a publicação principal."]);
            exit;
        }

        echo json_encode(["success" => true, "message" => "Publicação e dados relacionados excluídos com sucesso!"]);
        exit;

    } else {
        // Se o usuário não é o dono do post
        http_response_code(403); // Forbidden
        echo json_encode(["success" => false, "error" => "Acesso negado. Você não tem permissão para excluir esta publicação."]);
        exit;
    }

} catch (Exception $e) {
    error_log("Exceção inesperada em excluir_post.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Ocorreu um erro inesperado: " . $e->getMessage()]);
    exit;
}
?>