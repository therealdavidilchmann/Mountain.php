<?php

    class MainController {
        public function index()
        {
            echo Response::view("dashboard");
        }
    }


?>