<?php
// Configuração do banco de dados
$servername = "DESKTOP-REJT7MF\SQLEXPRESS"; // Substitua pelo nome do servidor do novo banco de dados
$username = "sa"; // Substitua pelo usuário do novo banco de dados
$password = "12121515"; // Substitua pela senha do novo banco de dados
$dbname = "VersamiDB"; // Substitua pelo nome do novo banco de dados

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
$sql = "INSERT INTO Posts (texto, imagem) VALUES ('$text', '$media')"; // Atualize para a nova tabela `Posts`
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>