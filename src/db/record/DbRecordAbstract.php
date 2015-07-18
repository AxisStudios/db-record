<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
namespace soloproyectos\db\record;
use \ArrayAccess;
use soloproyectos\arr\Arr;
use soloproyectos\db\Db;
use soloproyectos\db\DbConnector;
use soloproyectos\db\exception\DbException;
use soloproyectos\db\record\DbRecordCell;

/**
 * Class DbRecordAbstract.
 *
 * @package Db\Record
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
abstract class DbRecordAbstract implements ArrayAccess
{
    /**
     * Database connector.
     * @var DbConnector
     */
    protected $db;
    
    /**
     * Table name.
     * @var string
     */
    protected $tableName = "";
    
    /**
     * Internal record.
     * @var array of values
     */
    protected $record = [];
    
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
        $this->_fetchColumnNames();
    }
    
    /**
     * Saves the record.
     * 
     * @return void
     */
    abstract public function save();
    
    /**
     * Gets a column value.
     * 
     * @param string $columnName Column name
     * 
     * @return string
     */
    public function get($columnName)
    {
        if (!Arr::exist($this->record, $columnName)) {
            throw new DbException("Column not found: $columnName");
        }
        $cell = $this->record[$columnName];
        return $cell->getValue();
    }
    
    /**
     * Sets a column value.
     * 
     * @param string $columnName Column name
     * @param mixed  $value      Value
     * 
     * @return void
     */
    public function set($columnName, $value)
    {
        if (!Arr::exist($this->record, $columnName)) {
            throw new DbException("Column not found: $columnName");
        }
        $cell = $this->record[$columnName];
        $cell->setValue($value);
    }
    
    /**
     * Does the column exist?
     * 
     * This function implements ArrayAccess::offsetExists().
     *
     * @param string $columnName Column name
     *
     * @return boolean
     */
    public function offsetExists($columnName)
    {
        return Arr::exist($this->record, $columnName);
    }

    /**
     * Gets the column value.
     * 
     * This function implements ArrayAccess::offsetGet().
     *
     * @param string $columnName Column name
     *
     * @return string|null
     */
    public function offsetGet($columnName)
    {
        return $this->get($columnName);
    }

    /**
     * Sets the column value.
     * 
     * This function implements ArrayAccess::offsetSet().
     *
     * @param string $columnName Column name
     * @param mixed  $value      Value
     *
     * @return void
     */
    public function offsetSet($columnName, $value)
    {
        $this->set($columnName, $value);
    }

    /**
     * Removes a column.
     * 
     * This function implements ArrayAccess::offsetUnset().
     *
     * @param string $columnName Column name
     *
     * @return void
     */
    public function offsetUnset($columnName)
    {
        Arr::delete($this->record, $columnName);
    }
    
    /**
     * Retrieves the column names of the table.
     * 
     * @return void
     */
    private function _fetchColumnNames()
    {
        $rows = $this->db->query("show columns from " . Db::quoteId($this->tableName));
        foreach ($rows as $row) {
            $cell = new DbRecordCell($row["Default"]);
            $cell->setPrimaryKey($row["Key"] == "PRI");
            $this->record[$row["Field"]] = $cell;
        }
    }
}
