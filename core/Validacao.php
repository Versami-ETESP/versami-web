<?php
    class Validacao{

        private $name;
        private $login;
        private $pass;
        private $passConfirm;
        private $email;
        private $birthDate;

        public function getName(){
            return $this->name;
        }

        public function setname($name){
            $this->name = $name;
        }

        public function getLogin(){
           return $this->login;
        }

        public function setLogin($login){
            $this->login = $login;
        }

        public function getPass(){
            return $this->pass;
        }

        public function setPass($pass){
            $this->pass = $pass;
        }

    }