<?php
require_once '../core/Conexao.php';
class PublicacaoController
{
    private $pdo = null;

    public function __construct()
    {
        $connect = new Conexao();
        $this->pdo = $connect->connectDB();
    }

    /**
     * @param int $id
     * @param string $content
     * O metodo 'sendPost' recebe $id e $content e faz um insert no banco de dados na tabela Publicacao.
     * Caso haja algum erro de conexão ele encerra o script
     */

    public function sendPost($content, $id)
    {

        session_start();
        date_default_timezone_set('America/Sao_Paulo');
        $tabela = 'tblPublicacao';

        if (empty($content) || empty($id)) {
            $result = false;
            die(json_encode($result));
        }

        $datapub = new DateTime();

        try {
            $sql = $this->pdo->prepare("INSERT INTO $tabela (conteudo, dataPublic, usuario) VALUES (:cont, :datapub, :user);");

            $sql->bindValue(":cont", $content);
            $sql->bindValue(":datapub", $datapub);
            $sql->bindValue(":user", $id);

            $sql->execute();

            $result = true;
            die(json_encode($result));

        } catch (PDOException $e){
            error_log("Erro de conexão " . $e->getMessage());
        }


    }
}