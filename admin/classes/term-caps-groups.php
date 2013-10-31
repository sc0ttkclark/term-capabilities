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
	public $groups = array();

	/**
	 * @var bool $covered Is the current user covered under any group rules?
	 */
	private $covered = false;

	/**
	 * @var int[] $allowed_terms Array of term IDs the current user can utilize, if covered
	 */
	private $allowed_terms = array();

	/**
	 *
	 */
	public function __construct() {

		//$this->load();
		//$this->init_coverage_info();
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

		// Store all the coverage information now that
		$this->init_coverage_info();
	}

	/**
	 *
	 */
	public function save () {
		update_option( self::OPTION_NAME, serialize( $this->groups ) );
	}

	/**
	 *
	 */
	private function init_coverage_info() {

		// Check if the current user is covered under any of the groups
		foreach ( $this->groups as $this_group ) {

			if ( $this_group->is_user_covered() ) {
				$this->covered = true;

				// ToDo: Add to the list of allowed terms (IDs) from this group
			}
		}

	}
}