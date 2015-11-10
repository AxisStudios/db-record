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
use soloproyectos\db\DbConnector;
use soloproyectos\db\exception\DbException;
use soloproyectos\db\record\DbRecordAbstract;
use soloproyectos\db\record\DbRecordInsert;
use soloproyectos\db\record\DbRecordUpdate;
use soloproyectos\text\Text;

/**
 * Class DbRecord.
 *
 * @package Db\Record
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecord implements ArrayAccess
{
    /**
     * Database connector.
     * @var DbConnector
     */
    private $_db;
    
    /**
     * Record.
     * @var DbRecordAbstract
     */
    private $_record;
    
    /**
     * Table name.
     * @var string
     */
    private $_tableName = "";
    
    /**
     * Is the record updated?
     * @var boolean
     */
    private $_isUpdated = true;
    
    /**
     * Record ID.
     * @var string
     */
    private $_id;
    
    /**
     * Constructor.
     * 
     * @param DbConnector  $db        Database connector
     * @param string       $tableName Table name
     * @param scalar|array $id        Record ID (not required)
     */
    public function __construct($db, $tableName, $id = null)
    {
        $this->_db = $db;
        $this->_tableName = $tableName;
        $this->_id = $id;
        $this->_record = func_num_args() < 3
            ? new DbRecordInsert($db, $tableName)
            : new DbRecordUpdate($db, $tableName, $id);
    }
    
    /**
     * Inserts or updates the record.
     * 
     * @return void
     */
    public function save()
    {
        $this->_record->save();
        
        // retrieves the inserted record
        if ($this->_record instanceof DbRecordInsert) {
            $row = $this->_db->query("select last_insert_id() as id");
            $this->_id = $row["id"];
        }
        
        $this->_isUpdated = false;
    }
    
    /**
     * Deletes the record.
     * 
     * @return integer
     */
    public function delete()
    {
        if ($this->_record instanceof DbRecordInsert) {
            throw new DbException("The record is still not saved");
        }
        $this->_record->delete();
        $this->_isUpdated = false;
    }
    
    /**
     * Refreshes the record from the database.
     * 
     * @return void
     */
    public function refresh()
    {
        if ($this->_record instanceof DbRecordInsert) {
            throw new DbException("The record is still not saved");
        }
        $this->_record = new DbRecordUpdate($this->_db, $this->_tableName, $this->_id);
        $this->_isUpdated = true;
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
        return isset($this->_record[$columnName]);
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
        if (!$this->_isUpdated) {
            $this->refresh();
        }
        return $this->_record[$columnName];
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
        if (!$this->_isUpdated) {
            $this->refresh();
        }
        $this->_record[$columnName] = $value;
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
        unset($this->_record[$columnName]);
    }
}
