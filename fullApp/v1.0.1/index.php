<?php

    require './config.php'; 
    require './core_modules/_init_.php';

    $app = new Application();
    
    $app->registerRoutes('routes.php');

    $app->run();

?>
