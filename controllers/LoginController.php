<?php
require_once '../core/Conexao.php';
require_once '../core/Validacao.php';
require_once '../models/Usuario.php';

class LoginController
{
    private $pdo = null;

    public function __construct()
    {
        $connect = new Conexao();
        $this->pdo = $connect->connectDB();
    }

    public function login($user, $pass)
    {

        $table = "tblUsuario";

        if (empty($user) || empty($pass)) {
            $result = [false, "Necessário preencher todos os campos"];
            die(json_encode($result));
        }

        try {

            $sql = $this->pdo->prepare("SELECT * FROM $table WHERE arroba_usuario = :login AND senha = :senha ;");
            $sql->bindValue(":login", $user);
            $sql->bindValue(":senha", $pass);
            $sql->execute();

            $consulta = $sql->fetch(PDO::FETCH_ASSOC);



            if ($consulta) {

                $registerUser = new Usuario(
                    $consulta['nome'],
                    $consulta['arroba_usuario'],
                    $consulta['email'],
                    $consulta['senha'],
                    $consulta['data_nasc']
                );

                $_SESSION = $registerUser;

                $result = [true, $consulta['nome'], $consulta['arroba_usuario'], $consulta['fotoUsuario'], $consulta['fotoCapa']];
                die(json_encode($result));
            } else {
                $result = [false, "Usuário ou senha inválido"];
                die(json_encode($result));
            }

        } catch (PDOException $e) {
            $result = [false, "Erro de conexão, tente novamente!"];
            die(json_encode($result));
        }
    }

    /*
     * Esse método verifica se os campos de login e senha não estão vazios, caso estejam retornam uma mensagem de erro.
     * Caso esteja com os dados, ele ira tentar extrair da tabela tblUsuario todas as informações referentes ao login e senha informados.
     * Se nao localizar nenhuma ocorrencia no BD, irá retornar a mensagem de login ou senha incorretos.
     * Se localizar, ira criar um objeto do tipo Usuario e enviar para o session do PHP e enviar alguns dados do usuario via JSON para o JS.
     */
}