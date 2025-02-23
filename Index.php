<?php
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Postagem.php';
require_once __DIR__ . '/models/Comentario.php';
require_once __DIR__ . '/core/Validacao.php';
require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/PublicacaoController.php';
require_once __DIR__ . '/controllers/RegistroController.php';

if ($_POST) {

    $tipo = $_POST['tipo'];
    $controller = new RegistroController();
    $controller2 = new LoginController();

    switch($tipo){
        
        case '#valida':
            $input = $_POST['conteudo'];
            $opc = $_POST['usrInput'];
            die(json_encode($controller->inputValidate($opc,$input)));
        case '#confirma':
            $senha = $_POST['senha'];
            $confirma = $_POST['confirma'];
            die(json_encode(Validacao::passConfirm($senha,$confirma)));
        case '#user':
            $user = $_POST['login'];
           die(json_encode($controller->userLoginUnique($user)));
        case '#submit':
            $nome = $_POST['usrNome'];
            $email = $_POST['usrEmail'];
            $login = $_POST['usrLogin'];
            $senha = $_POST['usrSenha'];
            $confirma = $_POST['usrConfirma'];
            $nasc = $_POST['usrNasc'];
            die(json_encode($controller->register($nome,$login,$senha,$confirma,$email,$nasc)));
        case '#login':
            $login = $_POST['login'];
            $senha = $_POST['senha'];
            die(json_encode($controller2->login($login,$senha)));

    }

}