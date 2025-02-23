<?php

class Autor{
    private $id;
    private $name;
    private $descriprion;

    public function __construct(){
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescriprion() {
        return $this->descriprion;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setName($name): void {
        $this->name = $name;
    }

    public function setDescriprion($descriprion): void {
        $this->descriprion = $descriprion;
    }


}