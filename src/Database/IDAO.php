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

namespace MerciKI\Database;

use MerciKI\Models\Entity;

interface IDAO {
	public function newEntity();
	
	public function create(IDatabaseSerializable &$aEntity);
	
	public function edit(IDatabaseSerializable &$aEntity);
	
	/**
	 * @param String|Integer $id Identity of the object
	 */
	public function get($id);
	
	public function delete(IDatabaseSerializable &$aEntity);
}