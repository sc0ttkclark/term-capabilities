<?php
require_once 'term-caps-taxonomy.php';

/**
 * Class TermCapsGroup
 */
class TermCapsGroup {

	/**
	 * @var string $name
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
	 * @param $name
	 */
	public function __construct ( $name ) {
		$this->name = $name;
	}
}