<?php
require_once( 'classes/term-caps.php' ); // Other dependency classes will get loaded

/**
 * Plugin Name.
 *
 * @package   Term_Capabilities_Admin
 * @author    Scott Kingsley Clark, Phil Lewis <lol@scottkclark.com>
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
 * @author  Scott Kingsley Clark, Phil Lewis <lol@scottkclark.com>
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

		// Hook into the post save mechanism to remove disallowed terms
		add_action( 'save_post', array( $this, 'save_post' ) );

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
			__( 'PG Test', $this->plugin_slug ),
			__( 'PG Test', $this->plugin_slug ),
			'manage_options',
			'xyzzy',
			array( $this, 'pg_test' )
		);
	}

	// ToDo: Testing only
	public function pg_test () {
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
				$group->taxonomies[ $taxonomy->name ] = new TermCapsTaxonomy( $taxonomy->name, $terms, $all_terms );
			}
			elseif ( isset( $group->taxonomies[ $taxonomy->name ] ) ) {
				unset( $group->taxonomies[ $taxonomy->name ] );
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
			// ToDo: leaving the original metabox in now for debugging
			//remove_meta_box( $tax_meta_box_id, $post_type, 'side' );
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

		// ToDo: FixMe from WordPress core example
		$tax_name = $metabox[ 'args' ][ 'tax_name' ];
		$term_ids = $this->termcaps->get_allowed_term_ids( $tax_name );

		$input_name = ( 'category' == $tax_name ) ? 'post_category[]' : "tax_input[$tax_name][]";
		?>
		<div id="term-caps-taxonomy-category" class="categorydiv">
			<input type="hidden" name="<?php echo $input_name; ?>" value="0" />
			<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">

				<?php foreach ( $term_ids as $this_term_id ) { ?>
					<?php $term_name = get_term_by( 'id', $this_term_id, $tax_name )->name; ?>
					<li id="category-<?php echo $this_term_id; ?>">
						<label class="selectit">
							<input value="<?php echo $this_term_id; ?>" type="checkbox" name="<?php echo $input_name; ?>" id="in-category-<?php echo $this_term_id; ?>"> <?php echo $term_name; ?>
						</label>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php
	}

	/**
	 *
	 * @param int $post_id The ID of the post.
	 */
	public function save_post ( $post_id ) {
		$termcaps = $this->termcaps;

		// Nothing to do if the current user isn't under any coverage
		if ( !$termcaps->is_current_user_covered() ) {
			return;
		}

		// Loop through all the managed taxonomies
		foreach ( $termcaps->get_managed_taxonomies() as $tax_name ) {

			// Get the terms saved on this post for this taxonomy
			$saved_terms = wp_get_object_terms( $post_id, $tax_name, array( 'fields' => 'ids' ) );
			$saved_terms = array_map( 'absint', $saved_terms ); // Cast all the term IDs to int for array_intersect()

			// Remove any disallowed terms from the list and save
			$terms_to_save = array_intersect( $saved_terms, $termcaps->get_allowed_term_ids( $tax_name ) );
			wp_set_object_terms( $post_id, $terms_to_save, $tax_name );
		}
	}
}
