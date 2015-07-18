<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
namespace soloproyectos\db\record;

/**
 * Class DbRecordCell.
 *
 * @package Db\Record
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/db-record/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/db-record
 */
class DbRecordCell
{
    /**
     * Default value.
     * @var mixed
     */
    private $_defaultValue;
    
    /**
     * Value.
     * @var mixed
     */
    private $_value;
    
    /**
     * Is the column a primary key?
     * @var boolean
     */
    private $_isPrimaryKey = false;
    
    /**
     * Has the value changed?
     * @var boolean
     */
    private $_hasChanged = false;
    
    /**
     * Constructor.
     * 
     * @param mixed $defaultValue Default value (not required)
     */
    public function __construct($defaultValue = null)
    {
        $this->_defaultValue = $defaultValue;
    }
    
    /**
     * Is the column a primary key?
     * 
     * @return boolean
     */
    public function isPrimaryKey()
    {
        return $this->_isPrimaryKey;
    }
    
    /**
     * Sest the primary key state.
     * 
     * @param boolean $value Value
     * 
     * @return void
     */
    public function setPrimaryKey($value)
    {
        $this->_isPrimaryKey = $value;
    }
    
    /**
     * Gets the default value.
     * 
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }
    
    /**
     * Sets the default value.
     * 
     * @param mixed $value Value
     * 
     * @return void
     */
    public function setDefaultValue($value)
    {
        $this->_defaultValue = $value;
    }
    
    /**
     * Gets the value.
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->_hasChanged? $this->_value: $this->_defaultValue;
    }
    
    /**
     * Sets the value.
     * 
     * @param mixed $value Value
     * 
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
        $this->_hasChanged = true;
    }
    
    /**
     * Has the value changed?
     * 
     * @return boolean
     */
    public function hasChanged()
    {
        return $this->_hasChanged;
    }
    
    /**
     * Sets change status.
     * 
     * @param boolean $value Value
     * 
     * @return void
     */
    public function setChanged($value)
    {
        $this->_hasChanged = $value;
    }
}
