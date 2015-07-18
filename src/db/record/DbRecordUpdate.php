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
use soloproyectos\db\DbConnector;
use soloproyectos\db\exception\DbException;
use soloproyectos\db\record\DbRecordAbstract;
use soloproyectos\db\record\DbRecordCell;
use soloproyectos\text\Text;

/**
 * Class DbRecordUpdate.
 *
 * @package Db\Record
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecordUpdate extends DbRecordAbstract
{
    /**
     * Record ID.
     * @var mixed
     */
    private $_id;
    
    /**
     * Constructor.
     * 
     * @param DbConnector  $db        Database connector
     * @param string       $tableName Table name
     * @param scalar|array $id        Record ID
     */
    public function __construct($db, $tableName, $id)
    {
        parent::__construct($db, $tableName);
        $this->_id = $id;
        $this->refresh();
    }
    
    /**
     * Updates the record.
     * 
     * @return void
     */
    public function save()
    {
        // pairs columns values
        $sqlColumnValues = "";
        foreach ($this->record as $columnName => $cell) {
            if ($columnName == "updated_on" || $cell->hasChanged()) {
                $key = Db::quoteId($columnName);
                $value = $cell->hasChanged()? $this->db->quote($cell->getValue()): "utc_timestamp()";
                $sqlColumnValues = Text::concat(", ", $sqlColumnValues, "$key = $value");
            }
        }
        
        // updates the record
        if (!Text::isEmpty($sqlColumnValues)) {
            $this->db->exec(
                "update " . Db::quoteId($this->tableName) . " set $sqlColumnValues where id = ?",
                $this->_id
            );
        }
    }
    
    /**
     * Deletes the record.
     * 
     * @return integer
     */
    public function delete()
    {
        return $this->db->exec(
            "delete from " . Db::quoteId($this->tableName) . " where id = ?",
            $this->_id
        );
    }
    
    /**
     * Refreshes the record from the database.
     * 
     * @return void
     */
    public function refresh()
    {
        // sql columns
        $sqlColumns = "";
        foreach ($this->record as $key => $cell) {
            $sqlColumns = Text::concat(", ", $sqlColumns, Db::quoteId($key));
        }
        
        // fetches the record
        $rows = $this->db->query(
            "select $sqlColumns from " . Db::quoteId($this->tableName) . " where id = ?",
            $this->_id
        );
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (!is_numeric($key)) {
                    $cell = $this->record[$key];
                    $cell->setDefaultValue($value);
                    $cell->setChanged(false);
                }
            }
        }
    }
}
