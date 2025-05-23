<?php
session_start();
include '../../BD/bd-conexao.php';

if(!empty($_POST['login']) && !empty($_POST['senha'])){
    try{
        
        
            $login = $_POST['login'];
            $senha = $_POST['senha'];
    
    
            $tabela = "tblUsuario";
    
            $sql = $pdo->prepare("SELECT * FROM $tabela WHERE arroba_usuario = :login AND senha = :senha ;");
            $sql->bindValue(":login",$login);
            $sql->bindValue(":senha",$senha);
            $sql->execute();
    
            $consulta = $sql->fetch(PDO::FETCH_ASSOC);

    
            if($consulta){
                $_SESSION = array(
                    'id' => $consulta['idUsuario'],
                    'nome' => $consulta['nome'],
                    'login' => $consulta['arroba_usuario']
                );
                
                $result = [true,$consulta['nome'],$consulta['arroba_usuario'],$consulta['fotoUsuario'],$consulta['fotoCapa']];
                die(json_encode($result));
            } else{
                $result = [false,"Usuário ou senha inválido"];
                die(json_encode($result));
            }

       


    }catch(Exception $erro){
        $result = [false,"Erro de conexão, tente novamente!"];
        die(json_encode($result));
    }
}else{
    $result = [false,"Necessário preencher todos os campos"];
    die(json_encode($result));
}
?>