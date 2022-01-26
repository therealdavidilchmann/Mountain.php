<?php

    class HTTP_Error {
        public const NOT_FOUND = 0;
        public const FORBIDDEN = 1;

        private $errorTemplates = [
            self::NOT_FOUND => [
                "code" => 404,
                "msg" => "This page doesn't exist."
            ],
            self::FORBIDDEN => [
                "code" => 403,
                "msg" => "YOU SHALL NOT PASS!"
            ]
        ];

        private $errCode = 0;
        private $msg = "";

        public function __construct(...$args)
        {
            if (count($args) == 1 && $args[0] >= 0) {
                $this->errCode = $this->errorTemplates[$args[0]]['code'];
                $this->msg = $this->errorTemplates[$args[0]]['msg'];
            } else {
                $this->errCode = $args[0];
                $this->msg = $args[1];
            }
        }

        public function getErrorCode()
        {
            return $this->errCode;
        }

        public function getErrorMsg()
        {
            return $this->msg;
        }
    }

?>