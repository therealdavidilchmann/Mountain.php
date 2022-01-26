<?php

    require_once 'Path.php';


    class Routes {
        public const LOGIN = 0;
        public const REGISTER = 1;

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
                        if (($_SERVER['REQUEST_METHOD'] == "POST" && Auth::isValidCSRF()) || $_SERVER['REQUEST_METHOD'] != "POST") {
                            Auth::deleteCSRF();
                            if (($this->routes[$i]->authRequired && Auth::isLoggedIn()) || !$this->routes[$i]->authRequired) {
                                if (!empty($this->routes[$i]->restrictionName && Auth::userHasAccessTo($this->routes[$i]->restrictionName)) || empty($this->routes[$i]->restrictionName)) {
                                    $this->getController($this->routes[$i], $application);
                                } else {
                                    Response::error(403, "You don't have the permission to see this page.");
                                }
                            } else {
                                Path::redirect("/index.php/login");
                            }
                        } else {
                            Response::error(403, "The request timed out or has been sent by a non-authorized server.");
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

        public function auth($routes)
        {
            if (in_array(Routes::LOGIN, $routes)) {
                $this->get('/login', function () {
                    $data = Request::get('formError') ?? "";
                    echo Response::view('sites/auth/login', ['formError' => $data]);
                });
                $this->post('/login', function () {
                    $data = Request::validate(["username", "password"]);
                    $user = DB::query("SELECT `id`, `password` FROM `users` WHERE `username` = :username;", [":username" => $data["username"]]);
                    if (count($user) > 0) {
                        if (password_verify($data["password"], $user[0]["password"])) {
                            $token = bin2hex(random_bytes(25));
                            DB::query("INSERT INTO `tokens` (`userID`, `token`) VALUES (:userID, :token);", [":userID" => $user[0]["id"], ":token" => $token]);
    
                            setcookie("token", $token, time() + 60 * 60 * 24 * 30, "/");
                            echo "
                                <script>
                                    window.opener.refresh();
                                    window.close();
                                </script>
                            ";
                            exit;
                        }
                        Path::redirect("/index.php/login?formError=password");
                        exit;
                    }
                    Path::redirect("/index.php/login?formError=username");
                    exit;
                });
            }
            if (in_array(Routes::REGISTER, $routes)) {
                $this->both('/register', 'AuthController@register');
            }
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
