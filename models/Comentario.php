<?php
    class Comentario{

        private $content;
        private $like;
        private $commentDate;

        public function __construct($comment){
            $this->setContent($comment);
            $this->commentDate = new DateTime();
            $this->like = 0;
        }

        public function getContent(){
            return $this->content;
        }

        public function setContent($comment){
            $this->content = $comment;
        }

        public function getCommentDate(){
            return $this->commentDate;
        }

        public function getLike(){
            return $this->like;
        }

        public function addLike($isLike){
            if($isLike)
                $this->like++;
        }

        public function removeLike($isLike){
            if(!$isLike && $this->like > 0)
                $this->like--;
        }
    }