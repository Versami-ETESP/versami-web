<?php

require_once '../core/Conexao.php';
require_once '../core/Validacao.php';
class RegistroController
{

    private $pdo = null;

    public function __construct()
    {
        $connect = new Conexao();
        $this->pdo = $connect->connectDB();
    }

    public function inputValidate($input, $value)
    {

        switch ($input) {
            case "#nome":
                $result = Validacao::nameValidation($value);
                die(json_encode($result));
            case "#email":
                $result = Validacao::emailValidation($value);
                die(json_encode($result));
            case "#senha":
                $result = Validacao::passValidation($value);
                die(json_encode($result));
            case "#nasc":
                $result = Validacao::birthValidation($value);
                die(json_encode($result));
        }

    } // verifica se o input do usuario esta correto e devolve para o javascript mostrar para o usuario

    public function register($name, $user, $pass, $confirm, $email, $birth){

        $table = "tblUsuario";

        if (empty($name) || empty($user) || empty($email) || empty($birth) || empty($pass) || empty($confirm)) {
            $msg = "Necessário preencher todos os campos para o cadastro";
            die(json_encode($msg));
        }

        if (!Validacao::nameValidation($name) || !Validacao::emailValidation($email) || !Validacao::passValidation($pass) || !Validacao::birthValidation($birth) || !Validacao::userIsUnique($user, $table, $this->pdo)) {
            $msg = "Preencha todos os campos corretamente";
            die(json_encode($msg));
        }

        try {
            $sql = $this->pdo->prepare("insert into " . $table . "(nome,data_nasc,email,senha,arroba_usuario) values (:nome, :data, :email, :senha, :arroba);");

            $sql->bindValue(":nome", $name);
            $sql->bindValue(":data", $birth);
            $sql->bindValue(":email", $email);
            $sql->bindValue(":senha", $pass);
            $sql->bindValue(":arroba", $user);


            $sql->execute();

            $msg = "Usuario cadastrado com sucesso!";

            die(json_encode($msg));

        } catch (PDOException $e) {

            error_log("Erro de conexão " . $e->getMessage());
            $msg = "Erro ao cadastrar usuário. Tente novamente mais tarde.";
            die(json_encode($msg));

        }

    }

    /*
     * Esse método verifica se o campo input não esta vazio, depois faz a validação dos dados do usuario atraves dos metodos da classe Validacao.
     * Caso Não haja nenhuma dessas inconsistencias nos dados, havera uma tentativa de incluir os dados do usuario no banco de dados.
     * Caso haja algum erro com a conexão do banco de dados, ele retornará uma mensagem de erro ao cadastrar o usuario
     */
}