<?php

    class Auth {
        public static function isLoggedIn()
        {
            $user = DB::query("SELECT COUNT(`userID`) AS numUsers FROM `users` INNER JOIN `tokens` ON `users`.`id` = `tokens`.`userID` WHERE `tokens`.`token` = :token", [":token" => Auth::getToken()]);
            return $user[0]['numUsers'] > 0;
        }

        public static function userHasAccessTo($link)
        {
            return 0 < count(DB::query("SELECT `url` FROM `zugriff` WHERE `userID` = :userID AND `url` = :url", [':userID' => Auth::getUserID(), ':url' => trim($link)]));
        }

        public static function getUserID()
        {
            return DB::query("SELECT `users`.`id` FROM `users` INNER JOIN `tokens` ON `users`.`id` = `tokens`.`userID` WHERE `tokens`.`token` = :token", [":token" => Auth::getToken()])[0]['id'];
        }

        public static function getToken()
        {
            return $_COOKIE['token'] ?? "";
        }
    }

?>