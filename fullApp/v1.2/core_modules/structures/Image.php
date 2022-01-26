<?php

    class Image {
        public static function get($name)
        {
            return file_get_contents(Path::ROOT() . "/" . $name . ".b64");
        }
    }

?>