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

namespace MerciKI\Database\DAO;

use \PDO;
use \PDOStatement;
use MerciKI\Models\Entity;
use MerciKI\Database\IDAO;
use MerciKI\Database\IDatabaseSerializable;
use MerciKI\Exception\EntityNotExist;

abstract class PDO_DAO implements IDAO {
	
	/** 
	 * PDO object
	 * @var PDO
	 */
	protected $_db;
    
    /**
     * Read only
     * @var bool
     */
    protected $_readOnly = false;
	
	/**
	 * Name of the entity
	 * @var String
	 */
	protected $entity = null;
    
    /**
     * Name of the database to use
     */
    public static $database = 'default';

	/**
	 * Name of the table to use.
	 * @var Array
	 */
	protected $table = [];
	
	/** 
	 * Last request executed.
	 * @var String
	 */
	public $lastRequest;
	
	/**
	 * Default constructor
	 * @param PDO
	 */
	public function __construct(PDO &$db) {
		$this->setDb($db);
	}
	
	/**
	 * Update the PDO Instance.
	 * @param PDO
	 */
	public function setDb(PDO &$db) {
		$this->_db = &$db;
	}
	
	/**
	 * Return a new instance of the entity
	 */
	public function newEntity() {
		$entity = "MerciKI\\Models\\Entities\\" . $this->entity;
		return new $entity();
	}
	
	/**
	 * Add the entity in the database.
	 * @param IDatabaseSerializable $objet The entity to add.
	 */
	public function create(IDatabaseSerializable &$object) {
        if($this->_readOnly) throw new ReadOnlyException();
        
		$vars = [];
		$vals = [];
		$dataChanged = $object->getDataChanged();
		
		foreach($dataChanged as $var) {
			$vars[] = $var;
			$vals[] = ":" . $var . "__";
		}
		
		$this->lastRequest = 'INSERT INTO `' . $this->table . '` (`' . implode('`,`', $vars) . '`)'
						 .' VALUES (' . implode(',', $vals) . ');';
		$req = $this->_db->prepare($this->lastRequest);
        
        echo $this->lastRequest;
        
		$this->bindValues($req, $object);
        
		if(!$req->execute()) {
            /*
                echo "\nPDO::errorInfo():\n";
                print_r($req->errorInfo());
            */
        }
		
		$id = $this->_db->lastInsertId();
		if($id) {
			$object->set($object->primaryKey, $id);
		}
		
		return $req->rowCount();
	}
	
	/**
	 * Modify the entity in the database.
	 * @param IDatabaseSerializable $objet the entity to edit.
	 */
	public function edit(IDatabaseSerializable &$object) {
        if($this->_readOnly) throw new ReadOnlyException();
        
		$str		  = [];
		$editAssign = [];
		$primary	  = $object->primaryKey;
		$dataChanged  = $object->getDataChanged();
		$type		 = self::getType($object->getType($object->getType($primary)));
		
		foreach($dataChanged as $var) $editAssign[] = $var . "=:" . $var . "__";
		
		$this->lastRequest = 'UPDATE `' . $this->table . "`"
						 .' SET ' . implode(',', $editAssign) 
						 .' WHERE ' . $object->primaryKey . '=:id__;';
		$req = $this->_db->prepare($this->lastRequest);
		$this->bindValue($req, ':id__', $object->$primary, $type);
		$this->bindValues($req, $object);
		$req->execute();
		
		return $req->rowCount();
	}
	
	/**
	 * Delete the entity in the database
	 * @param IDatabaseSerializable $objet The entity to delete.
	 */
	public function delete(IDatabaseSerializable &$object) {
        if($this->_readOnly) throw new ReadOnlyException();
        
		$primary = $object->primaryKey;
		$type = self::getType($object->getType($object->getType($primary)));
		$this->lastRequest = 'DELETE FROM `' . $this->table . "`"
						 . ' WHERE ' . $object->primaryKey . '=:id__;';
		$req = $this->_db->prepare($this->lastRequest);
		$this->bindValue($req, ':id__', $object->$primary, $type);
		$req->execute();
		
		return $req->rowCount();
	}
	
	/**
	 * Bind vars to the SQL request.
	 * 
	 * @param PDOStatement $ps The SQL Request.
	 * @param String $search Replaced String.
	 * @param String|Integer $replace The substitute String.
	 */
	protected function bindValues(PDOStatement &$ps, &$object) {
		if($ps == null OR $object->countDataChanged() <= 0) return;
		if($object->countDataChanged() <= 0) return;
		$dataChanged = $object->getDataChanged();
		
		foreach($dataChanged as $var) {
			self::bindValue(
				$ps,
				':' . $var . '__', 
				$object->$var, 
				PDO::PARAM_STR
			);
		}
	}	
	/**
	 * Bind var to the SQL request.
	 * 
	 * @param PDOStatement $ps The SQL Request.
	 * @param String $search Replaced String.
	 * @param String|Integer $replace The substitute String.
	 */
	protected function bindValue(PDOStatement &$ps, $search, $replace, $type) {
		$ps->bindValue(
			$search, 
			$replace, 
			$type
		);
		$this->setToLastRequest(
			$search, 
			$replace, 
			$type
		);
	}
	
	/**
	 * Replace a String in the lastRequest attribute.
	 * @param String $search Replaced String.
	 * @param String|Integer $replace The substitute String.
	 * @param Integer $type Type of the substitute variable.
	 */
	protected function setToLastRequest($search, $replace, $type) {
		switch($type) {
			case PDO::PARAM_STR:
				$replace = "'" . $replace . "'";
				break;
			case PDO::PARAM_INT:
			default:
				break;
		}
		$this->lastRequest = str_replace(
			$search, 
			$replace, 
			$this->lastRequest
		);
	}
	
	/**
	 * Return the associated PDO's type.
	 * Type : 'i' Integer
	 *		's' String.
	 *		'b' BLOB
	 *		'd' Decimal.
	 */
	static public function getType($type = false) {
		switch($type) {
			case 'i':
				return PDO::PARAM_INT;
				break;
			case 's':
			case 'd':
			case 'b':
			default:
				return PDO::PARAM_STR;
				break;
		}
	}
	
	/**
	 * Return an entity which have the id set in parameter.
	 * @param $id
	 * @return Entity
	 */
	public function get($id) {
		$class = 'MerciKI\\Models\\Entities\\' . $this->entity;
        
        $object = new $class;
        
		$primary = $object->primaryKey;
		$type = is_int($id) ? self::getType('i') : self::getType('s');
		
		$this->lastRequest = 'SELECT * FROM `' . $this->table . '` WHERE ' . $primary . '=:id__;';
		$req = $this->_db->prepare($this->lastRequest);
		$this->bindValue($req, ':id__', $id, $type);
		$req->execute();
		
		$new = $req->fetch(\PDO::FETCH_ASSOC);

		if(!$new) throw new EntityNotExist('Entity don\'t exist : ID(' . $id .') !');
		
		return $new ? new $class($new) : null;
	}
}