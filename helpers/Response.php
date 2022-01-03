<?php

    class Response {

        public static function error($errCode, $msg)
        {
            echo Response::view("error", ["code" => $errCode, "message" => $msg]);
            exit;
        }

        public static function view(string $component, array $data = array(), array $meta=array())
        {
            $variables = [];
            $safeData = [];
            $loopData = [];


            foreach ($data as $key => $value) {

                if ($key == "safe") {
                    $safeData = $value;
                } else if (is_array($value)) {
                    $loopData[$key] = $value;
                } else {
                    $variables[$key] = $value;
                }
            }

            return TemplateEngine::template($component, new ReplaceData($variables, $safeData, $loopData, $meta));
        }
    }
?>
