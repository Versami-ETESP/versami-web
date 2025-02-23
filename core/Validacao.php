<?php
include_once 'Conexao.php';
class Validacao
{

    // nessa classe nao vou declarar construtor pois não preciso de dados especificos para instanciar

    // Os métodos dessa classe são estaticos para poderem ser chamados sem ser instanciados

    public static function nameValidation($name)
    {

        if (strlen($name) < 3) {
            return false;
        } else {
            return true;
        }
    } // recebe uma string e retorna 'true' se a string tiver mais de 3 letras e 'false' se for menor

    public static function emailValidation($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    } // recebe uma string e atraves do metodo do php filter_var e o parametro FILTER_VALIDATE_EMAIL define se essa string é um email válido e retorna 'true' ou 'false'

    public static function passValidation($pass)
    {
        if (strlen($pass) < 8) {
            return false;
        } else {
            return true;
        } // recebe uma string e retorna 'true' se ela tiver 8 ou mais caracteres e 'false' se não

    }

    public static function birthValidation($birth)
    {
        $userBirth = new DateTime($birth);
        $today = new DateTime();

        $interval = $today->diff($userBirth);

        if ($interval->y < 13) {
            return false;
        } else {
            return true;
        }
    } // recebe uma data, cria dois objetos do tipo DateTime e usa o metodo diff() para calcular a diferença entre as datas. Caso seja menor que 13 retorna 'false'


    public static function passConfirm($pass, $confirm)
    {

        if ($confirm != $pass) {
            $result = false;
        } else {
            $result = true;
        }

    } // verifica se senha e confirmaçao são iguais e retorna 'false' se nao forem iguais

    public static function userIsUnique($user, $table, $pdo)
    {

        $sql = $pdo->prepare("SELECT arroba_usuario FROM $table  WHERE arroba_usuario = :login;");
        $sql->bindValue(":login", $user);
        $sql->execute();

        $consulta = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (count($consulta) > 0) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;

    } // recebe o usuario, a tabela para procurar e um objeto pdo. Realiza a consulta no sql e retorna 'true' se encontrar um alguma ocorrencia de usuario com esse nome

}