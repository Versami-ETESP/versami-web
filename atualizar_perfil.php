<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bio = $_POST["bio"] ?? '';
    $foto = $_FILES["fotoPerfil"]["tmp_name"] ? 
            file_get_contents($_FILES["fotoPerfil"]["tmp_name"]) : null;
    $capa = $_FILES["fotoCapa"]["tmp_name"] ? 
            file_get_contents($_FILES["fotoCapa"]["tmp_name"]) : null;

    // Atualização condicional
    $sql = "UPDATE tblUsuario SET 
            bio_usuario = ?" . 
            ($foto ? ", fotoUsuario = ?" : "") . 
            ($capa ? ", fotoCapa = ?" : "") . 
            " WHERE idUsuario = ?";
    
    $params = array($bio);
    if ($foto) $params[] = $foto;
    if ($capa) $params[] = $capa;
    $params[] = $_SESSION["usuario_id"];

    if (sqlsrv_query($conn, $sql, $params)) {
        $_SESSION["success"] = "Perfil atualizado!";
    } else {
        $_SESSION["error"] = "Erro ao atualizar.";
    }
}
header("Location: profile.php");
?>