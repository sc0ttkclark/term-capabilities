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

	// ToDo: We need details on how we'll store roles and/or caps

	public function __construct( $name ) {
		$this-> name = $name;
	}
}