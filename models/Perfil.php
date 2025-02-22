<?php
    class Perfil{

        // Declaração de atributos

        private $userName;
        private $userID;
        private $userEmail;
        private $userPass;

        // Construtor da classe - será necessário definir os intens: nome, user, email, id, nenha

        public function __construct($name, $id, $email, $pass){
            $this->setUserName(name);
            $this->setUserID(id);
            $this->setUserEmail(email);
            $this->setUserPass(pass);
        }

        // metodos getters e setters

        public function getUserName(){
            return $this->userName;
        }

        public function setUserName($name){
            $this->userName = $name;
        }

        public function getUserID(){
            return $this->userID;
        }

        public function setUserID($id){
            $this->userID = $id;
        }

        public function getUserEmail(){
            return $this->userEmail;
        }

        public function setUserEmail($email){
            $this->userEmail = $email;
        }

        public function getUserPass(){
            return $this->userPass;
        }

        public function setUserPass($pass){
            $this->userPass = $pass;
        }
    }