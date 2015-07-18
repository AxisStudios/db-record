<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
namespace soloproyectos\db\record;
use soloproyectos\arr\Arr;
use soloproyectos\db\Db;
use soloproyectos\db\exception\DbException;
use soloproyectos\db\record\DbRecordAbstract;
use soloproyectos\db\record\DbRecordCell;
use soloproyectos\text\Text;

/**
 * Class DbRecordInsert.
 *
 * @package Db\Record
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecordInsert extends DbRecordAbstract
{
    /**
     * Inserts a record into the table.
     * 
     * @return void
     */
    public function save()
    {
        // sql columns and values
        $sqlColumns = "";
        $sqlValues = "";
        foreach ($this->record as $columnName => $cell) {
            if (in_array($columnName, ["created_on", "updated_on"]) || $cell->hasChanged()) {
                $key = Db::quoteId($columnName);
                $value = $cell->hasChanged()? $this->db->quote($cell->getValue()): "utc_timestamp()";
                $sqlColumns = Text::concat(", ", $sqlColumns, $key);
                $sqlValues = Text::concat(", ", $sqlValues, $value);
            }
        }
        
        $this->db->exec(
            "insert into " . Db::quoteId($this->tableName) . " ($sqlColumns) values ($sqlValues)"
        );
    }
}
