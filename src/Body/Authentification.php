<?php
/**
 * Framework
 * @author Jason BOURLARD<jason.bourlard@gmail.com>,
 *         Quentin DOUZIECH<quentin.douziech@gmail.com>
 */

namespace MerciKI\Body;

use MerciKI\Interfaces\IUserTable;

/**
 * Classe représentant un controleur
 */
class Authentification {
	
	/**
	 * Index où sera stocker les informations sur
	 * l'utilisateur.
	 * @var String
	 */
	protected static $session_index = 'user';

	/**
	 * Contient la classe représentant le tableau d'utilisateurs
	 * dans la base de données.
	 * @var IUtilisateurTableau
	 */
	protected $user;

	/**
	 * Constructeur par défaut.
	 * @param IUtilisateurTableau tableau Classe réprésentant le tableau
	 *		d'utilisateurs dans la base de données.
	 */
	public function __construct(IUserTable $table) {
		$this->user = $table;
		$this->initialise();
	}

    /**
     * Initialise les sessions
     */
	public function initialise() {
		session_start();
	}

	/**
	 * Connecte un utilisateur.
	 * @param String $username Le login de l'utilisateur voulant se connecter.
	 * @param String $pass	 Le mot de passe de l'utilisateur.
	 * @return boolean TRUE si l'utilisateur a été authentifié.
	 *				 FALSE si l'utilisateur n'a pas pu être authentifié.
	 */
	public function connexion($username, $passe) {
		$user = $this->user->getUser($username, $passe);

		if($user) {
			$_SESSION[self::$session_index] = $user->toArray();
			return true;
		}
		return false;
	}

	/**
	 * Déconnecte un utilisateur.
	 * @return boolean TRUE si l'utilisateur a été déconnecté.
	 *				 FALSE si l'utilisateur n'a pas pu être déconnecté.
	 */
	public function logout() {
		if($this->isConnected()) {
			unset($_SESSION[self::$session_index]);
			return true;
		}
		return false;
	}

	/**
	 * Permet de savoir si un utilisateur est connecté
	 * @return bool TRUE si l'utilisateur est connecté.
	 *			  FALSE si l'utilisateur est déconnecté.
	 */
	public function isConnected() {
		return isset($_SESSION[self::$session_index]) && $_SESSION[self::$session_index] != null;
	}

	/**
	 * Permet de retourner les informations sur l'utilisateur connecté.
	 * @return Modele L'utilisateur.
	 */
	public function getUser() {
		if($this->isConnected()) {
			return $_SESSION[self::$session_index];
		}
		return null;
	}
}

