<?php

    require './cmd/Cmd.php';

    array_shift($argv);


    $cmd = new CMD($argv);
    $cmd->handle(CMD::COMMAND);
