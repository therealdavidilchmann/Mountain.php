<?php


    class Path {
        public const HTML_ROOT = "./public/html";
        public const CSS_ROOT = "./public/static/css";
        public const JS_ROOT = "./public/static/js";
        public const IMG_ROOT = "public/static/img";

        public static function ROOT()
        {
            return str_replace("\\", "/", getcwd());
        }

        public static function getRouteWithParameters()
        {
            return str_replace('/index.php', '', $_SERVER['REQUEST_URI']);
        }

        public static function getPath()
        {
            $url = $_SERVER['REQUEST_URI'];

            $url = str_replace('/index.php', '', $url);

            if (isset($url)) {
                if (strlen($url) > 0) {
                    if ($url[strlen($url)-1] == '/') {
                        $url = substr($url, 0, -1);
                    }
                }
                if (strpos($url, '?')) {
                    $url = substr($url, 0, strpos($url, "?"));
                }
                return $url;
            } else {
                Response::error(404, 'Page not found');
            }
        }

        public static function isFile($path)
        {
            return is_file($path);
        }

        public static function getFullPath($root, $componentPath)
        {
            return $root . "/" . $componentPath . "." . (strpos($root, "static") !== false ? substr($root, strpos($root, "/", 9)+1) : substr($root, strpos($root, "/", 2)+1));
        }

        public static function redirect(string $path)
        {
            header('Location: ' . $path);
            exit;
        }
    }

?>
