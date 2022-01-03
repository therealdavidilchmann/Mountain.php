<?php

    class DB {

        public const DATE_FORMAT = "Y/m/d H:i:s";
        public const DATE = "Y/m/d";
        public const TIME = "H:i:s";
        
        private const HOST = '127.0.0.1';
        private const DBNAME = 'hundezucht';
        private const USERNAME = 'root';
        private const PASSWORD = '';

        private const HOST_SERVER = 'db5006135754.hosting-data.io';
        private const HOST_DBNAME = 'dbs5133290';
        private const HOST_USERNAME = 'dbu1994169';
        private const HOST_PASSWORD = 'n8Vg4a9ctHDP7EJi';

        private static function connect()
        {
            try {
                if (Config::IS_PRODUCTION) {
                    $pdo = new PDO('mysql:host=' . DB::HOST_SERVER . ';port=3306;dbname=' . DB::HOST_DBNAME . ';charset=utf8', DB::HOST_USERNAME, DB::HOST_PASSWORD);
                } else {
                    $pdo = new PDO('mysql:host=' . DB::HOST . ';dbname=' . DB::DBNAME . ';charset=utf8', DB::USERNAME, DB::PASSWORD);
                }
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (PDOException $e) {
                Response::error(500, 'Error connecting to DB due to: <br>' . $e->getMessage());
                exit;
            }
        }

        public static function query(string $query, array $params = array())
        {
            try {
                $db = DB::connect();
                $stmt = $db->prepare($query);
                $stmt->execute($params);

                if (strtoupper(explode(' ', $query)[0]) == 'SELECT') {
                    $result = $stmt->fetchAll();
                    if (isset($result)) {
                        return $result;
                    }
                } else if (strtoupper(explode(' ', $query)[0]) == 'INSERT') {
                    return $db->lastInsertId();
                }
            } catch (PDOException $e) {
                Response::error(500, "Error querying database.");
                exit;
            }
        }

        public static function exists($tableName, $columnName, $value)
        {
            $res = DB::query("SELECT * FROM $tableName WHERE $columnName = :value", [':value' => $value]);
            return count($res) > 0;
        }
        
        public static function get(string $tableName)
        {
            return DB::query("SELECT * FROM $tableName");
        }
    }
?>
