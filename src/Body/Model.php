<?php
/**
 * Copyright (c) 2015  Jason BOURLARD
 *                     Quentin DOUZIECH
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 *
 * @author      Jason BOURLARD<jason.bourlard@gmail.com>
 *
 * @copyright   Copyright (c) 2015  Jason BOURLARD, Quentin DOUZIECH
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace MerciKI\Body;

use \JsonSerializable;
use MerciKI\Database\IDatabaseSerializable;

/**
 * Entity is a class which represents a data in database.
 */
abstract class Model implements JsonSerializable, IDatabaseSerializable {

	/**
     * An entity is new is it doesn't exist in database.
     * @var boolean
	 */
	protected $isNew = false;

	/**
	 * Name of the table in the database.
	 */
	protected $table = false;
	
	/**
	 * List of modified attributes
	 */
	protected $dataChanged = [];
	
	/**
	 * Attributes and associated values.
	 */
	protected $data = [];

	/**
	 * Name of the primary key of this entity.
	 * @var String
	 */
	public $primaryKey = 'id';

	/**
	 * Attributes list which can have this entity.
	 *
     * Allow you to associate a type to an attribut.
	 * Type : 'i' Integer
	 *		  's' String.
	 *		  'b' BLOB
	 *		  'd' Decimal.
	 *
	 */
	public $attributes = [
		'id' => [
			'type' => 's',
			'column' => 'id'
		]
	];
	
	/**
	 * Default constructor.
	 * Initialize its attributes.
     *
     * @param Array $array Array of attributes to set.
	 */
	public function __construct(array $array = []) {
		$keys = array_keys($this->attributes);

        $this->set($array);
	}
	
	/**
	 * Return true if this entity is new.
	 * @return boolean
	 */
	public function isNew() {
		return $this->isNew;
	}
	
	/** 
	 * Setter on the isNew attribute.
	 * @param Boolean $bool Its new value
	 */
	public function setIsNew($bool) {
		$this->isNew = $bool;
	}
	
	/**
	 * Return the id of this entity.
	 * @return String|Integer The id of this entity.
	 */
	public function getId() {
		return isset($this->data['id']) ? $this->data['id'] : null;
	}
	
	/**
	 * Return a list of modified attributes.
	 * @return Array
	 */
	public function getDataChanged() {
		return $this->dataChanged;
	}
	
	/**
	 * Return the type of the attribut passed in parameter.
	 * @param String $attr Attribute
	 * @return Char Type of attribut.
	 */
	public function getType($attr) {
		return isset($this->attributes[$attr]['type'])
				   ? $this->attributes[$attr]['type']
				   : null;
	}
	
	/**
	 * Return the number of modified attributes.
	 * @return Integer Number of modified attributes.
	 */
	public function countDataChanged() {
		return count($this->dataChanged);
	}
	
	/**
	 * Magic setter on an attribute.
	 * Example : $entity->id = $blabla;  <=>  __set('id', $blabla);
     *
	 * @param String name  Name of the attribute to set.
	 * @param String value Value of the attribute.
	 */
	public function __set( $name, $value) {
		$this->_set($name, $value);
	}
	
	/**
	 * Magic getter on an attribute.
	 * Example : $entity->id;  <=>  __get('id');
     *
	 * @param String name Name of the attribute.
	 * @return Object     Value of the attribute.
	 */
	public function __get($name) {
		if(array_key_exists($name, $this->data)) return $this->data[$name];
		return null;
	}
	
	/**
	 * Update this entity :
     * - Update an attribute passing first its name then its value.
     * - Update multiple attribute passing an array of attributes in keys.
     *
     * @param $var Array|String Name of an attribute or an array of attributes
     *             to update.
     * @param $val Object|null Value of the attribute or null.
	 */
	public function set($var = [], $val = null) {
		if(empty($var)) return;
		
		if(!is_array($var)) $var = [$var => $val];
		foreach($var as $att => $val) $this->_set($att, $val);
	}
	
	/**
	 * Update an attribute passing first its name then its value.
     *
     * @param $var String Name of an attribute or an array of attributes to update.
     * @param $val Object Value of the attribute or null.
	 */
	protected function _set ($var, $val) {
        $this->data[$var] = $val;
		if(isset($this->attributes[$var]) && !in_array($var, $this->dataChanged)) {
            $this->dataChanged[] = $var;
		}
	}
	
	/**
	 * Encode this entity as an array.
	 * @return Array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
	
	/**
	 * Encode this entity as an array.
	 * @return Array
	 */
	public function toArray() {		
		return $this->data;
	}
}

?>