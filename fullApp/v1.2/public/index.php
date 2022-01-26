<?php

    require '../config.php'; 
    require '../core_modules/_init_.php';

    $kernel = require '../core_modules/Kernel.php';

    $kernel->routes('routes.php');

    $kernel->handle();

?>