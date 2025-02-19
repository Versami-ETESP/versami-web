<?php
    class Usuario{

        private $userName;
        private $userID;
        private $userEmail;
        private $userPass;
        private $userBirth;
        private $userImage;
        private $userBio;


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