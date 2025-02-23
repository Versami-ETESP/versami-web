<?php

require_once __DIR__ . '/../core/Conexao.php';
require_once __DIR__ . '/../core/Validacao.php';
class RegistroController
{

    private $pdo = null;
    private $table = "tblUsuario";

    public function __construct()
    {
        $connect = new Conexao();
        $this->pdo = $connect->connectDB();
    }

    /*
    'inputValidate' verifica se o input do usuario esta correto e devolve para o javascript mostrar para o usuario
    */

    public function inputValidate($input, $value)
    {

        switch ($input) {
            case "#nome":
                return $result = Validacao::nameValidation($value);

            case "#email":
               return $result = Validacao::emailValidation($value);

            case "#senha":
                return $result = Validacao::passValidation($value);

            case "#nasc":
               return  $result = Validacao::birthValidation($value);
        }

    } 

    /*
        'userLoginUnique' torna pratico o uso do metodo userIsUnique da classe validação
        O retorno será um booleano para saber se o usuario está disponivel
    */

    public function userLoginUnique($login){
        return Validacao::userIsUnique($login,$this->table,$this->pdo);
    }

    /*
     * Esse método verifica se o campo input não esta vazio, depois faz a validação dos dados do usuario atraves dos metodos da classe Validacao.
     * Caso Não haja nenhuma dessas inconsistencias nos dados, havera uma tentativa de incluir os dados do usuario no banco de dados.
     * Caso haja algum erro com a conexão do banco de dados, ele retornará uma mensagem de erro ao cadastrar o usuario
     */

    public function register($name, $user, $pass, $confirm, $email, $birth){


        if (empty($name) || empty($user) || empty($email) || empty($birth) || empty($pass) || empty($confirm)) {
            $msg = "Necessário preencher todos os campos para o cadastro";
            return $msg;
        }

        if (!Validacao::nameValidation($name) || !Validacao::emailValidation($email) || !Validacao::passValidation($pass) || !Validacao::birthValidation($birth) || !Validacao::userIsUnique($user, $this->table, $this->pdo) || !Validacao::passConfirm($pass, $confirm)) {
            $msg = "Preencha todos os campos corretamente";
            return $msg;
        }

        try {
            $sql = $this->pdo->prepare("insert into " . $this->table . "(nome,data_nasc,email,senha,arroba_usuario) values (:nome, :data, :email, :senha, :arroba);");

            $sql->bindValue(":nome", $name);
            $sql->bindValue(":data", $birth);
            $sql->bindValue(":email", $email);
            $sql->bindValue(":senha", $pass);
            $sql->bindValue(":arroba", $user);


            $sql->execute();

            $msg = "Usuario cadastrado com sucesso!";

            return $msg;

        } catch (PDOException $e) {

            error_log("Erro de conexão " . $e->getMessage());
            $msg = "Erro ao cadastrar usuário. Tente novamente mais tarde.";
            return $msg;

        }

    }

}