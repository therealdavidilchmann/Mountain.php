<?php

    class Request {

        public static function method()
        {
            return $_SERVER['REQUEST_METHOD'];
        }

        public static function get($name)
        {
            if (is_array($name)) {
                $res = array();
                foreach ($name as $param) {
                    $value = isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;
                    $res[$param] = $value;
                }
                return $res;
            }
            return isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
        }

        public static function validate(array $columns)
        {
            $data = [];
            for ($i=0; $i < count($columns); $i++) { 
                if (Request::get($columns[$i]) == null) {
                    return [];
                }
                $data[$columns[$i]] = Request::get($columns[$i]);   
            }
            return $data;
        }

        public static function require(array $columns)
        {
            for ($i=0; $i < count($columns); $i++) { 
                if (Request::get($columns[$i]) === null) {
                    Response::error(500, 'Attribute not found.');
                    exit;
                }
            }
        }
    }

?>