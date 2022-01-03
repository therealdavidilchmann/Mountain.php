<?php

    function routes() {
        $routes = new Routes();

        $routes->setRestrictedAreas(["usermanagement"]);

        $routes->get('/', 'MainController@index');

        $routes->admin();
        $routes->usermanagement();
        $routes->auth();

        return $routes;
    }
?>
