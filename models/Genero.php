<?php
class Genero{
    private $id;
    private $type;
    private $description;

    public function __construct(){

    }
    
    public function getId() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setType($type): void {
        $this->type = $type;
    }

    public function setDescription($description): void {
        $this->description = $description;
    }


}