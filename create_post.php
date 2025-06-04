<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Usuário não autenticado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['user_id'];
    $texto = isset($_POST['texto']) ? trim($_POST['texto']) : null;
    $imagem = isset($_FILES['imagem']) ? $_FILES['imagem']['name'] : null;
    
    if ($imagem) {
        $uploadDir = "uploads/";
        $uploadFile = $uploadDir . basename($_FILES['imagem']['name']);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadFile);
    }

    // Buscar informações do usuário
    $sql_usuario = "SELECT fullname, profile_pic FROM users WHERE id = ?";
    $stmt_usuario = sqlsrv_query($conn, $sql_usuario, array($usuario_id));
    $usuario = sqlsrv_fetch_array($stmt_usuario, SQLSRV_FETCH_ASSOC);

    if ($usuario) {
        $nome_usuario = $usuario['fullname'];
        $foto_perfil = $usuario['profile_pic'];
    } else {
        echo json_encode(["success" => false, "error" => "Erro ao buscar informações do usuário"]);
        exit();
    }

    // Inserir o post no banco de dados
    $sql = "INSERT INTO Posts (usuario_id, texto, imagem, nome_usuario, foto_perfil, data_postagem) VALUES (?, ?, ?, ?, ?, GETDATE())";
    $params = array($usuario_id, $texto, $imagem, $nome_usuario, $foto_perfil);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo json_encode(["success" => true, "message" => "Post criado com sucesso"]);
    } else {
        echo json_encode(["success" => false, "error" => print_r(sqlsrv_errors(), true)]);
    }
}
?>