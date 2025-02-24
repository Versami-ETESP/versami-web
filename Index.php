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
    $controllerRegistro = new RegistroController();
    $controllerLogin = new LoginController();
    $controllerPublic = new PublicacaoController();

    switch ($tipo) {

        case '#valida':
            $input = $_POST['conteudo'];
            $opc = $_POST['usrInput'];
            die(json_encode($controllerRegistro->inputValidate($opc, $input)));
        case '#confirma':
            $senha = $_POST['senha'];
            $confirma = $_POST['confirma'];
            die(json_encode(Validacao::passConfirm($senha, $confirma)));
        case '#user':
            $user = $_POST['login'];
            die(json_encode($controllerRegistro->userLoginUnique($user)));
        case '#submit':
            $nome = $_POST['usrNome'];
            $email = $_POST['usrEmail'];
            $login = $_POST['usrLogin'];
            $senha = $_POST['usrSenha'];
            $confirma = $_POST['usrConfirma'];
            $nasc = $_POST['usrNasc'];
            die(json_encode($controllerRegistro->register($nome, $login, $senha, $confirma, $email, $nasc)));
        case '#login':
            $login = $_POST['login'];
            $senha = $_POST['senha'];
            die(json_encode($controllerLogin->login($login, $senha)));
        case '#busca':
            session_start();
            $id = $_SESSION['user']['id'];
            $limite = (int) $_POST['limite'];
            $inicio = (int) $_POST['inicio'];
            die(json_encode($controllerPublic->restorePosts($id, $inicio, $limite)));
        case '#public':
            session_start();
            $id = $_SESSION['user']['id'];
            $conteudo = $_POST['conteudo'];
            
            die(json_encode($controllerPublic->sendPost($conteudo,$id)));

    }
}
