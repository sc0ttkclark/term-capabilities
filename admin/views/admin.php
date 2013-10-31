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

	<table class="wp-list-table widefat fixed" style="width:auto;">
		<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
					<input id="cb-select-all-1" type="checkbox">
				</th>
				<th scope="col" id="title" class="manage-column column-title">
					Role
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$roles = new WP_Roles;

				foreach ( $roles->role_objects as $role ) {
			?>
				<tr>
					<th scope="row" class="check-column">
						<input type="checkbox" name="roles[]" value="<?php echo esc_attr( $role->name ); ?>" />
					</th>
					<td>
						<a href="<?php echo add_query_arg( array( 'role' => $role->name ) ); ?>"><?php echo esc_html( $role->name ); ?></a>
					</td>
				</tr>
			<?php
				}
			?>
		</tbody>
	</table>

	<h3>Capabilities</h3>

	<table class="wp-list-table widefat fixed" style="width:auto;">
		<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
					<input id="cb-select-all-1" type="checkbox">
				</th>
				<th scope="col" id="title" class="manage-column column-title">
					Capability
				</th>
			</tr>
		</thead>
		<tbody>
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
				<tr>
					<th scope="row" class="check-column">
						<input type="checkbox" name="capabilities[]" value="<?php echo esc_attr( $capability ); ?>" />
					</th>
					<td>
						<a href="<?php echo add_query_arg( array( 'capability' => $capability ) ); ?>"><?php echo esc_html( $capability ); ?></a>
					</td>
				</tr>
			<?php
				}
			?>
		</tbody>
	</table>

	<h3>Taxonomies</h3>

	<table class="wp-list-table widefat fixed" style="width:auto;">
		<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
					<input id="cb-select-all-1" type="checkbox">
				</th>
				<th scope="col" id="title" class="manage-column column-title">
					Taxonomy
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$taxonomies = get_taxonomies( array(), 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
			?>
				<tr>
					<th scope="row" class="check-column">
						<input type="checkbox" name="taxonomies[]" value="<?php echo esc_attr( $taxonomy->name ); ?>" />
					</th>
					<td>
						<a href="<?php echo add_query_arg( array( 'taxonomy' => $taxonomy->name ) ); ?>"><?php echo esc_html( $taxonomy->label ); ?></a>
					</td>
				</tr>
			<?php
				}
			?>
		</tbody>
	</table>

	<?php
		foreach ( $taxonomies as $taxonomy ) {
	?>
		<h3><?php echo $taxonomy->label; ?></h3>

		<table class="wp-list-table widefat fixed" style="width:auto;">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
						<input id="cb-select-all-1" type="checkbox">
					</th>
					<th scope="col" id="title" class="manage-column column-title">
						<?php echo $taxonomy->labels->singular_name; ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$terms = get_terms( $taxonomy->name );

					foreach ( $terms as $term ) {
				?>
					<tr>
						<th scope="row" class="check-column">
							<input type="checkbox" name="<?php echo esc_attr( $taxonomy->name ); ?>[]" value="<?php echo esc_attr( $term->term_id ); ?>" />
						</th>
						<td>
							<a href="<?php echo add_query_arg( array( 'term' => $term->term_id ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</td>
					</tr>
				<?php
					}
				?>
			</tbody>
		</table>
	<?php
		}
	?>

</div>
