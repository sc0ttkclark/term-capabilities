<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Term_Capabilities
 * @author    Scott Kingsley Clark, Phil Lewis <lol@scottkclark.com>
 * @license   GPL-2.0+
 * @link      https://github.com/sc0ttkclark/term-capabilities
 * @copyright 2013 Scott Kingsley Clark, Phil Lewis
 */
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<h3>Roles</h3>
	<ul>
		<?php
			$roles = new WP_Roles;

			foreach ( $roles->role_objects as $role ) {
		?>
			<li><?php echo $role->name; ?></li>
		<?php
			}
		?>
	</ul>

	<h3>Capabilities</h3>

	<ul>
		<?php
			$data = array();

			global $wp_roles;

			$default_caps = array(
				'activate_plugins',
				'add_users',
				'create_users',
				'delete_others_pages',
				'delete_others_posts',
				'delete_pages',
				'delete_plugins',
				'delete_posts',
				'delete_private_pages',
				'delete_private_posts',
				'delete_published_pages',
				'delete_published_posts',
				'delete_users',
				'edit_dashboard',
				'edit_files',
				'edit_others_pages',
				'edit_others_posts',
				'edit_pages',
				'edit_plugins',
				'edit_posts',
				'edit_private_pages',
				'edit_private_posts',
				'edit_published_pages',
				'edit_published_posts',
				'edit_theme_options',
				'edit_themes',
				'edit_users',
				'import',
				'install_plugins',
				'install_themes',
				'list_users',
				'manage_categories',
				'manage_links',
				'manage_options',
				'moderate_comments',
				'promote_users',
				'publish_pages',
				'publish_posts',
				'read',
				'read_private_pages',
				'read_private_posts',
				'remove_users',
				'switch_themes',
				'unfiltered_html',
				'unfiltered_upload',
				'update_core',
				'update_plugins',
				'update_themes',
				'upload_files'
			);

			$role_caps = array();

			foreach ( $wp_roles->role_objects as $key => $role ) {
				if ( is_array( $role->capabilities ) ) {
					foreach ( $role->capabilities as $cap => $grant ) {
						$role_caps[ $cap ] = $cap;
					}
				}
			}

			$role_caps = array_unique( $role_caps );

			$capabilities = array_merge( $default_caps, $role_caps );

			// To support Members filters
			$capabilities = apply_filters( 'members_get_capabilities', $capabilities );

			$capabilities = apply_filters( 'pods_roles_get_capabilities', $capabilities );

			sort( $capabilities );

			$capabilities = array_unique( $capabilities );

			global $wp_roles;

			foreach ( $capabilities as $capability ) {
				$data[ $capability ] = $capability;
			}

			foreach ( $data as $capability ) {
		?>
			<li><?php echo $capability; ?></li>
		<?php
			}
		?>
	</ul>

	<h3>Taxonomies</h3>
	<ul>
		<?php
			$taxonomies = get_taxonomies( array(), 'objects' );

			foreach ( $taxonomies as $taxonomy ) {
		?>
			<li><?php echo $taxonomy->label; ?></li>
		<?php
			}
		?>
	</ul>

	<?php
		foreach ( $taxonomies as $taxonomy ) {
	?>
		<h3><?php echo $taxonomy->label; ?></h3>
		<ul>
			<?php
				$terms = get_terms( $taxonomy->name );

				foreach ( $terms as $term ) {
			?>
				<li><?php echo $term->name; ?></li>
			<?php
				}
			?>
		</ul>
	<?php
		}
	?>

</div>
