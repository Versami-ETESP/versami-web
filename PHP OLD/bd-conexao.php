<?php

$local_server = "YGOR\SQLEXPRESS";
$banco_de_dados = "versami";
$usuario_server = "sa";
$senha_server = "Tc2088275";

try{
    $pdo = new PDO("sqlsrv:server=$local_server; database=$banco_de_dados",$usuario_server,$senha_server);

} catch (Exception $erro){

    $msg = "Erro na conexão do banco de dados ";
    die(json_encode($msg));
}
