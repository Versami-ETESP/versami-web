<?php
    class Postagem{

        private $title;
        private $content;
        private $like;
        private $postDate;
        private $reading;
        private $unread;
        private $read;

        private $comments = array(); // Postagem tem relação com a classe Comentario

        // Contrutor padrão da classe iniciando com valores padrão: like, read, unread, reading. Além disso torna obrigatorio para um post o titulo e o conteudo
        //  instanciei DateTime para definir o horario e data do post

        public function __construct($title, $content){
            $this->setTitle($title);
            $this->setContent($content);
            $this->postDate = new DateTime();
            $this->like = 0;
            $this->reading = false;
            $this->unread = false;
            $this->read = false;
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

        public function getReading(){
            return $this->reading;
        }

        public function isReading($reading){
            if($reading){
                $this->reading = true;
            }
        }

        public function getRead(){
            return $this->read;
        }

        public function isRead($read){
            if($read){
                $this->read = true;
            }
        }

        public function getUnread(){
            return $this->unread;
        }

        public function isUnread($unread){
            if($unread){
                $this->unread = true;
            }
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