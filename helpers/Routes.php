<?php

    require_once 'Path.php';


    class Routes {
        public $routes;
        public $restrictedAreas;

        public function __construct()
        {
            $this->routes = array();
        }
        
        public function both($route, $activator)
        {
            $this->get($route, $activator);
            $this->post($route, $activator);
            return $this;
        }

        public function get($route, $activator)
        {
            $newRoute = new Route($route, 'GET', $activator);
            array_push($this->routes, $newRoute);
            return $this;
        }

        public function view($route, $view)
        {
            $newRoute = new Route($route, 'GET', [$view]);
            array_push($this->routes, $newRoute);
            return $this;
        }

        public function post($route, $activator)
        {
            $newRoute = new Route($route, 'POST', $activator);
            array_push($this->routes, $newRoute);
            return $this;
        }

        public function restrictedOn($name)
        {
            $this->routes[count($this->routes)-1]->setRestrictionName($this->restrictedArea($name));
            return $this;
        }

        public function authRequired()
        {
            $this->routes[count($this->routes)-1]->authRequired();
            return $this;
        }

        public function restrictedArea($area)
        {
            if (array_key_exists($area, $this->restrictedAreas)) {
                return $this->restrictedAreas[$area];
            }
            Response::error(500, "Restricted area '" . $area . "' not defined.");
        }

        public function listen($application)
        {
            $application->setRestrictedAreas($this->restrictedAreas);
            $path = Path::getPath();
            if ($path == "") {
                $path = "/";
            }

            for ($i=0; $i < count($this->routes); $i++) {
                if ($this->routes[$i]->route == $path) {
                    if ($_SERVER['REQUEST_METHOD'] == $this->routes[$i]->method) {
                        if ($this->routes[$i]->authRequired) {
                            if (Auth::isLoggedIn()) {
                                if (!empty($this->routes[$i]->restrictionName)) {
                                    if (Auth::userHasAccessTo($this->routes[$i]->restrictionName)) {
                                        $this->getController($this->routes[$i], $application);
                                    } else {
                                        echo Response::error(403, "You don't have the permission to see this page.");
                                    }
                                } else {
                                    $this->getController($this->routes[$i], $application);
                                }
                            } else {
                                Path::redirect("/index.php/login");
                            }
                        } else {
                            if (!empty($this->routes[$i]->restrictionName)) {
                                echo Response::error(500, "Route has restriction, but you don't have to be logged in.");
                            } else {
                                $this->getController($this->routes[$i], $application);
                            }
                        }
                        
                    }
                }
            }
            Response::error(404, 'Page not found');
        }

        public function setRestrictedAreas($areas)
        {
            $arr = [];
            for ($i=0; $i < count($areas); $i++) { 
                $arr[$areas[$i]] = $areas[$i];
            }
            $this->restrictedAreas = $arr;
        }

        public function getController(Route $route, $application)
        {
            if (is_string($route->activator)) {
                $activator = explode('@', $route->activator);
                if (file_exists("controllers/$activator[0].php")) {
                    require_once "controllers/$activator[0].php";
                    $class = new $activator[0]();
                    $method = $activator[1];
                    $class->$method($application);
                } else {
                    Response::error(500, "Controller $activator[0] doesn't exist.");
                }
                exit;
            } else if (is_array($route->activator)) {
                echo Response::view($route->activator[0]);
                exit;
            } else {
                $method = $route->activator;
                $method($application);
                exit;
            }
        }

        

        public function usermanagement()
        {
            $this->get('/admin/usermanagement', 'UserController@index')->restrictedOn("usermanagement")->authRequired();
            $this->both('/admin/usermanagement/create', 'UserController@create')->restrictedOn("usermanagement")->authRequired();
            $this->both('/admin/usermanagement/update', 'UserController@update')->restrictedOn("usermanagement")->authRequired();
            $this->get('/admin/usermanagement/delete', 'UserController@delete')->restrictedOn("usermanagement")->authRequired();
        }

        public function admin()
        {
            $this->get('/admin', 'AdminController@index')->authRequired();
        }

        public function auth()
        {
            $this->get('/login', 'AuthController@login');
        }
    }

    class Route {
        public $route;
        public $method;
        public $activator;
        public $restrictionName;
        public $authRequired;

        public function __construct($route, $method, $activator)
        {
            $this->route = $route;
            $this->method = $method;
            $this->activator = $activator;
            $this->restrictionName = "";
            $this->authRequired = false;
        }

        public function setRestrictionName($name)
        {
            $this->restrictionName = $name;
        }

        public function authRequired()
        {
            $this->authRequired = true;
        }
    }

    

?>
