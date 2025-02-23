<?php
require_once __DIR__ . '/Perfil.php';
class Admin extends Perfil{

    private $admPermission;

    public function __construct($name, $login, $email, $pass, $permission){
        parent::__construct($name, $login, $email, $pass);
        $this->setAdmPermission($permission);
    }

    public function getAdmPermission(){
        return $this->admPermission;
    }

    public function setAdmPermission($permission){
        $this->admPermission = $permission;
    }
}