<?php


    require 'helpers/require.php';
    
    require 'config.php';


    $app = new Application();
    
    $app->registerRoutes('routes.php');

    $app->run();


?>
