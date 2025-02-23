<?php

class Conexao{

    private $localServer = "YGOR\SQLEXPRESS";
    private $dataBase = "versami";
    private $userServer = "sa";
    private $serverPass = "Tc2088275";
    private $connect;

    /**
     * 'connectDB' realiza a conexão com o banco de dados e retorna um objeto do tipo PDO para o solicitante
     */

    public function connectDB(){

        try{
            $this->connect = new PDO("sqlsrv:server=$this->localServer; database=$this->dataBase",$this->userServer,$this->serverPass);

        }catch(PDOException $e){

            error_log("Erro de conexão " . $e->getMessage());

        }

        return $this->connect;
    }

    /**
     * 'disconectDB' insere null no objeto $this->connect para encerrar a conexão com o DB caso necessario
     */

    public function disconnectDB(){
        $this->connect = null;
    }
}