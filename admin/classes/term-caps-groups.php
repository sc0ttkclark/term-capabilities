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
	 * @var bool $covered Is the current user covered under any group rules?
	 */
	private $covered = false;

	/**
	 * @var int[] $allowed_terms Array of term IDs the current user can utilize, if covered
	 */
	private $allowed_terms = array();

	/**
	 * @return bool Whether or not the current user is covered under any group restriction rules
	 */
	public function is_covered () {
		return $this->covered;
	}

	/**
	 *
	 */
	public function load () {
		$groups = get_option( self::OPTION_NAME );
		if ( false !== $groups ) {
			$this->groups = unserialize( $groups );
		}

		// Store all the coverage information now that
		$this->init_coverage_info();
	}

	/**
	 *
	 */
	public function save () {
		update_option( self::OPTION_NAME, serialize( $this->groups ) );
	}

	/**
	 * Called internally after load()
	 */
	private function init_coverage_info () {

		foreach ( $this->groups as $this_group ) {

			// Is the current user covered under this group?
			if ( $this_group->is_user_covered() ) {
				$this->covered = true;  // Flag the current user as being under coverage

				// Collect all allowed term IDs for this group
				foreach ( $this_group->taxonomies as $this_tax_obj ) {

					// All terms allowed?
					if ( $this_tax_obj->allow_all_terms ) {
						$all_terms = get_terms( $this_tax_obj->taxonomy_name, array( 'hide_empty' => false ) );

						// ToDo: We should consider some logging if the tax name wasn't found
						if ( is_array( $all_terms ) ) {
							foreach ( $all_terms as $this_term ) {
								$this->allowed_terms[] = $this_term->term_id;
							}
						}
					}
					// Not all terms are allowed, use the term ID list
					else {
						$this->allowed_terms = array_merge( $this_tax_obj->term_ids, $this->allowed_terms );
					}
					$this->allowed_terms = array_unique( $this->allowed_terms );
				}
			}
		}
	}
}