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

namespace MerciKI\Interfaces;

/**
 * Interface permettant la demande d'implémenter
 * des classes utilisables pour l'authentification
 * et la gestion des comptes de connexion.
 */
interface IUserTable {

	/**
	 * Tente de connecté un utilisateur
	 *
	 * @param String login Le login de l'utilisateur
	 * @param String password Le password de l'utilisateur
	 * @return Modele L'utilisateur
	 */
	public function getUser($login, $password);
}

