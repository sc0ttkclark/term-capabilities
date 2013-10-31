<?php
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
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

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
		require_once( 'classes/term-caps-groups.php' ); // Other dependency classes will get loaded

		$groups = new TermCapsGroups();

		// Test creation and save
		$tax_obj1 = new TermCapsTaxonomy( 'category', array( 3, 5 ) );
		$tax_obj2 = new TermCapsTaxonomy( 'my_taxonomy' );
		$tax_obj2->allow_all_terms = true;

		$new_group = new TermCapsGroup( 'My Group' );
		$new_group->taxonomies[ ] = $tax_obj1;
		$new_group->taxonomies[ ] = $tax_obj2;
		$new_group->roles = array( 'administrator', 'subscriber' );
		$new_group->capabilities = array( 'not_an_existing_cap' );
		$groups->groups[ ] = $new_group;

		$groups->save();

		// Test load
		$groups->load();

		echo "<pre>" . print_r( $groups, true ) . "</pre>";
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page () {
		require_once( 'classes/term-caps-groups.php' ); // Other dependency classes will get loaded

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

}
