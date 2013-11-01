<?php
require_once 'term-caps-taxonomy.php';

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
	 * @var TermCapsTaxonomy[]
	 */
	public $taxonomies = array();

	/**
	 * @var string[] $roles
	 */
	public $roles = array();

	/**
	 * @var string[] $capabilities
	 */
	public $capabilities = array();

	/**
	 * @param string $title Descriptive tile
	 * @param string|null $name Slugified identifier (will use sanitized version of $title if omitted)
	 */
	public function __construct ( $title, $name = null ) {
		$this->title = $title;
		$this->name = ( !empty( $name ) ) ? sanitize_title( $name ) : sanitize_title( $title );
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