<?php
include_once __DIR__ . '/Conexao.php';
class Validacao
{
    /**
     * Esta classe nao não possui construtor pois não precisa de dados especificos para instanciar.
     * Os métodos dessa classe são estaticos para poderem ser chamados sem ser instanciados.
     */

    /**
     * 'nameValidation' recebe uma string e retorna 'true' se a string tiver mais de 3 letras e 'false' se for menor
     * @param string $name
     */

    public static function nameValidation($name)
    {

        if (strlen($name) < 3) {
            return false;
        } else {
            return true;
        }
    } 

    /**
     * 'emailValidation' recebe uma string e atraves do metodo do php filter_var e o parametro FILTER_VALIDATE_EMAIL define se essa string é um email válido e retorna 'true' ou 'false'
     * @param string $email
     */

    public static function emailValidation($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    } 

    /**
     * 'passValidation' recebe uma string e retorna 'true' se ela tiver 8 ou mais caracteres e 'false' se não
     * @param string $pass
     */

    public static function passValidation($pass)
    {
        if (strlen($pass) < 8) {
            return false;
        } else {
            return true;
        } 

    }

    /**
     * 'birthValidation' recebe uma data, cria dois objetos do tipo DateTime e usa o metodo diff() para calcular a diferença entre as datas. Caso seja menor que 13 retorna 'false'
     * @param string $birth
     */

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
    } 

    /**
     * 'passConfirm' verifica se senha e confirmaçao são iguais e retorna 'false' se nao forem iguais
     * @param string $pass
     * @param string $confirm
     */

    public static function passConfirm($pass, $confirm)
    {

        if ($confirm != $pass) {
            return false;
        } else {
            return true;
        }

    } 

    /**
     * 'userIsUnique' recebe o usuario, a tabela para procurar e um objeto pdo. Realiza a consulta no sql e retorna 'true' se encontrar um alguma ocorrencia de usuario com esse nome
     * @param string $user
     * @param string $table
     * @param $pdo
     */

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

    } 

}