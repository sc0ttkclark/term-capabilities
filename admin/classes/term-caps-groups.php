<?php
require_once 'term-caps-group.php';

/**
 * Class TermCapsGroups
 */
class TermCapsGroups {

	const OPTION_NAME = 'term_caps_groups';

	/**
	 * @var TermCapsGroup[] $groups
	 */
	// ToDo: This should probably be private with getters/setters to provide access, if needed
	public $groups = array();

	/**
	 * @var bool $covered Is the current user covered under any group rules?
	 */
	private $covered = false;

	/**
	 *
	 */
	public function __construct() {

		/**
		 * ToDo: Perhaps just call load from here?
		 */

		/**
		 * ToDo: If we autoload, maybe iterate through groups and check if the curent user is covered now.
		 * If they are, also store an array of allowed Term IDs and we'll have everything we need to know about
		 * coverage when we need to check it later.
		 */
	}

	/**
	 * @return bool Whether or not the current user is covered under any group restriction rules
	 */
	public function is_covered() {
		return $this->covered;
	}

	/**
	 *
	 */
	public function load () {
		$groups = get_option( self::OPTION_NAME );
		if ( false !== $groups ) {
			$this->groups = unserialize( $groups );
		}
	}

	/**
	 *
	 */
	public function save () {
		update_option( self::OPTION_NAME, serialize( $this->groups ) );
	}
}