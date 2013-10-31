<?php
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
	public $term_ids;

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
		$this->terms = $term_ids;
	}
}