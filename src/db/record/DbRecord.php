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
use soloproyectos\text\Text;

/**
 * With this class you can insert, edit or delete records in a database table.
 * 
 * The only condition is that tables MUST HAVE a single auto-incrementable primary key.
 *
 * @package Db
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecord
{
    /**
     * Database connector.
     * @var DbConnector
     */
    private $_db = null;
    
    /**
     * Table name.
     * @var string
     */
    private $_tableName = "";
    
    /**
     * Primary key.
     * @var DbRecordColumn
     */
    private $_primaryKey = null;
    
    /**
     * List of columns.
     * @var DbRecordColumn[]
     */
    private $_columns = [];
    
    /**
     * List of left join tables.
     * @var DbRecordLeftJoin[]
     */
    private $_leftJoins = [];
    
    /**
     * Is the record updated?
     * @var boolean
     */
    private $_isUpdated = true;
    
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
        // gets the primary key and value
        $pkName = "";
        $pkValue = "";
        if (!is_array($pk)) {
            $pk = ["id" => "$pk"];
        }
        foreach ($pk as $key => $value) {
            $pkName = "$key";
            $pkValue = "$value";
            break;
        }
        
        $this->_db = $db;
        $this->_tableName = $tableName;
        $this->_primaryKey = new DbRecordColumn($this, $pkName);
        if (!Text::isEmpty($pkValue)) {
            $this->_primaryKey->setValue($pkValue);
        }
    }
    
    /**
     * Gets the list of columns.
     * 
     * @return DbRecordColumn[]
     */
    public function getColumns()
    {
        return $this->_columns;
    }
    
    /**
     * Gets the list of 'left joins'
     * 
     * @return DbRecordLeftJoin[]
     */
    public function getLeftJoins()
    {
        return $this->_leftJoins;
    }
    
    /**
     * Gets the primary key column.
     * 
     * @return DbRecordColumn
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }
    
    /**
     * Is the record updated?
     * 
     * @return boolean
     */
    public function isUpdated()
    {
        return $this->_isUpdated;
    }
    
    /**
     * Inserts or updates the current record.
     * 
     * @return void
     */
    public function save()
    {
        // first saves the 'left join' tables
        foreach ($this->_leftJoins as $leftJoin) {
            $leftJoin->save();
        }
        
        $columns = $this->_getChangedColumns();
        if ($this->_primaryKey->hasChanged()) {
            // update
            if (count($columns) > 0) {
                $this->_db->exec($this->_getUpdateStatement($columns));
            }
        } else {
            // insert
            $this->_db->exec($this->_getInsertStatement($columns));
            $row = $this->_db->query("select last_insert_id() as id");
            $this->_primaryKey->setValue($row["id"]);
        }
        
        // resets columns
        foreach ($columns as $column) {
            $column->reset();
        }
        $this->_isUpdated = false;
    }
    
    /**
     * Fetches column values from database.
     * 
     * @return void
     */
    public function fetch()
    {
        if ($this->_primaryKey->hasChanged()) {
            // gets the columns that haven't changed
            $columns = array_diff($this->_columns, $this->_getChangedColumns());
            
            // fills columns
            if (count($columns) > 0) {
                $row = $this->_db->query($this->_getSelectStatement($columns));
                foreach ($columns as $column) {
                    $column->setDbValue($row[$column->getName()]);
                }
            }
        }
        $this->_isUpdated = true;
    }
    
    /**
     * Deletes the current record.
     * 
     * @return void
     */
    public function delete()
    {
        $this->_db->exec($this->_getDeleteStatement());
        $this->_isUpdated = true;
    }
    
    /**
     * Deletes the current record and olso de linked records.
     * 
     * @return void
     */
    public function deleteAll()
    {
        // first deletes linked records
        foreach ($this->_leftJoins as $leftJoin) {
            $record = $leftJoin->getRecord();
            $record->deleteAll();
        }
        
        // and finally deletes the current record
        $this->delete();
    }
    
    /**
     * Registers a column.
     * 
     * The following example registers a simple column
     * ```php
     * $col = $r->regCol("col0");
     * ```
     * 
     * The following example registers a complex column
     * ```php
     * // 'table1' is linked to 'table0' by the 'id = table1_id' condition
     * $col = $r->regCol("table1[id = table1_id].col0");
     * 
     * // or more briefly
     * $col = $r->regCol("table1.col0");
     * ```
     * 
     * This method adds a column only if not already added.
     * 
     * @param string $colPath Column path
     * 
     * @return DbRecordColumn
     */
    public function regColumn($colPath)
    {
        $table = $this;
        $colName = $colPath;
        
        $pos = strrpos($colPath, ".");
        if ($pos !== false) {
            $tableExp = trim(substr($colPath, 0, $pos));
            $colName = trim(substr($colPath, $pos + 1));
            $table = $this->regTable($tableExp);
        }
        
        $column = $table->_searchColumn($colName);
        if ($column === null) {
            $column = $table->_addColumn(new DbRecordColumn($table, $colName));
        }

        return $column;
    }
    
    /**
     * Registers a 'left join' table.
     * 
     * Example:
     * ```php
     * // 'table1' is linked to 'table0' by the 'id = table1_id' condition
     * $t = $r->regTable("table1[id = table1_id]");
     * 
     * // or more briefly
     * $t = $r->regTable("table1");
     * ```
     * 
     * @param string $tablePath Table path
     * 
     * @return DbRecord
     */
    public function regTable($tablePath)
    {
        $tableName = $tablePath;
        $pkName = "id";
        $colName = "{$tableName}_id";

        if (preg_match("/(.*)\s*\[(.*)\]$/U", $tablePath, $matches)) {
            $tableName = $matches[1];
            $colName = trim($matches[2]);

            $pos = strpos($colName, "=");
            if ($pos !== false) {
                $pkName = trim(substr($colName, 0, $pos));
                $colName = trim(substr($colName, $pos + 1));
            }
        }

        $column = $this->regColumn($colName);
        $record = $column->getRecord();
        $table = $record->_searchTable($tableName, $pkName, $column->getName());
        if ($table === null) {
            $table = $record->_addTable(
                new DbRecordLeftJoin(
                    new static($this->_db, $tableName, [$pkName => $column->getValue()]),
                    $column
                )
            );
        }

        return $table;
    }
    
    /**
     * Adds colum.
     * 
     * @param DbRecordColumn $column Column
     * 
     * @return DbRecordColumn
     */
    private function _addColumn($column)
    {
        array_push($this->_columns, $column);
        $this->_isUpdated = false;
        return $column;
    }
    
    /**
     * Adds a 'left join' table.
     * 
     * @param DbRecordLeftJoin $leftJoin Left join table
     * 
     * @return DbRecord
     */
    private function _addTable($leftJoin)
    {
        array_push($this->_leftJoins, $leftJoin);
        return $leftJoin->getRecord();
    }
    
    /**
     * Searches column by name.
     * 
     * @param string $colName Column name
     * 
     * @return DbRecordColumn|null
     */
    private function _searchColumn($colName)
    {
        $ret = null;
        foreach ($this->_columns as $column) {
            if ($column->getName() == $colName) {
                $ret = $column;
                break;
            }
        }
        return $ret;
    }
    
    /**
     * Searches a 'left join' table by its name and column index.
     * 
     * @param string $tableName Table name
     * @param string $pkName    Primary key name
     * @param string $colName   Column index name
     * 
     * @return DbRecord
     */
    private function _searchTable($tableName, $pkName, $colName)
    {
        $ret = null;
        foreach ($this->_leftJoins as $leftJoin) {
            $record = $leftJoin->getRecord();
            $pk = $record->getPrimaryKey();
            $column = $leftJoin->getColumn();
            if ($record->_tableName == $tableName
                && $pk->getName() == $pkName
                && $column->getName() == $colName
            ) {
                $ret = $record;
                break;
            }
        }
        return $ret;
    }
    
    /**
     * Gets the columns that have changed.
     * 
     * @return DbRecordColumn[]
     */
    private function _getChangedColumns()
    {
        return array_filter(
            $this->_columns,
            function ($column) {
                return $column->hasChanged();
            }
        );
    }
    
    /**
     * Gets the SQL SELECT statement.
     * 
     * @param DbRecordColumn $columns List of columns
     * 
     * @return string
     */
    private function _getSelectStatement($columns)
    {
        // list of column names separated by commas
        $cols = implode(
            ", ",
            array_map(
                function ($column) {
                    return Db::quoteId($column->getName());
                },
                $columns
            )
        );
        
        $tableName = Db::quoteId($this->_tableName);
        $pkName = Db::quoteId($this->_primaryKey->getName());
        $pkValue = $this->_db->quote($this->_primaryKey->getValue());
        return "select $cols from $tableName where $pkName = $pkValue";
    }
    
    /**
     * Gets the SQL UPDATE statement.
     * 
     * @param DbRecordColumn $columns List of columns
     * 
     * @return string
     */
    private function _getUpdateStatement($columns)
    {
        // list of column assignments separated by commas
        $colValues = implode(
            ", ",
            array_map(
                function ($column) {
                    $colName = Db::quoteId($column->getName());
                    $colValue = $this->_db->quote($column->getValue());
                    return "$colName = $colValue";
                },
                $columns
            )
        );
        
        $tableName = Db::quoteId($this->_tableName);
        $pkName = Db::quoteId($this->_primaryKey->getName());
        $pkValue = $this->_db->quote($this->_primaryKey->getValue());
        return "update $tableName set $colValues where $pkName = $pkValue"; 
    }
    
    /**
     * Gets the SQL INSERT statement.
     * 
     * @param DbRecordColumn $columns List of columns
     * 
     * @return string
     */
    private function _getInsertStatement($columns)
    {
        // list of column and values separated by commas
        $colNames = [];
        $colValues = [];
        foreach ($columns as $column) {
            array_push($colNames, Db::quoteId($column->getName()));
            array_push($colValues, $this->_db->quote($column->getValue()));
        }
        $cols = implode(", ", $colNames);
        $vals = implode(", ", $colValues);
        
        $tableName = Db::quoteId($this->_tableName);
        return "insert into $tableName($cols) values($vals)";
    }
    
    /**
     * Gets the SQL DELETE statement.
     * 
     * @return string
     */
    private function _getDeleteStatement()
    {
        $tableName = Db::quoteId($this->_tableName);
        $pkName = Db::quoteId($this->_primaryKey->getName());
        $pkValue = $this->_db->quote($this->_primaryKey->getValue());
        return "delete from $tableName where $pkName = $pkValue";
    }
}
