<?php
    class Usuario{

        // Declaração de atributos

        private $userName;
        private $userID;
        private $userEmail;
        private $userPass;
        private $userBirth;
        private $userImage;
        private $userBio;

        private $userPosts = array(); // esse array armazena no todos os objetos posts relacionados ao usuario

        // Construtor da classe - será necessário definir os intens: nome, user, email, id, nenha e nascimento para criar o objeto usuario

        public function __construct($name, $id, $email, $pass, $birth){

            $this->setUserName(name);
            $this->setUserID(id);
            $this->setUserEmail(email);
            $this->setUserPass(pass);
            $this->setUserBirth(birth);
        }

        // Declaração de métodos

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

        public function getUserBirth(){
            return $this->userBirth;
        }

        public function setUserBirth($birth){
            $this->userBirth = $birth;
        }

        public function getUserImage(){
            return $this->userImage;
        }

        public function setUserImage($image){
            $this->userImage = $image;
        }

        public function getUserBio(){
            return $this->userBio;
        }

        public function setUserBio($bio){
            $this->userBio = $bio;
        }

    }