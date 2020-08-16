<?php 

namespace App\DBHelper;

use PDO;

class DBHelper{

    public static function insert($pdo, $table, $insertParams){

        if(empty($insertParams)){
            return ["status" => 0, "message" => "Insert parameter array cannot be empty! #ERR-DBINSERT-01", "errorCode" => "DBINSERT-01"];
        }

        if(empty($table)){
            return ["status" => 0, "message" => "Table parameter cannot be empty! #ERR-DBINSERT-02", "errorCode" => "DBINSERT-02"];
        }
        
        $tableColumnsResponse = self::getTableColumns($pdo, $table);

        if($tableColumnsResponse['status']){

            $getInsertSqlResponse = self::prepareInsertStatement($table, $tableColumnsResponse['tableColumns'], $insertParams);

            if($getInsertSqlResponse['status']){

                $insertSql = $getInsertSqlResponse['insertQuery'];

                try{

                    $insertIds = [];
                    $statement = $pdo->prepare($insertSql);

                    for($i=0; $i < count($insertParams); $i++){

                        $singleRow = $insertParams[$i];
                        foreach($singleRow as $key => &$value){
                            $statement->bindParam(":$key$i", $value);
                        }                        
                    }

                    $statement->execute();

                    $insertId = $pdo->lastInsertId();
                    $insertIds[] = $insertId;
                    for($i=1; $i < count($insertParams); $i++){
                        $insertIds[] = ++$insertId;
                    }

                    $insertParamCount = count($insertParams);
                    if($insertParamCount > 1){
                        $stringifedInsertIds = implode(",", $insertIds);
                        return ["status" => 1, "message" => "$insertParamCount new entries have been successfully added to table: [$table] with IDs: $stringifedInsertIds", "insertIds" => $insertIds];
                    }

                    return ["status" => 1, "message" => "A new entry has been successfully added to table: [$table] with ID: $insertId", "insertId" => $insertId];

                }catch(\PDOException $e){

                    return ["status" => 0, "message" => "Something went wrong while trying to insert a new row to DB! #ERR-DBINSERT-03", "errorCode" => "DBINSERT-03", "pdoMessage" => $e->getMessage()];
                }
            }

            return $getInsertSqlResponse;
        }
    }

    public static function get($pdo, $table, $getParams){

        if(empty($getParams)){
            return ["status" => 0, "message" => "Get parameter array cannot be empty! #ERR-DBGET-01", "errorCode" => "DBGET-01"];
        }

        if(empty($table)){
            return ["status" => 0, "message" => "Table parameter cannot be empty! #ERR-DBGET-02", "errorCode" => "DBGET-02"];
        }

        $getSqlResponse = self::prepareGetStatement($table, $getParams);
        if($getSqlResponse['status']){

            $getSql = $getSqlResponse['getQuery'];
            $whereClauseParams = $getSqlResponse['whereClauseParams'];

            try{
                $statement = $pdo->prepare($getSql);
                if(!empty($whereClauseParams)){
                    $data = $statement->execute($whereClauseParams);
                    $data = $statement->fetchAll();
                }else{
                    $data = $statement->fetchAll();
                }
                
                return ["status" => 1, "message" => "Data from DB was successfully retrieved!", "data" => $data];

            }catch(\PDOException $e){

                return ["status" => 0, "message" => "Something went wrong while trying to retrieve data from DB! #ERR-DBGET-03", "errorCode" => "DBGET-03", "pdoMessage" => $e->getMessage()];
            }

        }
    }

    public static function update($pdo, $table, $updateParams){

        if(empty($updateParams)){
            return ["status" => 0, "message" => "Update parameter array cannot be empty! #ERR-DBUPDATE-01", "errorCode" => "DBUPDATE-01"];
        }

        if(empty($table)){
            return ["status" => 0, "message" => "Table parameter cannot be empty! #ERR-DBUPDATE-02", "errorCode" => "DBUPDATE-02"];
        }

        $updateSqlResponse = self::prepareUpdateStatement($table, $updateParams);
        if($updateSqlResponse['status']){

            $updateSql = $updateSqlResponse['updateQuery'];
            $whereClauseParams = $updateSqlResponse['whereClauseParams'];

            try{
                $statement = $pdo->prepare($updateSql);
                $statement->execute($whereClauseParams);
                
                return ["status" => 1, "message" => "Query completed successfully!", "affectedRows" => $statement->rowCount()];

            }catch(\PDOException $e){

                return ["status" => 0, "message" => "Something went wrong while trying to update row/s from DB! #ERR-DBUPDATE-03", "errorCode" => "DBUPDATE-03", "pdoMessage" => $e->getMessage()];
            }

        }
    }

    public static function delete($pdo, $table, $deleteParams){

        if(empty($deleteParams)){
            return ["status" => 0, "message" => "Delete parameter array cannot be empty! #ERR-DBDELETE-01", "errorCode" => "DBDELETE-01"];
        }

        if(empty($table)){
            return ["status" => 0, "message" => "Table parameter cannot be empty! #ERR-DBDELETE-02", "errorCode" => "DBDELETE-02"];
        }

        $deleteSqlResponse = self::prepareDeleteStatement($table, $deleteParams);
        if($deleteSqlResponse['status']){

            $deleteSql = $deleteSqlResponse['deleteQuery'];
            $whereClauseParams = $deleteSqlResponse['whereClauseParams'];

            try{
                $statement = $pdo->prepare($deleteSql);
                $statement->execute($whereClauseParams);
                
                return ["status" => 1, "message" => "Query completed successfully!", "affectedRows" => $statement->rowCount()];

            }catch(\PDOException $e){

                return ["status" => 0, "message" => "Something went wrong while trying to remove row/s from DB! #ERR-DBDELETE-03", "errorCode" => "DBDELETE-03", "pdoMessage" => $e->getMessage()];
            }

        }
    }

    public static function getTableColumns($pdo, $table){
        try{

            $sql = $pdo->query("DESCRIBE $table");
            $r = $sql->fetchAll(PDO::FETCH_ASSOC);

            $tableColumns = [];
            foreach($r as $tableCol){

                $colName = $tableCol['Field'];

                $tableColumns[$colName] = [
                    "type" => $tableCol['Type'],
                    "canBeNull" => ($tableCol['Null'] == "NO") ? 0 : 1,
                    "defaultValue" => $tableCol['Default'],
                    "autoIncrement" => ($tableCol['Extra'] == "auto_increment") ? 1 : 0
                ];
            }

            return ["status" => 1, "message" => "Information on table columns has been successfully retrieved!", "tableColumns" => $tableColumns];

        }catch(\PDOException $e){
           return ["status" => 0 , "message" => "Something went wrong while fetching information on table columns! #ERR-DBTABLECOL-01", "errorCode" => "DBTABLECOL-01", "pdoMessage" => $e->getMessage()];
        }
    }

    public static function prepareInsertStatement($table,$tableColumns, $insertParams){

        $sqlFields = [];
        $sqlValues = [];
        $keyChecked = [];
    
        for($i = 0; $i < count($insertParams); $i++){

            $singleRow = $insertParams[$i];

            $tempSqlValues = [];
            $tempCount = 0;
            foreach($tableColumns as $key => $attributes){
                //check if table column from db is part of insertParams array
                if(!array_key_exists($key, $singleRow)){
    
                    //if it isnt, check if this table column from db can be null
                    if(!$attributes['canBeNull']){
                        
                        //it cant be null so check for default value and that is not auto incremented
                        if(empty($attributes['defaultValue']) && !$attributes['autoIncrement']){
    
                            //default value not set -- return error;
                            return ["status" => 0, "message" => "There was a missing table column from your insert parameter [$i] that cannot be null. Required column: [$key]"];
                        }
                    }
                }
    
                //generate sqlFields string;
                if(array_key_exists($key, $singleRow)){
    
                    //prevent duplicate keys
                    if(!in_array($key, $keyChecked)){
                        $sqlFields[] = $key;
                    }
    
                    //sqlValues
                    $tempSqlValues[] =":$key$i";
                    $stringifedSqlValues = implode(",", $tempSqlValues);
                    $tempValue = "($stringifedSqlValues)";
                    if($tempCount > 0){
                        $sqlValues[] = $tempValue;
                    }

                    $keyChecked[] = $key;
                    $tempCount++;
                    
                }
            }
        }

        $stringifiedSqlFields = implode(",", $sqlFields);
        $stringifiedSqlValues = implode(",", $sqlValues);
        $insertSQL = "INSERT INTO $table($stringifiedSqlFields) VALUES $stringifiedSqlValues";

        return ["status" => 1, "message" => "Insert query successfully generated!" , "insertQuery" => $insertSQL];
    }

    public static function prepareGetStatement($table, $getParams){

        $columns = (!empty($getParams['columns'])) ? implode(",", $getParams['columns']) : "*";
        $whereClause = (!empty($getParams['whereClause'])) ? $getParams['whereClause'] : "";

        $getSQL = "SELECT $columns FROM `$table` $whereClause";
        return ["status" => 1, "message" => "Get query successfully generated!" , "getQuery" => $getSQL, "whereClauseParams" => $getParams['whereClauseParams']];
    }

    public static function prepareUpdateStatement($table, $updateParams){

        if(empty($updateParams['setClause'])){
            return ["status" => 0, "message" => "Update clause in query cannot be empty! #ERR-DBUPDATE-04", "errorCode" => "DBUPDATE-04"];
        }

        if(empty($updateParams['whereClauseParams'])){
            return ["status" => 0, "message" => "Where clause parameters in query cannot be empty! #ERR-DBUPDATE-05", "errorCode" => "DBUPDATE-05"];
        }

        $setClause = "";
        foreach($updateParams['setClause'] as $key => $value){

            //return 0 if a required parameter is missing
            if(!array_key_exists($value, $updateParams['whereClauseParams'])){
                return ["status" => 0, "message" => "[$value] parameter is required in Where clause parameters! #ERR-DBUPDATE-06", "errorCode" => "DBUPDATE-06"];
            }

            if($key == 0){
                $setClause .= "`$value` = :$value";
            }else{
                $setClause .= ",`$value` = :$value";
            }
        }

        $whereClause = (!empty($updateParams['whereClause'])) ? $updateParams['whereClause'] : "";

        $updateSQL = "UPDATE `$table` SET $setClause $whereClause";

        return ["status" => 1, "message" => "Update query successfully generated!", "updateQuery" => $updateSQL, "whereClauseParams" => $updateParams['whereClauseParams']];
    }

    public static function prepareDeleteStatement($table, $deleteParams){

        if(empty($deleteParams['whereClause'])){
            return ["status" => 0, "message" => "Where clause cannot be empty! #ERR-DBDELETE-04", "errorCode" => "DBDELETE-04"];
        }

        if(empty($deleteParams['whereClauseParams'])){
            return ["status" => 0, "message" => "Where clause parameters cannot be empty! #ERR-DBDELETE-05", "errorCode" => "DBDELETE-05"];
        }

        $whereClause = $deleteParams['whereClause'];
        $deleteSQL = "DELETE FROM `$table` $whereClause";

        return ["status" => 1, "message" => "Delete query successfully generated!", "deleteQuery" => $deleteSQL, "whereClauseParams" => $deleteParams['whereClauseParams']];

    }
}