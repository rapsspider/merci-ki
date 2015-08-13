<?php
/**
 * Copyright (c) 2015  Jason BOURLARD
 *                     Quentin DOUZIECH
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Jason BOURLARD<jason.bourlard@gmail.com>
 *
 * @copyright   Copyright (c) 2015  Jason BOURLARD, Quentin DOUZIECH
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace MerciKI\Exception;

use \Exception;
use MerciKI\Network\Response;

/**
 * This exception must be throw when the user is not connected
 *  and access to a page which need an connected user.
 */
class NotConnectedUser extends MerciKIException {

	/**
	 * HTTP status code to return.
	 */
	protected $code = 403;

}


?>