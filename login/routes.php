<?php

    function routes() {
        $routes = new Routes();

        $routes->view('/success', 'sites/success');
        $routes->get('/', "MainController@index");

        $routes->auth([Routes::LOGIN, Routes::REGISTER]);
        $routes->admin();

        return $routes;
    }
?>
