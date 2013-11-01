<?php
require_once 'term-caps-group.php';

/**
 * Class TermCapsGroups
 */
class TermCapsGroups {

	/**
	 * Option name for saving the groups
	 */
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
	public $allowed_terms = array();

	/**
	 * @param string $title Descriptive tile
	 * @param string|null $name Slugified identifier (will use sanitized version of $title if omitted)
	 *
	 * @return TermCapsGroup Newly created group object
	 */
	public function add_group ( $title, $name = null ) {
		$new_group = new TermCapsGroup( $title, $name );

		// ToDo: We need to make sure the name is unique
		$this->groups[ $new_group->name ] = $new_group;

		return $new_group;
	}

	/**
	 * Remove a group by name
	 *
	 * @param string $name
	 */
	public function remove_group ( $name ) {
		if ( isset( $this->groups[ $name ] ) ) {
			unset( $this->groups[ $name ] );
		}
	}

	/**
	 * @return bool Whether or not the current user is covered under any group restriction rules
	 */
	public function is_current_user_covered () {
		return $this->covered;
	}

	/**
	 * Load the persisted groups data
	 *
	 * The function has the side effect of setting the covered flag and looking up all allowed terms if the
	 * current user is covered
	 */
	public function load () {
		$groups = get_option( self::OPTION_NAME );
		if ( false !== $groups ) {
			$this->groups = unserialize( $groups );
		}

		// Store all the coverage information that we may need to know about later
		$this->init_coverage_info();
	}

	/**
	 * Save groups data
	 */
	public function save () {
		update_option( self::OPTION_NAME, serialize( $this->groups ) );
	}

	/**
	 * Called internally after load().  Initialize the basic coverage information for the current user
	 */
	private function init_coverage_info () {

		foreach ( $this->groups as $this_group ) {

			// Is the current user covered under this group?
			if ( $this_group->is_user_covered() ) {
				$this->covered = true; // Flag the current user as being under coverage

				// Add all allowed term IDs for this group
				foreach ( $this_group->taxonomies as $this_tax_obj ) {
					$this->allowed_terms = array_merge($this->allowed_terms, $this_tax_obj->get_allowed_term_ids());
				}
			}
		}

		// Normalize the list of allowed term IDs
		$this->allowed_terms = array_unique( $this->allowed_terms );
	}
}