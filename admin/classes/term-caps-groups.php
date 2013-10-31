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
	 * @return mixed|TermCapsGroups|void
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