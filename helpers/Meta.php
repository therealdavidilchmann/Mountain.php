<?php

    class Meta {
        public static function keywords($keywords)
        {
            return Meta::a("name", "keywords", $keywords);
        }
        public static function description($description)
        {
            return Meta::a("name", "description", $description);
        }

        private static function a($attribute, $value, $description)
        {
            return "<meta $attribute=\"$value\" content=\"$description\">";
        }
    }


?>