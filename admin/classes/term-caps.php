<?php
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
	 *
	 * This is currently the only thing that is serialized and saved.
	 */
	public $groups = array();

	/**
	 * @var TermCapsCoverage $coverage
	 */
	private $coverage = null;

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
	 * @return bool Whether or not the current user is covered under any group restriction rules
	 */
	public function is_current_user_covered () {
		return $this->coverage->is_current_user_covered();
	}

	/**
	 * @param string $tax_name
	 *
	 * @return bool
	 */
	public function is_taxonomy_managed ( $tax_name ) {
		return $this->coverage->is_taxonomy_managed( $tax_name );
	}

	/**
	 * @param string $tax_name
	 *
	 * @return int[]|null Array of allowed term IDs for the taxonomy, or null if the tax isn't managed
	 */
	public function get_allowed_term_ids ( $tax_name ) {
		return $this->coverage->get_allowed_term_ids( $tax_name );
	}

	/**
	 * @return string[] Array of managed taxonomy names
	 */
	public function get_managed_taxonomies () {
		return $this->coverage->get_managed_taxonomies();
	}

	/**
	 * Load the persisted groups data
	 *
	 * The function has the side effect of initializing the coverage object
	 */
	public function load () {
		$groups_data = get_option( self::OPTION_NAME );
		if ( false !== $groups_data ) {

			// ToDo: Error checking needed, detect bad data and report on it
			$this->groups = unserialize( $groups_data );
		}

		// Store all the coverage information that we may need to know about later
		$this->coverage = new TermCapsCoverage( $this->groups );
	}

	/**
	 * Save groups data
	 */
	public function save () {
		update_option( self::OPTION_NAME, serialize( $this->groups ) );
	}
}

/**
 * Class TermCapsCoverage
 */
class TermCapsCoverage {

	/**
	 * @var bool $covered Is the current user covered under any group rules?
	 */
	private $covered = false;

	/**
	 * @var array $managed_taxonomies Covered for the current user, consolidated across all groups
	 *        Format: 'taxonomy' => (termID, termID, termID), ...
	 */
	public $managed_taxonomies = array();

	/**
	 * @param TermCapsGroup[] $groups
	 */
	public function __construct ( $groups ) {

		foreach ( $groups as $this_group ) {

			// Is the current user covered under this group?
			if ( $this_group->is_user_covered() ) {
				$this->covered = true; // Flag the current user as being under coverage

				// Store all managed tax/term information for this group
				foreach ( $this_group->get_taxonomies() as $this_tax ) {

					$tax_name = $this_tax->taxonomy_name;
					$new_terms = $this_tax->get_allowed_term_ids();

					// Have we added this taxonomy before?
					if ( $this->is_taxonomy_managed( $tax_name ) ) {
						// Append any new unique terms to the taxonomy
						$this->managed_taxonomies[ $tax_name ] = array_unique( array_merge( $this->managed_taxonomies[ $tax_name ], $new_terms ) );
					}
					// A taxonomy we haven't seen before
					else {
						$this->managed_taxonomies[ $tax_name ] = $new_terms;
					}
				}
			}
		}
	}

	/**
	 * @return bool Whether or not the current user is covered under any group restriction rules
	 */
	public function is_current_user_covered () {
		return $this->covered;
	}

	/**
	 * @param string $tax_name
	 *
	 * @return bool Whether or not the taxonomy is managed for the user across one ore more groups
	 */
	public function is_taxonomy_managed ( $tax_name ) {
		return array_key_exists( $tax_name, $this->managed_taxonomies );
	}

	/**
	 * @return string[] Array of managed taxonomy names
	 */
	public function get_managed_taxonomies () {
		return array_keys( $this->managed_taxonomies );
	}

	/**
	 * @param string $tax_name
	 *
	 * @return int[]|null Array of allowed term IDs for the taxonomy, or null if the tax isn't managed
	 */
	public function get_allowed_term_ids ( $tax_name ) {

		if ( !$this->is_taxonomy_managed( $tax_name ) ) {
			return null;
		}

		return $this->managed_taxonomies[ $tax_name ];
	}
}

/**
 * Class TermCapsGroup
 */
class TermCapsGroup {

	/**
	 * @var string $title Descriptive name
	 */
	public $title;

	/**
	 * @var string $name Slugified identifier
	 */
	public $name;

	/**
	 * @var string[] $roles
	 */
	public $roles = array();

	/**
	 * @var string[] $capabilities
	 */
	public $capabilities = array();

	/**
	 * @var TermCapsTaxonomy[]
	 */
	private $taxonomies = array();

	/**
	 * @param string $title Descriptive tile
	 * @param string|null $name Slugified identifier (will use sanitized version of $title if omitted)
	 */
	public function __construct ( $title, $name = null ) {
		$this->title = $title;
		$this->name = ( !empty( $name ) ) ? sanitize_title( $name ) : sanitize_title( $title );
	}

	/**
	 * @param string $taxonomy_name
	 * @param int[] $term_ids
	 * @param bool $allow_all_terms
	 * @param bool $auto_enable_new_terms
	 */
	public function add_taxonomy ( $taxonomy_name, $term_ids = array(), $allow_all_terms = false, $auto_enable_new_terms = false ) {
		$this->taxonomies[] = new TermCapsTaxonomy( $taxonomy_name, $term_ids, $allow_all_terms, $auto_enable_new_terms );
	}

	/**
	 * @param $taxonomy_name
	 *
	 * @return null|TermCapsTaxonomy
	 */
	public function get_taxonomy( $taxonomy_name ) {

		foreach ( $this->taxonomies as $this_taxonomy ) {
			if ( $taxonomy_name == $this_taxonomy->taxonomy_name ) {
				return $this_taxonomy;
			}
		}

		// Didn't find the target name
		return null;
	}

	/**
	 * @return TermCapsTaxonomy[]
	 */
	public function get_taxonomies() {
		return $this->taxonomies;
	}

	/**
	 * @param string $taxonomy_name
	 */
	public function remove_taxonomy( $taxonomy_name ) {
		for ( $i = 0; $i < count( $this->taxonomies ); $i++ ) {
			if ( $this->taxonomies[ $i ]->taxonomy_name == $taxonomy_name ) {
				array_splice( $this->taxonomies, $i, 1 );
			}
		}
	}

	/**
	 * @param int|null $target_user_id Omit to check the current user
	 *
	 * @return bool
	 */
	public function is_user_covered ( $target_user_id = null ) {

		// Get the specified user, or the current user if omitted
		$target_user = ( null !== $target_user_id ) ? get_userdata( $target_user_id ) : wp_get_current_user();

		// Check if they're covered through any roles
		if ( !empty( $this->roles ) ) {
			foreach ( $this->roles as $this_role ) {
				if ( in_array( $this_role, (array) $target_user->roles ) ) {
					return true;
				}
			}
		}

		// Check if they're covered through any capabilities
		if ( !empty( $this->capabilities ) ) {
			foreach ( $this->capabilities as $this_capability ) {
				if ( array_key_exists( $this_capability, $target_user->allcaps ) ) {
					return true;
				}
			}
		}

		// If we make it here then they're not covered
		return false;
	}
}

/**
 * Class TermCapsTaxonomy
 */
class TermCapsTaxonomy {

	/**
	 * @var string $taxonomy_name
	 */
	public $taxonomy_name;

	/**
	 * @var bool $allow_all_terms
	 */
	public $allow_all_terms;

	/**
	 * @var bool $auto_enable_new_terms
	 */
	public $auto_enable_new_terms;

	/**
	 * @var int[] $term_ids
	 */
	public $term_ids = array();

	/**
	 * @param string $taxonomy_name
	 * @param int[] $term_ids
	 * @param bool $allow_all_terms
	 * @param bool $auto_enable_new_terms
	 */
	public function __construct ( $taxonomy_name, $term_ids = array(), $allow_all_terms = false, $auto_enable_new_terms = false ) {
		$this->taxonomy_name = $taxonomy_name;
		$this->allow_all_terms = $allow_all_terms;
		$this->auto_enable_new_terms = $auto_enable_new_terms;
		$this->term_ids = (array) $term_ids;
	}

	/**
	 * @return int[] Array of allowed term IDs for this taxonomy
	 */
	public function get_allowed_term_ids () {

		// Default to the term ID array
		$allowed_term_ids = $this->term_ids;

		if ( $this->allow_all_terms ) {
			$allowed_term_ids = array();
			$all_terms = get_terms( $this->taxonomy_name, array( 'hide_empty' => false ) );

			// ToDo: We should consider some logging if the tax name wasn't found
			if ( is_array( $all_terms ) ) {

				// Add each term ID from the taxonomy
				foreach ( $all_terms as $this_term ) {
					$allowed_term_ids[ ] = (int) $this_term->term_id;
				}
			}
		}

		return $allowed_term_ids;
	}
}