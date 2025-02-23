<?php
require_once 'Autor.php';
require_once 'Genero.php';

class Livro{
    private $idBook;
    private $title;
    private $summary;
    private $autor;
    private $cover;
    private $genre;

    public function __construct($id, $title, $autor, $genre){

    }

    public function getIdBook() {
        return $this->idBook;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getSummary() {
        return $this->summary;
    }

    public function getAutor() {
        return $this->autor;
    }

    public function getCover() {
        return $this->cover;
    }

    public function getGenre() {
        return $this->genre;
    }

    public function setIdBook($idBook): void {
        $this->idBook = $idBook;
    }

    public function setTitle($title): void {
        $this->title = $title;
    }

    public function setSummary($summary): void {
        $this->summary = $summary;
    }

    public function setAutor($autor): void {
        $this->autor = $autor;
    }

    public function setCover($cover): void {
        $this->cover = $cover;
    }

    public function setGenre($genre): void {
        $this->genre = $genre;
    }

}