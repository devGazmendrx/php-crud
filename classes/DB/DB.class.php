<?php 

namespace App\DB;

use PDO;
use App\DBHelper\DBHelper;

class DB{

    private $db;

    public function __construct(array $dbParams){

        $serverName = $dbParams['serverName'];
        $dbName = $dbParams['dbName'];
        $userName = $dbParams['userName'];
        $password = $dbParams['password'];

        try{

            $this->db = new PDO("mysql:host=$serverName;dbname=$dbName", $userName, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        }catch(\PDOException $e){

            echo "Something went wrong while trying to establish connection to DB: ".$e->getMessage();
        }
    }

    public function executeQuery($type, $table, $params = []){

        switch($type){

            case "insert":
               return DBHelper::insert($this->db, $table, $params);
            break;

            case "get":
                return DBHelper::get($this->db, $table, $params);
            break;

            case "update":
                return DBHelper::update($this->db, $table, $params);
            break;

            case "delete":
                return DBHelper::delete($this->db, $table, $params);
            break;
        }

    }
}