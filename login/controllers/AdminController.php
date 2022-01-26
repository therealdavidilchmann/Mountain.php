<?php

    class AdminController {
        public function index()
        {
            echo Response::view("sites/admin/dashboard");
        }
    }


?>