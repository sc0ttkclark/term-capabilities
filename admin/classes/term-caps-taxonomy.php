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
					$allowed_term_ids[ ] = $this_term->term_id;
				}
			}
		}

		return $allowed_term_ids;
	}
}