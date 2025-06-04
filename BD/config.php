<?php
// Configuração do banco de dados
$serverName = "DESKTOP-REJT7MF\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "VersamiDB",
    "Uid" => "sa",
    "PWD" => "12121515"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("Erro na conexão com o banco de dados: " . print_r(sqlsrv_errors(), true));
}
?>
