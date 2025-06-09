<?php
session_start();
include '../config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

if (!isset($_POST['book_id']) || !isset($_POST['action'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$bookId = $_POST['book_id'];
$userId = $_SESSION["usuario_id"];
$action = $_POST['action'];

if ($action === 'add') {
    $sql = "INSERT INTO tblLivrosFavoritos (idUsuario, idLivro) VALUES (?, ?)";
} else {
    $sql = "DELETE FROM tblLivrosFavoritos WHERE idUsuario = ? AND idLivro = ?";
}

$params = array($userId, $bookId);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(['success' => true]);
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => print_r(sqlsrv_errors(), true)]);
}
?>