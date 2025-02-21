<?php
    class Comentario{

        private $content;
        private $like;
        private $commentDate;

        public function __construct($comment){
            $this->content = $comment;
            $this->commentDate = new DateTime();
            $this->like = 0;
        }
    }