<?php

include_once 'Perfil.php';
class Usuario extends Perfil
{

    // Declaração de atributos

    private $userBirth;
    private $userImage;
    private $userBio;

    private $userPosts = array(); // esse array armazena no todos os objetos posts relacionados ao usuario

    public function __construct($name, $login, $email, $pass, $birth)
    {
        parent::__construct($name, $login, $email, $pass);
        $this->setUserBirth($birth);
    }

    // Declaração de métodos

    // metodos getters e setters

    public function getUserBirth()
    {
        return $this->userBirth;
    }

    public function setUserBirth($birth)
    {
        $this->userBirth = $birth;
    }

    public function getUserImage()
    {
        return $this->userImage;
    }

    public function setUserImage($image)
    {
        $this->userImage = $image;
    }

    public function getUserBio()
    {
        return $this->userBio;
    }

    public function setUserBio($bio)
    {
        $this->userBio = $bio;
    }

}