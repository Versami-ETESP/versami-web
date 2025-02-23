<?php
require_once __DIR__ . '/../core/Conexao.php';
require_once __DIR__ . '/../core/Validacao.php';
require_once __DIR__ . '/../models/Usuario.php';

class LoginController
{
    private $pdo = null;

    public function __construct()
    {
        $connect = new Conexao();
        $this->pdo = $connect->connectDB();
    }

    /**
     * O método 'login' verifica se os campos de login e senha não estão vazios, caso estejam retornam uma mensagem de erro.
     * Caso esteja com os dados, ele ira tentar extrair da tabela tblUsuario todas as informações referentes ao login e senha informados.
     * Se nao localizar nenhuma ocorrencia no BD, irá retornar a mensagem de login ou senha incorretos.
     * Se localizar, ira criar um array no session do PHP com os dados do usuario para instanciar usuario depois e envia alguns dados do usuario via JSON para o JS.
     * 
     * @param string $user
     * @param string $pass
     */

    public function login($user, $pass)
    {
        session_start();
        $table = "tblUsuario";

        if (empty($user) || empty($pass)) {
           return $result = [false, "Necessário preencher todos os campos"];
        }

        try {

            $sql = $this->pdo->prepare("SELECT * FROM $table WHERE arroba_usuario = :login AND senha = :senha ;");
            $sql->bindValue(":login", $user);
            $sql->bindValue(":senha", $pass);
            $sql->execute();

            $consulta = $sql->fetch(PDO::FETCH_ASSOC);

            if ($consulta) {

                $_SESSION['user'] = [
                    'name' => $consulta['nome'],
                    'id' => $consulta['idUsuario'],
                    'login' => $consulta['arroba_usuario'],
                    'email' => $consulta['email'],
                    'pass' => $consulta['senha'],
                    'birth' => $consulta['data_nasc']
                ];

                return $result = [true, $consulta['nome'], $consulta['arroba_usuario'], $consulta['fotoUsuario'], $consulta['fotoCapa']];
                
            } else {
                return $result = [false, "Credenciais inválidas"];
            }

        } catch (PDOException $e) {
            error_log("Erro de conexão " . $e->getMessage());
            return $result = [false, "Erro de conexão, tente novamente!"];
        }
    }

}