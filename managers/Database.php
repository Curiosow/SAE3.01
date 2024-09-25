<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $host = null;
        $port = null;
        $dbname = null;
        $user = null;
        $password = null;

        if (!file_exists('config.yml')) {
            echo "<script> location.href='Error.php'; </script>";
            exit;
        }

        $config = file('config.yml');

        if ($config === false) {
            echo "<script> location.href='Error.php'; </script>";
            exit;
        }

        foreach ($config as $line) {
            $split = explode(': ', $line);
            $key = $split[0];
            $value = $split[1];
            switch ($key) {
                case "host":
                    $host = $value;
                    break;

                case "port":
                    $port = $value;
                    break;

                case "dbname":
                    $dbname = $value;
                    break;

                case "user":
                    $user = $value;
                    break;

                case "password":
                    $password = $value;
                    break;
            }
        }

        $this->connection = pg_connect(
            "host=$host port=$port dbname=$dbname user=$user password=$password"
        );

        if (!$this->connection) {
            echo "<script> location.href='Error.php'; </script>";
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}