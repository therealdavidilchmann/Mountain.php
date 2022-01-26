<?php

    class Date {
        public const DATE_FORMAT = "Y/m/d H:i:s";
        public const DATE = "Y/m/d";
        public const TIME = "H:i:s";

        public static function format($date)
        {
            $date = substr($date, 0, 10);
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);
            return $day . "." . $month . "." . $year;
        }
    }
?>
