<?php

class Conexao{

    private $localServer = "YGOR\SQLEXPRESS";
    private $dataBase = "versami";
    private $userServer = "sa";
    private $serverPass = "Tc2088275";
    private $connect;

    public function connectDB(){

        try{
            $this->connect = new PDO("sqlsrv:server=$this->localServer; database=$this->dataBase",$this->userServer,$this->serverPass);

        }catch(PDOException $e){

            error_log("Erro de conexão " . $e->getMessage());

        }

        return $this->connect;
    }

    public function disconnectDB(){
        $this->connect = null;
    }
}