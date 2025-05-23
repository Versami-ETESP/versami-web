<?php
include 'config.php';

$usuarios = [];
$busca = $_GET['buscar_usuario'] ?? '';

if (!$conn) {
    die("Erro de conexão: " . print_r(sqlsrv_errors(), true));
}

if (!empty($busca)) {
    // Busca no novo banco (tblUsuario)
    $sql_busca = "SELECT idUsuario, nome, arroba_usuario, fotoUsuario, fotoCapa
                 FROM tblUsuario 
                 WHERE nome LIKE ? OR arroba_usuario LIKE ?";
    $params_busca = array("%$busca%", "%$busca%");
    $result_busca = sqlsrv_query($conn, $sql_busca, $params_busca);

    if ($result_busca === false) {
        die("Erro ao buscar usuários: " . print_r(sqlsrv_errors(), true));
    }

    while ($usuario = sqlsrv_fetch_array($result_busca, SQLSRV_FETCH_ASSOC)) {
        $usuarios[] = $usuario;
    }

    // Exibe os resultados (HTML mantido igual)
    if (!empty($usuarios)) {
        foreach ($usuarios as $usuario) {
            echo '<div class="usuario">';
            echo '<a href="profile_view.php?id=' . $usuario['idUsuario'] . '">';
            echo '<div class="foto-capa">';
            echo '<img src="' . $usuario['fotoCapa'] . '" alt="Foto de capa" width="50" class="imgfotoCapa">';
            echo '</div>';
            echo '<img src="' . $usuario['fotoUsuario'] . '" alt="Foto de perfil" width="50">';
            echo '<p>' . htmlspecialchars($usuario['nome']) .'</p>';
            echo '<p>' . '@' . htmlspecialchars($usuario['arroba_usuario']) . '</p>';
            echo '</a>';
            echo '</div>';
        }
    } else {
        echo '<p>Nenhum usuário encontrado.</p>';
    }
}
?>