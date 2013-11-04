<?php
require_once( 'classes/termcaps.php' ); // Other dependency classes will get loaded

/**
 * Plugin Name.
 *
 * @package   Term_Capabilities_Admin
 * @author    Phil Lewis, Scott Kingsley Clark <lol@scottkclark.com>
 * @license   GPL-2.0+
 * @link      https://github.com/sc0ttkclark/term-capabilities
 * @copyright 2013 Scott Kingsley Clark, Phil Lewis
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-term-capabilities.php`
 *
 * @package Term_Capabilities_Admin
 * @author  Phil Lewis, Scott Kingsley Clark <lol@scottkclark.com>
 */
class Term_Capabilities_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * @var TermCaps $termcaps
	 */
	private $termcaps = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct () {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = Term_Capabilities::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_init', array( $this, 'process_plugin_admin_post' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Hook into the metabox display machinery
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

		// Called whenever a term relationship is added to a post object
		add_action( 'added_term_relationship', array( $this, 'added_term_relationship' ), 10, 2 );

		// Setup the global TermCaps object
		add_action( 'wp_loaded', array( $this, 'init_termcaps_object' ) );
	}

	/**
	 *
	 */
	public function init_termcaps_object () {
		$this->termcaps = new TermCaps();
		$this->termcaps->load();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance () {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles () {

		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Term_Capabilities::VERSION );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts () {

		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Term_Capabilities::VERSION );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu () {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Term Capabilities', $this->plugin_slug ),
			__( 'Term Capabilities', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

		// ToDo: Testing only
		add_options_page(
			__( 'Termcap Debug', $this->plugin_slug ),
			__( 'Termcap Debug', $this->plugin_slug ),
			'manage_options',
			'xyzzy',
			array( $this, 'termcap_debug_menu' )
		);
	}

	// ToDo: Testing only
	public function termcap_debug_menu () {
		echo "<pre>" . print_r( $this->termcaps, true ) . "</pre>";
	}

	/**
	 *
	 */
	public function process_plugin_admin_post () {

		// Bail if needed
		if ( !isset( $_GET[ 'page' ] ) || $this->plugin_slug != $_GET[ 'page' ] || !isset( $_GET[ 'action' ] ) ) {
			return;
		}

		// Farm out to the action handlers
		if ( !empty( $_POST ) && 'add' == $_GET[ 'action' ] ) {
			$this->add_group();
		}
		elseif ( !empty( $_POST ) && 'edit' == $_GET[ 'action' ] && isset( $_GET[ 'group' ] ) && !empty( $_GET[ 'group' ] ) ) {
			$this->edit_group();
		}
		elseif ( 'delete' == $_GET[ 'action' ] && isset( $_GET[ 'group' ] ) && !empty( $_GET[ 'group' ] ) ) {
			$this->delete_group();
		}
	}

	/**
	 *
	 */
	public function add_group () {
		$postdata = wp_unslash( $_POST );

		$new_group = $this->termcaps->add_group( $postdata[ 'term_caps_title' ], $postdata[ 'term_caps_name' ] );

		// Collect and store all the $_POST data and save
		$this->save_group( $new_group, $postdata );

		wp_redirect( add_query_arg( array(
			'action' => 'edit',
			'group' => $new_group->name,
			'message' => 'added'
		) ) );
		die();
	}

	/**
	 *
	 */
	public function edit_group () {
		$postdata = wp_unslash( $_POST );

		$group = $this->termcaps->get_group( $_GET[ 'group' ] );
		if ( !empty( $group ) ) {
			$group->title = $postdata[ 'term_caps_title' ];

			if ( !empty( $postdata[ 'term_caps_name' ] ) ) {
				$group->name = sanitize_title( $postdata[ 'term_caps_name' ] );
			}
			else {
				$group->name = sanitize_title( $group->title );
			}
		}
		else {
			$group = $this->termcaps->add_group( $postdata[ 'term_caps_title' ], $postdata[ 'term_caps_name' ] );
		}

		// Collect and store all the $_POST data and save
		$this->save_group( $group, $postdata );

		wp_redirect( add_query_arg( array(
			'action' => 'edit',
			'group' => $group->name,
			'message' => 'saved'
		) ) );
		die();
	}

	/**
	 * @param TermCapsGroup $group
	 * @param array $postdata
	 */
	public function save_group ( TermCapsGroup $group, $postdata ) {

		if ( isset( $postdata[ 'term_caps_roles' ] ) ) {
			$group->roles = (array) $postdata[ 'term_caps_roles' ];
		}

		if ( isset( $postdata[ 'term_caps_capabilities' ] ) ) {
			$group->capabilities = (array) $postdata[ 'term_caps_capabilities' ];
		}

		$taxonomies = get_taxonomies( array(), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, TermCaps::$IGNORED_TAXONOMIES ) ) {
				continue;
			}

			$all_terms = false;
			$terms = array();

			if ( isset( $postdata[ 'term_caps_all_' . $taxonomy->name ] ) && 1 == $postdata[ 'term_caps_all_' . $taxonomy->name ] ) {
				$all_terms = true;
			}
			elseif ( isset( $postdata[ 'tax_input' ] ) && is_array( $postdata[ 'tax_input' ] ) && isset( $postdata[ 'tax_input' ][ $taxonomy->name ] ) && !empty( $postdata[ 'tax_input' ][ $taxonomy->name ] ) ) {
				$terms = (array) explode( ',', $postdata[ 'tax_input' ][ $taxonomy->name ] );

				foreach ( $terms as $k => $term_name ) {
					$term = get_term_by( 'name', $term_name, $taxonomy->name );

					if ( !empty( $term ) ) {
						$terms[ $k ] = $term->term_id;
					}
					else {
						unset( $terms[ $k ] );
					}
				}

				$terms = array_values( $terms );
			}

			if ( !empty( $terms ) || $all_terms ) {
				$group->remove_taxonomy( $taxonomy->name );
				$group->add_taxonomy( $taxonomy->name, $terms, $all_terms );
			}
			elseif ( null != $group->get_taxonomy( $taxonomy->name ) ) {
				$group->remove_taxonomy( $taxonomy->name );
			}
		}

		$this->termcaps->save();
	}

	/**
	 *
	 */
	public function delete_group () {

		if ( null !== $this->termcaps->get_group( $_GET[ 'group' ] ) ) {
			$this->termcaps->remove_group( $_GET[ 'group' ] );
		}

		$this->termcaps->save();

		wp_redirect( add_query_arg( array( 'action' => false, 'group' => false, 'message' => 'deleted' ) ) );
		die();
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page () {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$termcaps = $this->termcaps; // Used by the views

		if ( isset( $_GET[ 'action' ] ) && 'add' == $_GET[ 'action' ] ) {
			include_once( 'views/admin-add.php' );
		}
		elseif ( isset( $_GET[ 'action' ] ) && 'edit' == $_GET[ 'action' ] && isset( $_GET[ 'group' ] ) && !empty( $_GET[ 'group' ] ) ) {
			include_once( 'views/admin-edit.php' );
		}
		else {
			include_once( 'views/admin.php' );
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links ( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);
	}

	/**
	 * @param string $post_type The WordPress post type
	 * @param WP_Post $post Post object being edited
	 */
	public function add_meta_boxes ( $post_type, $post ) {
		$termcaps = $this->termcaps;

		// Nothing to do if the current user isn't covered
		if ( !$termcaps->is_current_user_covered() ) {
			return;
		}

		foreach ( get_object_taxonomies( $post ) as $tax_name ) {
			$taxonomy = get_taxonomy( $tax_name );

			// Skip  hidden and some special taxonomies
			if ( !$taxonomy->show_ui || in_array( $tax_name, TermCaps::$IGNORED_TAXONOMIES ) ) {
				continue;
			}

			// Skip this taxonomy if it isn't managed under user coverage
			if ( !$termcaps->is_taxonomy_managed( $tax_name ) ) {
				continue;
			}

			if ( !is_taxonomy_hierarchical( $tax_name ) ) {
				$tax_meta_box_id = 'tagsdiv-' . $tax_name;
			}
			else {
				$tax_meta_box_id = $tax_name . 'div';
			}
			$new_tax_meta_box_id = 'term-caps-' . $tax_meta_box_id;

			// Replace the stock metabox with our own
			remove_meta_box( $tax_meta_box_id, $post_type, 'side' );
			add_meta_box(
				$new_tax_meta_box_id,
				$taxonomy->labels->name,
				array( $this, 'term_caps_render_meta_box' ),
				null,
				'side',
				'core',
				array(
					'tax_name' => $tax_name
				)
			);
		}
	}

	/**
	 * @param WP_Post $post
	 * @param mixed[] $metabox
	 */
	public function term_caps_render_meta_box ( $post, $metabox ) {

		$tax_name = $metabox[ 'args' ][ 'tax_name' ];
		$tax = get_taxonomy( $tax_name );
		?>
		<div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv">
			<ul id="<?php echo $tax_name; ?>-tabs" class="category-tabs">
				<li class="tabs"><a href="#<?php echo $tax_name; ?>-all"><?php echo $tax->labels->all_items; ?></a></li>
				<li class="hide-if-no-js"><a href="#<?php echo $tax_name; ?>-pop"><?php _e( 'Most Used' ); ?></a></li>
			</ul>

			<div id="<?php echo $tax_name; ?>-pop" class="tabs-panel" style="display: none;">
				<ul id="<?php echo $tax_name; ?>checklist-pop" class="categorychecklist form-no-clear">
					<?php $popular_ids = $this->term_caps_popular_terms_checklist( $tax_name ); ?>
				</ul>
			</div>

			<div id="<?php echo $tax_name; ?>-all" class="tabs-panel">
				<?php
				$name = ( $tax_name == 'category' ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
				echo "<input type='hidden' name='{$name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
				?>
				<ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name ?>" class="categorychecklist form-no-clear">
					<?php
					$this->term_caps_metabox_checklist( $post->ID, array(
						'taxonomy' => $tax_name,
						'popular_cats' => $popular_ids
					) );
					?>
				</ul>
			</div>
		</div>
	<?php
	}

	/**
	 * @param string $tax_name Taxonomy to retrieve terms from.
	 * @param int $default Unused.
	 * @param int $number Number of terms to retrieve. Defaults to 10.
	 * @param bool $echo Optionally output the list as well. Defaults to true.
	 *
	 * @return array List of popular term IDs.
	 */
	function term_caps_popular_terms_checklist ( $tax_name, $default = 0, $number = 10, $echo = true ) {
		/** @noinspection PhpExpressionResultUnusedInspection */
		$default; // Harmless use of unused param to suppress unused param notice in the IDE

		$post = get_post();

		if ( $post && $post->ID ) {
			$checked_terms = wp_get_object_terms( $post->ID, $tax_name, array( 'fields' => 'ids' ) );
		}
		else {
			$checked_terms = array();
		}

		$terms = get_terms( $tax_name, array(
			'orderby' => 'count',
			'include' => $this->termcaps->get_allowed_term_ids( $tax_name ),
			'order' => 'DESC',
			'number' => $number,
			'hierarchical' => false
		) );

		$tax = get_taxonomy( $tax_name );

		$popular_ids = array();
		foreach ( (array) $terms as $term ) {
			$popular_ids[ ] = $term->term_id;
			if ( !$echo ) { // hack for AJAX use
				continue;
			}
			$id = "popular-$tax_name-$term->term_id";
			$checked = in_array( $term->term_id, $checked_terms ) ? 'checked="checked"' : '';
			?>

			<li id="<?php echo $id; ?>" class="popular-category">
				<label class="selectit">
					<input id="in-<?php echo $id; ?>" type="checkbox" <?php echo $checked; ?> value="<?php echo (int) $term->term_id; ?>" <?php disabled( !current_user_can( $tax->cap->assign_terms ) ); ?> />
					<?php echo esc_html( apply_filters( 'the_category', $term->name ) ); ?>
				</label>
			</li>

		<?php
		}
		return $popular_ids;
	}

	/**
	 * @param int $post_id
	 * @param array $args
	 */
	public function term_caps_metabox_checklist ( $post_id = 0, $args = array() ) {
		/**
		 * Suppress undefined variable notices in the IDE caused by using extract()
		 *
		 * @var int $descendants_and_self (treated as a boolean)
		 * @var bool|int[] $selected_cats
		 * @var bool|int[] $popular_cats Array of popular category IDs
		 * @var null|Walker $walker
		 * @var string $taxonomy The taxonomy name
		 * @var bool $checked_ontop
		 */
		$defaults = array(
			'descendants_and_self' => 0,
			'selected_cats' => false,
			'popular_cats' => false,
			'walker' => null,
			'taxonomy' => 'category',
			'checked_ontop' => true
		);
		$args = apply_filters( 'wp_terms_checklist_args', $args, $post_id );

		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		if ( empty( $walker ) || !is_a( $walker, 'Walker' ) ) {
			$walker = new Walker_Category_Checklist;
		}

		$descendants_and_self = (int) $descendants_and_self;

		$args = array( 'taxonomy' => $taxonomy );

		$tax = get_taxonomy( $taxonomy );
		$args[ 'disabled' ] = !current_user_can( $tax->cap->assign_terms );

		if ( is_array( $selected_cats ) ) {
			$args[ 'selected_cats' ] = $selected_cats;
		}
		elseif ( $post_id ) {
			$args[ 'selected_cats' ] = wp_get_object_terms( $post_id, $taxonomy, array_merge( $args, array( 'fields' => 'ids' ) ) );
		}
		else {
			$args[ 'selected_cats' ] = array();
		}

		$args[ 'popular_cats' ] = $popular_cats;

		// ToDo: look into this one
		if ( $descendants_and_self ) {
			$categories = (array) get_terms( $taxonomy, array(
				'child_of' => $descendants_and_self,
				'hierarchical' => 0,
				'hide_empty' => 0
			) );
			$self = get_term( $descendants_and_self, $taxonomy );
			array_unshift( $categories, $self );
		}
		else {
			$categories = (array) get_terms( $taxonomy, array( 'include' => $this->termcaps->get_allowed_term_ids( $taxonomy ) ) );
		}

		if ( $checked_ontop ) {
			// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
			$checked_categories = array();
			$keys = array_keys( $categories );

			foreach ( $keys as $k ) {
				if ( in_array( $categories[ $k ]->term_id, $args[ 'selected_cats' ] ) ) {
					$checked_categories[ ] = $categories[ $k ];
					unset( $categories[ $k ] );
				}
			}

			// Put checked cats on top
			echo call_user_func_array( array( &$walker, 'walk' ), array( $checked_categories, 0, $args ) );
		}
		// Then the rest of them
		echo call_user_func_array( array( &$walker, 'walk' ), array( $categories, 0, $args ) );
	}

	/**
	 * @param int $object_id Post object ID
	 * @param int $tt_id term_taxonomy_id used in the newly added term relationship
	 */
	public function added_term_relationship ( $object_id, $tt_id ) {
		global $wpdb;
		$termcaps = $this->termcaps;

		// Nothing to do if the current user isn't under any coverage
		if ( !$termcaps->is_current_user_covered() ) {
			return;
		}

		// Lookup the term ID and taxonomy name used in the newly added term relationship
		list ( $new_term_id, $tax_name ) = $wpdb->get_row( "SELECT tt.term_id, tt.taxonomy FROM $wpdb->term_taxonomy AS tt WHERE tt.term_taxonomy_id = $tt_id", ARRAY_N );

		// Remove the newly added term relationship if the term ID isn't allowed
		if ( !in_array( $new_term_id, $termcaps->get_allowed_term_ids( $tax_name ) ) ) {
			wp_remove_object_terms( $object_id, (int) $new_term_id, $tax_name );
		}
	}
}