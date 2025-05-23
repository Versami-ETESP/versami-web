<?php
include '../config.php';

function validaNome($usrNome){

    if(strlen($usrNome) < 3){
        return false;
    }else{
        return true;
    }    
}

function validaEmail($usrEmail){
    if(filter_var($usrEmail, FILTER_VALIDATE_EMAIL)){
        return true;
    } else {
        return false;
    }
}

function validaSenha($usrSenha){
    if(strlen($usrSenha) < 8){
        return false;
    }else{
        return true;
    }
}

function validaIdade($usrIdade){
    $nasc = new DateTime($usrIdade);
    $hoje = new DateTime();

    $intervalo = $hoje -> diff($nasc);

    if($intervalo->y < 13){
        return false;
    }else{
        return true;
    }
}

if(isset($_POST['usrInput']) && isset($_POST['conteudo'])){

    $input = $_POST['usrInput'];
    $valor = $_POST['conteudo'];
   

    switch($input){
        case "#nome":
            $result = validaNome($valor);
            die(json_encode($result));
        case "#email":
            $result = validaEmail($valor);
            die(json_encode($result));
        case "#senha":
            $result = validaSenha($valor);
            die(json_encode($result));
        case "#nasc":
            $result = validaIdade($valor);
            die(json_encode($result));
    }

}


if(isset($_POST['senha']) && isset($_POST['confirma'])){
    $usrSenha = $_POST['senha'];
    $usrConfirma = $_POST['confirma'];

    if ($usrConfirma != $usrSenha){
        $result = false;
    }else{
        $result = true;
    }

    die(json_encode($result));
}


if(isset($_POST['login'])){

    $tabela = "tblUsuario";
    $usrLogin = $_POST['login'];

    $sql = $pdo->prepare("SELECT arroba_usuario FROM $tabela  WHERE arroba_usuario = :login;");
    $sql->bindValue(":login",$usrLogin);
    $sql->execute();

    $consulta = $sql->fetchAll(PDO::FETCH_ASSOC);

    if(count($consulta) > 0){
        $result = false;
    } else {
        $result = true;
    }

    die(json_encode($result));
} 

?>