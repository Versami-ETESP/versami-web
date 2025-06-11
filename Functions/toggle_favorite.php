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

try {
    if ($action === 'add') {
        $sql = "INSERT INTO tblLivrosFavoritos (idUsuario, idLivro) VALUES (?, ?)";
    } else {
        $sql = "DELETE FROM tblLivrosFavoritos WHERE idUsuario = ? AND idLivro = ?";
    }

    $params = array($userId, $bookId);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        // Get the updated total favorites count for this book
        $sql_count = "SELECT COUNT(*) as total FROM tblLivrosFavoritos WHERE idLivro = ?";
        $stmt_count = sqlsrv_query($conn, $sql_count, array($bookId));
        $total_favorites = 0;
        if ($stmt_count && $row_count = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC)) {
            $total_favorites = $row_count['total'];
        }

        echo json_encode(['success' => true, 'total_favorites' => $total_favorites, 'action' => $action]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['success' => false, 'error' => print_r(sqlsrv_errors(), true)]);
    }
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>