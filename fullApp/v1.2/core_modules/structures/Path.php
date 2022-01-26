<?php



    class Path {
        public const HTML = 0;
        public const CSS = 1;
        public const JS = 2;
        public const SRC = 3;

        public static function ROOT($to=-1)
        {
            if ($to > -1) {
                return [
                    Path::HTML => Path::ROOT() . "src\\views\\",
                    Path::CSS => Path::ROOT() . "src\\css\\",
                    Path::JS => Path::ROOT() . "src\\js\\",
                    Path::SRC => Path::ROOT() . "src\\",
                ][$to];
            }
            return dirname(__DIR__, 2) . "\\";
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
                Response::error(new HTTP_Error(HTTP_Error::NOT_FOUND));
            }
        }

        public static function isFile($path)
        {
            return is_file(Path::ROOT() . "/" . $path);
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
