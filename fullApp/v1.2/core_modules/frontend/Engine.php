<?php


    class Engine {
        public static function template(string $componentName, ReplaceData $replaceData)
        {
            $path = "";
            $path = Path::ROOT(Path::HTML) . $componentName . ".html";

            if (is_file($path)) {
                $component = file_get_contents($path);
                return Tag::process($component, $replaceData);
            }

            return Response::error(new HTTP_Error(500, "Template $componentName doesn't exist"));
        }
    }

    class Tag extends DB {        
        public static function process(string $component, ReplaceData $replaceData, $loopIteration = null, $loopArrayName = null)
        {
            // safeData must be inserted first in order to not run into unwanted user input injections
            foreach ($replaceData->safeData as $key => $value) {
                $component = str_replace("{% getvar $key %}", $value, $component);
            }


            $tagsActivator = ["{%", "%}"];
            $currentIndex = 0;
            $template = null;

            while ($currentIndex + 10 < strlen($component)) {
                $tagBegin = strpos($component, $tagsActivator[0], $currentIndex);
                $tagEnd = strpos($component, $tagsActivator[1], $tagBegin);
                
                if ($tagBegin === false || $tagEnd === false) break;

                $tagContent = ltrim(substr($component, $tagBegin+2, ($tagEnd - $tagBegin)-2));
                $tagKeyword = explode(" ", $tagContent)[0];
                $tagParam = explode(" ", $tagContent)[1];

                // For loop stuff
                if ($loopIteration !== null) {
                    if ($tagKeyword == "getvar" && strpos($tagParam, $loopArrayName . ".") !== false) {
                        $key = substr(trim($tagContent), strpos(trim($tagContent), ".")+1);
                        if (empty($key)) {
                            $component = substr_replace($component, $replaceData->loopData[$loopArrayName][$loopIteration], $tagBegin, $tagEnd-$tagBegin+2);
                        } else if ($key == "loopIteration") {
                            $component = substr_replace($component, $loopIteration, $tagBegin, $tagEnd-$tagBegin+2);
                        } else {
                            $component = substr_replace($component, $replaceData->loopData[$loopArrayName][$loopIteration][$key], $tagBegin, $tagEnd-$tagBegin+2);
                        }
                        
                        continue;
                    }
                }
                
                $tagContent = trim($tagContent);
                $endloopIndex = strpos($component, "{% end" . $tagContent . " %}");
                $endloopEndIndex = strpos($component, "%}", $endloopIndex);
                $innerContent = trim(substr($component, $tagEnd + 2, $endloopIndex - $tagEnd - 2));

                // Normal Tags
                switch ($tagKeyword) {
                    case "extend":
                        $component = str_replace("{% extend $tagParam %}", "", $component);
                        $template = Engine::template($tagParam, $replaceData);
                        break;
                    case "section":
                        if (isset($template)) {
                            $template = str_replace("{% create $tagParam %}", Tag::process($innerContent, $replaceData), $template);
                        }
                        $component = substr_replace($component, "", $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    case "import":
                        $content = Logic::resource($tagContent);
                        $component = str_replace("{% " . ltrim(rtrim($tagContent)) . " %}", $content, $component);
                        break;
                    case "setvar":
                        $parts = explode("=>", str_replace("setvar ", "", $tagContent));
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        if (key_exists($key, $replaceData->loopData)) {
                            $replaceData->loopData->$key = $value;
                        } else {
                            $replaceData->data[$key] = $value;
                        }
                        $component = str_replace("{% " . trim($tagContent) . " %}", "", $component);
                        break;
                    case "getvar":
                        $varname = trim($tagContent);
                        if (strpos($varname, "??") !== false) {
                            $mainName = trim(explode("??", $varname)[0]);
                            $defaultValue = trim(explode("??", $varname)[1]);
                            $component = str_replace("{% " . $varname . " %}", Tag::process($replaceData->data[trim(str_replace("getvar ", "", $mainName))] ?? $defaultValue, $replaceData), $component);
                            break;
                        }
                        $component = str_replace("{% " . $varname . " %}", Tag::process($replaceData->data[trim(str_replace("getvar ", "", $varname))] ?? "", $replaceData), $component);
                        break;
                    case "loop":
                        $add = "";
                        if (array_key_exists(trim($tagParam), $replaceData->loopData)) {
                            for ($i=0; $i < count($replaceData->loopData[trim($tagParam)]); $i++) {
                                $add .= Tag::process($innerContent, $replaceData, $i, $tagParam);
                            }
                        }
                        $component = substr_replace($component, $add, $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    case "img":
                        $imgPath = explode(";", $tagParam)[0];
                        $imgType = explode(";", $tagParam)[1];
                        $base64 = Image::get($imgPath);
                        $component = str_replace("{% img $tagParam %}", "data:$imgType;base64,$base64", $component);
                        break;
                    case "loggedIn":
                        $add = "";
                        if (Auth::isLoggedIn()) {
                            $add .= Tag::process($innerContent, $replaceData);
                        }
                        $component = substr_replace($component, $add, $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    case "guest":
                        $add = "";
                        if (!Auth::isLoggedIn()) {
                            $add .= Tag::process($innerContent, $replaceData);
                        }
                        $component = substr_replace($component, $add, $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    case "propertyExists":
                        $propertyName = explode(" ", trim($tagContent))[1];
                        $add = "";
                        if (array_key_exists(trim($propertyName), $replaceData->data)) {
                            $add .= Tag::process($innerContent, $replaceData);
                        }
                        $component = substr_replace($component, $add, $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    case "form":
                        $endloopIndex = strpos($component, "{% end" . trim(substr($tagContent, 0, strpos($tagContent, "(") !== false ? strpos($tagContent, "(") : strlen($tagContent))) . " %}");
                        $endloopEndIndex = strpos($component, "%}", $endloopIndex);
                        $formContent = trim(substr($component, $tagEnd + 2, $endloopIndex - $tagEnd - 2));
                        $formParams = substr($tagContent, strpos($tagContent, "(")+1, (strpos($tagContent, "(") !== false ? strpos($tagContent, "(") : 0) + (strpos($tagContent, ")") !== false ? strpos($tagContent, ")")-11 : 0));
                        $formParams = explode("&", $formParams);
                        
                        foreach ($formParams as $key => $value) {
                            $formParams[trim(explode("=", $value)[0])] = trim(explode("=", $value)[1]);
                        }

                        $csrfToken = bin2hex(random_bytes(25));
                        self::query("INSERT INTO `csrf` (`token`, `timestamp`) VALUES (:token, :timestamp);", [':token' => $csrfToken, ':timestamp' => date(Date::DATE_FORMAT)]);
                        
                        $add = "<form method=\"" . $formParams['method'] . "\" action=\"" . $formParams['action'] . "\"" . (isset($formParams['enctype']) ? " enctype=\"" . $formParams['enctype'] : "") . "\">
                            <input type=\"hidden\" name=\"csrf\" value=\"$csrfToken\">
                            " . Tag::process($formContent, $replaceData) . "
                        </form>";
                        $component = substr_replace($component, $add, $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    case "link":
                        $link = explode(" ", $tagContent)[1];
                        $add = "";
                        if (Auth::userHasAccessTo($link)) {
                            $add .= Tag::process($innerContent, $replaceData);
                        }
                        $component = substr_replace($component, $add, $tagBegin, $endloopEndIndex - $tagBegin + 2);
                        break;
                    default:
                        break;
                }
                $currentIndex = $tagBegin + 1;
            }
            return isset($template) ? $template : $component;
        }
    }

    class Logic {

        public static function resource($innerData)
        {
            $getTag = function ($tag, $path) {
                if (strpos($path, "html") !== false) {
                    return Path::isFile($path) ? file_get_contents($path) : (Config::IS_PRODUCTION ? "" : "<!--Komponent konnten nicht geladen werden-->");
                } else {
                    return Path::isFile($path) ? "<$tag>" . file_get_contents($path) . "</$tag>" : (Config::IS_PRODUCTION ? "" : "<!--$tag konnten nicht geladen werden-->");
                }
            };

            $endingToTag = function ($ending) { return array("css" => "style", "js" => "script")[$ending] ?? null; };
    
            $innerData = str_replace("import ", "", $innerData);
            $fileFormat = rtrim(substr($innerData, strpos($innerData, ".") + 1, strlen($innerData) - strpos($innerData, ".")));
    
            $path = Path::ROOT(Path::SRC) . $innerData;
            
            return $getTag($endingToTag($fileFormat), $path) ?? "<!--Fehlerhafter Style oder Script tag-->";
        }

        public static function removeTag($component, $needle)
        {
            $component = str_replace("{% extend $needle %}", "", $component);
            return $component;
        }
    }

?>