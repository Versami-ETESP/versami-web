<?php
    class Postagem{

        private $title;
        private $content;
        private $like;
        private $postDate;
        private $reading;
        private $unread;
        private $read;

        private $comments = array();

        public function __construct($title, $content){
            $this->title = $title;
            $this->content = $content;
            $this->like = 0;
            //$this->postDate 
        }

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
    }