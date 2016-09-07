<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
namespace soloproyectos\db\record;
use soloproyectos\text\Text;

/**
 * DbRecordTable class.
 *
 * @package Db
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecordTable
{
    /**
     * Database connector.
     * @var DbConnector
     */
    protected $db = null;
    
    /**
     * Table name.
     * @var string
     */
    protected $tableName = "";
    
    /**
     * Constructor.
     * 
     * @param DbConnector $db        Database connector
     * @param string      $tableName Table name
     */
    public function __construct($db, $tableName)
    {
        $this->db = $db;
        $this->tableName = $tableName;
    }
    
    /**
     * Selects a record.
     * 
     * Examples:
     * ```php
     * $r = new DbRecordTable($db, "table0");
     * list($title, $createdAt) = $r->select(["title", "created_at"], 17);
     * echo "title: $title, created at: $createdAt";
     * ```
     * 
     * @param array       $colPaths Column paths
     * @param mixed|array $pk       Primary key
     * 
     * @return mixed[]
     */
    public function select($colPaths, $pk)
    {
        $ret = [];
        $r = new DbRecord($this->db, $this->tableName, $pk);
        
        // registers columns
        $cols = $this->_regColumns($r, $colPaths);
        foreach ($cols as $col) {
            array_push($ret, $col->getValue());
        }
        
        return $ret;
    }
    
    /**
     * Inserts a record.
     * 
     * This function return the record ID.
     * 
     * Example:
     * ```php
     * // inserts a record and prints the record ID
     * $t = new DbRecordTable($db, "table0");
     * $id = $t->insert(["title" => "Title", "created_at" => date("Y-m-d H:i:s")]);
     * echo "Inserted record ID: $id";
     * ```
     * 
     * @param array $colVals Column values
     * 
     * @return mixed
     */
    public function insert($colVals)
    {
        return $this->save($colVals);
    }
    
    /**
     * Updates a record.
     * 
     * This function return the record ID.
     * 
     * Examples:
     * ```php
     * // updates a record
     * // the following code assumes that the primary key is 'id'
     * $t = new DbRecordTable($db, "table0");
     * $t->update(["title" => "Title $id"], 17);
     * 
     * // updates a record
     * // in this case the primary key is called 'pk'
     * $t = new DbRecordTable($db, "table0");
     * $t->update(["title" => "Title $id"], ["pk" => 17]);
     * ```
     * 
     * @param array       $colVals Column values
     * @param mixed|array $pk      Primary key
     * 
     * @return mixed
     */
    public function update($colVals, $pk)
    {
        return $this->save($colVals, $pk);
    }
    
    /**
     * Deletes a record.
     * 
     * Example:
     * ```php
     * // deletes a record
     * // the following code assumes that the primary key is 'id'
     * $t = new DbRecordTable($db, "table0");
     * $t->delete(17);
     * 
     * // deletes a record
     * // in this case the primary key is called 'pk'
     * $t = new DbRecordTable($db, "table0");
     * $t->delete(["pk" => 17]);
     * ```
     * 
     * @param mixed|array $pk Primary key
     * 
     * @return void
     */
    public function delete($pk)
    {
        $r = new DbRecord($this->db, $this->tableName, $pk);
        $r->delete();
    }
    
    /**
     * Updates or inserts a record.
     * 
     * This function returns the the record ID.
     * 
     * @param array       $colVals Column values
     * @param mixed|array $pk      Primary key (not required)
     * 
     * @return mixed
     */
    public function save($colVals, $pk = ["id" => ""])
    {
        $r = new DbRecord($this->db, $this->tableName, $pk);
        
        // registers columns and saves
        $cols = $this->_regColumns($r, array_keys($colVals));
        $vals = array_values($colVals);
        foreach ($cols as $i => $col) {
            $col->setValue($vals[$i]);
        }
        $r->save();
        
        $pk = $r->getPrimaryKey();
        return $pk->getValue();
    }
    
    /**
     * Registers a list of columns.
     * 
     * @param DbRecord $record   Record
     * @param string[] $colPaths Column paths
     * 
     * @return DbRecordColumn[]
     */
    private function _regColumns($record, $colPaths)
    {
        $ret  = [];
        foreach ($colPaths as $colPath) {
            array_push($ret, $record->regColumn($colPath));
        }
        return $ret;
    }
}
