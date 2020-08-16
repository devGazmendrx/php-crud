<?php 

define("APP_BASE_PATH", __DIR__);

require_once("vendor/autoload.php");

use App\DB\DB; 

/**
 * Initiating DB connection and DB object
 */
$dbParams = [
    "serverName" => "localhost",
    "userName" => "root",
    "password" => "",
    "dbName" => "php_crud_db"
]; 
$db = new DB($dbParams);


/**
 * Example Inserting a row or multiple rows into a table "users" [C]RUD
 */
$insertParams = [
    ["name" => "John Doe","email" => "john@doe.com"],
    ["name" => "Jane Doe","email" => "jane@doe.com"]
];
$response = $db->executeQuery("insert", "users", $insertParams);

/**
 * Example Fetching a row/s from a table "users", leaving "columns" empty C[R]UD
 * will fetch all columns from table
 */
$getParams = [
    "columns" => ["name","email"],
    "whereClause" => "WHERE `name` = :name",
    "whereClauseParams" => [
        "name" => "John Doe"
    ]
];

$response = $db->executeQuery("get", "users", $getParams);

/**
 * Update a row from a table "users" CR[U]D
 */
 $updateParams = [
     "setClause" => ["name", "email"], //which columns to update
     "whereClause" => "WHERE `id` = :id",
     "whereClauseParams" => [
         "id" => 18,
         "name" => "Updated John",
         "email" => "updated@john.com"
     ] 
 ];

 $response = $db->executeQuery("update", "users", $updateParams);

/**
 * Delete from a table "users" CRU[D]
 */

 $deleteParams = [
     "whereClause" => "WHERE `id` = :id",
     "whereClauseParams" => [
         "id" => 19
     ]
 ];

 $response = $db->executeQuery("delete", "users", $deleteParams);
 