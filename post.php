<?php
// Configuração do banco de dados
$servername = "DESKTOP-REJT7MF\SQLEXPRESS";
$username = "sa";
$password = "12121515";
$dbname = "VersamiDB"; // Substitua pelo nome do seu banco de dados

// Conectar ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Recebe os dados do post
$data = json_decode(file_get_contents("php://input"));
$text = $data->text;
$media = $data->media; // Pode ser uma URL de GIF ou uma URL de imagem

// Insere no banco de dados
$sql = "INSERT INTO posts (text, media) VALUES ('$text', '$media')";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
