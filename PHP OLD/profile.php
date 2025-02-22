<?php
session_start(); //inicia a sessão para resgatar os dados do usuário que foram consultados na tela de login
include '../../PHP/util/Conexao.php';

if($_SESSION){
    try{
        $tabela = "tblPublicacao";
        $user = $_SESSION['id']; // resgata o id do usuario para fazer a consulta do post
        $qtd = isset($_POST['qtd']) ? (int) $_POST['qtd'] :0; // operador ternario que define um valor para os limites 
        $limite = isset($_POST['limite']) ? (int) $_POST['limite'] : 5; // operador ternario que define um valor para os limites

        $sql = $pdo->prepare("SELECT * FROM $tabela WHERE usuario = :user ORDER BY idPublicacao DESC OFFSET :qtd ROWS FETCH NEXT :limite ROWS ONLY;"); // seleciona os posts do usuario e limita a quantidade de post por requisição, para carregar aos poucos na pagina
        $sql->bindValue(":user",$user);
        $sql->bindValue(":limite",$limite, PDO::PARAM_INT); // PDO::PARAM_INT define que o parametro :limite seja lido no sql com inteiro
        $sql->bindValue(":qtd",$qtd, PDO::PARAM_INT);
        $sql->execute();

        $consulta = $sql->fetchAll(PDO::FETCH_ASSOC); // salva em um vetor as postagens do usuario

        if(count($consulta) > 0){
            $result = $consulta; // envia esse vetor para o js
            die(json_encode($result));
        } else {
            $result = []; // envia a informação caso o usuario nao tenha postagens salvas na tabel tblPublicacao
            die(json_encode($result));
        }
    }catch(PDOException $e){
        die(json_encode($e->getMessage())); //tratamento de erro em caso de erro na conexão com o BD
    }
}