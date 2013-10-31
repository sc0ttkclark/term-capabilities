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
	 * @var string $name Slugified title
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
	 * @param $title
	 */
	public function __construct ( $title ) {
		$this->title = $title;
		$this->name = sanitize_title( $title );
	}

	/**
	 * @param int|null $user_id Omit to check the current user
	 */
	public function is_user_covered ( $user_id = null ) {

		if ( null !== $user_id ) {
			$user = get_userdata( $user_id );
		}
		else {
			$user = wp_get_current_user();
		}

		if ( !empty( $this->roles ) ) {

		}

		// ToDo: real implementation plz

		// If we make it here then they're not covered
		return false;
	}
}