<?php


    class Application {
        private $routes;
        private $restrictedAreas;

        public function registerRoutes($path)
        {
            if (Path::isFile($path)) {
                $this->routes = require_once Path::ROOT() . "/" . $path;
            }
        }

        public function getRoutes()
        {
            return $this->routes;
        }

        public function run(Events $events)
        {
            $events->run(Events::BEFORE_ROUTING);
            echo $this->routes->listen($this);
            $events->run(Events::AFTER_ROUTING);
        }

        public function setRestrictedAreas($areas)
        {
            $this->restrictedAreas = $areas;
        }

        public function getRestrictedAreas()
        {
            return $this->restrictedAreas;
        }
    }

?>