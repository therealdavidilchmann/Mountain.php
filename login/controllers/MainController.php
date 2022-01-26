<?php

    class MainController {
        public function index(Application $application)
        {
            echo Response::view("dashboard", [
                'loginSuccessRedirect' => $application->getDefaultLoggedInPath() ?? "/index.php/"
            ]);
        }
    }
    
?>