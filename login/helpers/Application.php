<?php

    class Application {
        private $routes;
        private $tags;
        private $restrictedAreas;
        private $defaultLoggedInPath;

        public function registerTags($path)
        {
            if (Path::isFile($path)) {
                require_once getcwd() . "/" . $path;
                array_push($this->tags, "tags"());
            }
        }

        public function registerRoutes($path)
        {
            if (Path::isFile($path)) {
                require_once getcwd() . "/" . $path;
                $this->routes = routes();
            }
        }

        public function setDefaultLoggedInPath($path)
        {
            $this->defaultLoggedInPath = $path;
        }

        public function getDefaultLoggedInPath()
        {
            return $this->defaultLoggedInPath;
        }

        public function getRoutes()
        {
            return $this->routes;
        }

        public function run()
        {
            $this->routes->listen($this);
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