<?php

$local_server = "DESKTOP-REJT7MF\SQLEXPRESS";
$banco_de_dados = "versami";
$usuario_server = "sa";
$senha_server = "12121515";

try{
    $pdo = new PDO("sqlsrv:server=$local_server; database=$banco_de_dados",$usuario_server,$senha_server);
    $msg = "Conexão realizada com sucesso!";

} catch (Exception $erro){

    $msg = "Erro na conexão do banco de dados ";
    die(json_encode($msg));
}
    