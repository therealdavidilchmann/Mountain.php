<?php

    function routes() {
        $routes = new Routes();

        $routes->get('/', 'MainController@index');
        
        $routes->admin();
        $routes->usermanagement();
        $routes->auth();

        return $routes;
    }
?>
