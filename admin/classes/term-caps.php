<?php
require_once 'term-caps-group.php';

/**
 * Class TermCaps
 */
class TermCaps {

	/**
	 * Option name for saving the groups
	 */
	const OPTION_NAME = 'term_caps_groups';

	public static $IGNORED_TAXONOMIES = array( 'post_format', 'nav_menu', 'link_category' );

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
	 * @return TermCapsGroup|null Newly created group object
	 */
	public function add_group ( $title, $name = null ) {

		// No duplicates
		$new_group = new TermCapsGroup( $title, $name );
		if ( $this->get_group( $new_group->name ) ) {
			return null;
		}

		// Add the new group object and return it
		$this->groups[ ] = $new_group;
		return $new_group;
	}

	/**
	 * @param string $target_name
	 *
	 * @return null|TermCapsGroup A TermCapsGroup object or null if not found
	 */
	public function get_group ( $target_name ) {

		foreach ( $this->groups as $this_group ) {
			if ( $target_name == $this_group->name ) {
				return $this_group;
			}
		}

		// Didn't find the target name
		return null;
	}

	/**
	 * Remove a group by name
	 *
	 * @param string $name
	 */
	public function remove_group ( $name ) {

		for ( $i = 0; $i < count( $this->groups ); $i++ ) {
			if ( $this->groups[ $i ]->name == $name ) {
				array_splice( $this->groups, $i, 1 );
			}
		}
	}

	/**
	 * @return string[] Array of all allowed taxonomies across all groups
	 */
	public function get_managed_taxonomies () {
		$managed_taxonomies = array();

		foreach ( $this->groups as $this_group ) {

			// Is the current user covered under this group?
			if ( $this_group->is_user_covered() ) {
				foreach ( $this_group->taxonomies as $this_tax ) {
					$managed_taxonomies[ ] = $this_tax->taxonomy_name;
				}
			}
		}

		return array_unique( $managed_taxonomies );
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

			// ToDo: Error checking needed, detect bad data and report on it
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
					$this->allowed_terms = array_merge( $this->allowed_terms, $this_tax_obj->get_allowed_term_ids() );
				}
			}
		}

		// Normalize the list of allowed term IDs
		$this->allowed_terms = array_unique( $this->allowed_terms );
	}
}