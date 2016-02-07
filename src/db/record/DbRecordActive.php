<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
namespace soloproyectos\db\record;
use soloproyectos\db\Db;

/**
 * DbRecordActive class.
 * 
 * This class is similar to the DbRecord one, except that it implements
 * the magic '__get' and '__set' methods.
 *
 * @package Db
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecordActive extends DbRecord
{
    /**
     * Constructor.
     * 
     * Examples:
     * ```php
     * // the following instance represents a NEW record
     * $r = new DbRecord($db, "my_table");
     * 
     * // the following instance represents an EXISTING record
     * $r = new DbRecord($db, "my_table", $id);
     * 
     * // By default the primary key name is "id".
     * // But you can change it in the constructor:
     * $r = new DbRecord($db, "my_table", ["pk" => ""]);  // new record
     * $r = new DbRecord($db, "my_table", ["pk" => $id]); // existing record
     * ```
     * 
     * @param DbConnector $db        Database connector
     * @param string      $tableName Table name
     * @param mixed|array $pk        Primary key (not required)
     */
    public function __construct($db, $tableName, $pk = ["id" => ""])
    {
        parent::__construct($db, $tableName, $pk);
        
        // registers all columns
        $sql = "show columns from " . Db::quoteId($tableName);
        $rows = $db->query($sql);
        foreach ($rows as $row) {
            $this->regColumn($row["Field"]);
        }
    }
    
    /**
     * Gets a column value.
     * 
     * @param string $colName Column name
     * 
     * @return mixed
     */
    public function __get($colName)
    {
        $col = $this->regColumn($colName);
        return $col->getValue();
    }
    
    /**
     * Sets a column value.
     * 
     * @param string $colName Column name
     * @param mixed  $value   Value
     * 
     * @return void
     */
    public function __set($colName, $value)
    {
        $col = $this->regColumn($colName);
        $col->setValue($value);
    }
}
