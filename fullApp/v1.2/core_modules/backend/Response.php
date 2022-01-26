<?php

    class Response {

        public static function error(HTTP_Error $e)
        {
            return Response::view("error", ["code" => $e->getErrorCode(), "message" => $e->getErrorMsg()]);
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

            return Engine::template($component, new ReplaceData($variables, $safeData, $loopData, $meta));
        }
    }
?>
