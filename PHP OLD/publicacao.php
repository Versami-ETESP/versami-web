<?php
session_start();
include '../../PHP/util/Conexao.php';

try{
    if($_POST['conteudo']){

        date_default_timezone_set('America/Sao_Paulo');
        $tabela = 'tblPublicacao';
        $user = $_SESSION['id'];
        $datapub = new DateTime();
        $datastring = $datapub->format('YYYY-MM-DD HH:MM:SS');
        $conteudo = $_POST['conteudo'];
    
        $sql = $pdo->prepare("INSERT INTO $tabela (conteudo, dataPublic, usuario) VALUES (:cont, :datapub, :user);");
    
        $sql->bindValue(":cont",$conteudo);
        $sql->bindValue(":datapub",$datastring);
        $sql->bindValue(":user",$user);
    
        $sql->execute();
    
        $result = true;
        die(json_encode($result));
    } else {
        $result = false;
        die(json_encode($result));
    }
}catch(PDOException $e){
    $result = $e ->getMessage();
    die(json_encode($result));
}