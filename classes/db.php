<?php

class DB {
    private $connect;

    public function connectDB(string $host, string $username, string $db_name, string $password)
    {
        try {
            $this->connect = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        } catch (PDOException $e) {
            echo 'Error establishing connection to DB' . $e->getMessage();
        }

        return $this->connect;
    }

}