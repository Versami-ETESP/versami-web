<?php
    class Postagem{

        private $title;
        private $content;
        private $like;
        private $postDate;
        private $book;

        private $comments = array(); // Postagem tem relação com a classe Comentario

        // Contrutor padrão da classe iniciando com valores padrão: like, read, unread, reading. Além disso torna obrigatorio para um post o titulo e o conteudo
        //  instanciei DateTime para definir o horario e data do post

        public function __construct($title, $content){
            $this->setTitle($title);
            $this->setContent($content);
            $this->postDate = new DateTime();
            $this->like = 0;
        }

        // metodos getters e setters

        public function getTitle(){
            return $this->title;
        }

        public function setTitle($title){
            $this->title = $title;
        }

        public function getContent(){
            return $this->content;
        }

        public function setContent($content){
            $this->content = $content;
        }

        public function getLike(){
            return $this->like;
        }

        public function setLike($isLike){
            
            if($isLike){
                $this->like++;
            }elseif (!$isLike && $this->like > 0) {
                $this->like--;
            }

        }

        public function getPostDate(){
            return $this->postDate;
        }

        public function getBook(){
            return $this->book;
        }

        public function setBook($book){
            $this->book = $book;
        }

        public function getComments(){
            return $this->comments;
        }

        public function setComment($comment){
            array_push($this->comments, $comment);
        }

        // metodos da classe

        public function addLike($isLike){
            if($isLike)
                $this->like++;
        }

        public function removeLike($isLike){
            if(!$isLike && $this->like > 0)
                $this->like--;
        }

        // removi o metodo setLike e dividi a funcionalidade em dois para facilitar o uso no código
    }