<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Term_Capabilities
 * @author    Phil Lewis, Scott Kingsley Clark <lol@scottkclark.com>
 * @license   GPL-2.0+
 * @link      https://github.com/sc0ttkclark/term-capabilities
 * @copyright 2013 Scott Kingsley Clark, Phil Lewis
 *
 * @wordpress-plugin
 * Plugin Name:       Term Capabilities
 * Plugin URI:        https://github.com/sc0ttkclark/term-capabilities
 * Description:       Limit access to certain terms in the post editor, per capability or role
 * Version:           1.0.0
 * Author:            Phil Lewis and Scott Kingsley Clark
 * Author URI:        https://github.com/sc0ttkclark/term-capabilities
 * Text Domain:       term-capabilities
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/sc0ttkclark/term-capabilities
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . '/public/class-term-capabilities.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, array( 'Term_Capabilities', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Term_Capabilities', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Term_Capabilities', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . '/admin/class-term-capabilities-admin.php' );
	add_action( 'plugins_loaded', array( 'Term_Capabilities_Admin', 'get_instance' ) );

}