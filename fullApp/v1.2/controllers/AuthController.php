<?php

    class AuthController extends DB {
        public function login()
        {
            if (Request::method() == "GET") {
                $data = Request::get('formError');
                if ($data == null) {
                    $data = "";
                }
                echo Response::view('sites/auth/login', ['formError' => $data]);
            } else {
                $data = Request::validate(["username", "password"]);
                $user = self::query("SELECT `id`, `password` FROM `users` WHERE `username` = :username;", [":username" => $data["username"]]);
                if (count($user) > 0) {
                    if (password_verify($data["password"], $user[0]["password"])) {
                        $token = bin2hex(random_bytes(25));
                        self::query("INSERT INTO `tokens` (`userID`, `token`) VALUES (:userID, :token);", [":userID" => $user[0]["id"], ":token" => $token]);

                        setcookie("token", $token, time() + 60 * 60 * 24 * 30, "/");
                        Path::redirect("/index.php/admin");
                        exit;
                    }
                    Path::redirect("/index.php/login?formError=password");
                    exit;
                }
                Path::redirect("/index.php/login?formError=username");
                exit;
            }
        }
    }

?>