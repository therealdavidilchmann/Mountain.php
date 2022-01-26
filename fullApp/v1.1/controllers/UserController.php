<?php

    class UserController extends DB implements CRUD {
        public function index()
        {

            echo Response::view("sites/admin/usermanagement/index", [
                'users' => self::query("SELECT * FROM `users`")
            ]);
        }

        public function create(Application $application = null)
        {

            if (Request::method() == "GET") {
                echo Response::view("sites/admin/usermanagement/create", [
                    'restrictedAreas' => array_values($application->getRestrictedAreas())
                ]);
            } else {
                $data = Request::validate(["username", "password", "format"]);

                self::query("INSERT INTO `users` (`username`, `password`) VALUES (:username, :pw)", [':username' => $data['username'], ':pw' => password_hash($data['password'], PASSWORD_DEFAULT)]);
                $newUser = self::query("SELECT `id` FROM `users` WHERE `username` = :username;", [":username" => $data["username"]])[0];
                
                $linkToTextfield = explode('->', $data['format']);
                array_shift($linkToTextfield);
                
                for ($i=0; $i < count($linkToTextfield); $i++) {
                    $text = Request::get($linkToTextfield[$i]) ?? "";
                    self::query("INSERT INTO `zugriff` (`userID`, `url`) VALUES (:userID, :url)", [':userID' => $newUser['id'], ':url' => $text]);
                }

                Path::redirect("/index.php/admin/usermanagement");
                exit;
            }
        }

        public function update(Application $application = null)
        {
            
            $generateRandomString = function($length = 5) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                return $randomString;
            };
            
            if (Request::method() == "GET") {
                $data = Request::validate(["id"]);

                $user = self::query("SELECT `id`, `username` FROM `users` WHERE `id` = :id", [":id" => $data["id"]]);

                if (count($user) == 0) {
                    echo Response::error(500, "Post doesn't exist.");
                }

                $user = $user[0];
                $user_rights = self::query("SELECT * FROM `zugriff` WHERE `userID` = :userID;", [":userID" => $user['id']]);

                $form_html = '<form method="post" action="/index.php/admin/usermanagement/update"><input type="hidden" name="id" value="' . $user['id'] . '"><div class="form-group"><input type="text" class="form-control" id="username" name="username" placeholder="Benutzername" value="' . $user['username'] . '"></div><div id="all-content">';
                $format = "s";
                
                for ($i=0; $i < count($user_rights); $i++) {
                    $tfID = $generateRandomString();
                    $form_html .= '
                        <div class="form-group">
                            <button type="button" class="btn btn-danger mb-1" onclick="removeTextfield(this, \'text-' . $tfID . '\')">Recht löschen</button>
                            <select name="text-' . $tfID . '" id="text-' . $tfID . '" class="form-control">
                                <option value=""></option>
                    ';
                    for ($j=0; $j < count(array_values($application->getRestrictedAreas())); $j++) {
                        $form_html .= '<option value="' . array_values($application->getRestrictedAreas())[$j] . '" ' . ($user_rights[$i]['url'] == array_values($application->getRestrictedAreas())[$j] ? "selected" : "") . '>' . array_values($application->getRestrictedAreas())[$j] . '</option>';
                    }
                    
                    $form_html .= '
                            </select>
                        </div>
                    ';
                    $format .= "->text-" . $tfID;
                }
                
                $form_html .= '</div><input type="hidden" name="format" value="' . $format . '" id="content-format"><div class="row justify-content-center"><button type="button" class="btn btn-success ml-2 mr-2" onclick="addTextfield(\'right\')">Recht hinzufügen</button></div><button type="submit" class="btn btn-primary">Fertigstellen</button></form>';

                echo Response::view("sites/admin/usermanagement/update", [
                    "form" => $form_html,
                    'restrictedAreas' => array_values($application->getRestrictedAreas())
                ]);

            } else {
                $data = Request::validate(["id", "format", "username"]);

                $linkToTextfield = explode('->', $data['format']);
                array_shift($linkToTextfield);

                self::query("DELETE FROM `zugriff` WHERE `userID` = :userID", [":userID" => $data['id']]);

                for ($i=0; $i < count($linkToTextfield); $i++) { 
                    $text = Request::get($linkToTextfield[$i]) ?? "";
                    if (!empty($text)) {
                        self::query("INSERT INTO `zugriff` (`userID`, `url`) VALUES (:userID, :url);", [":userID" => $data['id'], ":url" => $text]);
                    }
                }
                
                Path::redirect("/index.php/admin/usermanagement");
            }
        }

        public function delete()
        {

            $data = Request::validate(["id"]);
            self::query("DELETE FROM `users` WHERE `id` = :id", [':id' => $data['id']]);
            self::query("DELETE FROM `zugriff` WHERE `userID` = :id", [':id' => $data['id']]);
            
            Path::redirect("/index.php/admin/usermanagement");
            
        }
    }



?>

