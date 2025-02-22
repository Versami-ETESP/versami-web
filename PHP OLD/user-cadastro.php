<?php

include 'valida-dados.php';
include '../../PHP/util/Conexao.php';



try{
    
    if(isset($_POST)){

        $nome = $_POST['usrNome']; 
        $login = $_POST['usrLogin']; 
        $email = $_POST['usrEmail']; 
        $nasc = $_POST['usrNasc']; 
        $senha = $_POST['usrSenha']; 
        $confirma = $_POST['usrConfirma'];

        if(empty($nome)||empty($login)||empty($email)||empty($nasc)||empty($senha)||empty($confirma)){ 
            $msg = "Necessário preencher todos os campos para o cadastro"; 
            die(json_encode($msg)); 
        }


        $resNome = validaNome($nome);
        $resEmail = validaEmail($email); 
        $resSenha = validaSenha($senha); 
        $resIdade = validaIdade($nasc);

    if (!$resNome || !$resEmail || !$resSenha || !$resIdade){ 
	    $msg = "Preencha todos os campos corretamente";
	    die(json_encode($msg)); 
    }
            
            $tabela = "tblUsuario";

            $sql = $pdo -> prepare("insert into ". $tabela . "(nome,data_nasc,email,senha,arroba_usuario) values (:nome, :data, :email, :senha, :arroba);");

            $sql -> bindValue(":nome",$nome);
            $sql -> bindValue(":data",$nasc);
            $sql -> bindValue(":email",$email);
            $sql -> bindValue(":senha",$senha);
            $sql -> bindValue(":arroba",$login);


            $sql -> execute();

            $msg = "Usuario cadastrado com sucesso!";

            die(json_encode($msg));

    } 
}catch (Exception $erro){
    $msg = "Erro na inclusão dos dados";
    die(json_encode($msg));
}


