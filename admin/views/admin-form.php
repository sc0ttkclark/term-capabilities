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

$fields = array(
	'title' => array(
		'label' => __( 'Group Title', 'term-capabilities' )
	),
	'name' => array(
		'label' => __( 'Group Name', 'term-capabilities' ),
		'description' => __( 'This will be automatically generated from the Group Title, or you can customize it. It will be sanitized during save like a permalink.', 'term-capabilities' )
	)
);

include_once ABSPATH . 'wp-admin/includes/meta-boxes.php';

wp_enqueue_script( 'post', false, array(), false, true );
?>

<form action="" method="post">
	<table class="form-table">
		<tbody>
			<?php
				foreach ( $fields as $field => $field_data ) {
			?>
				<tr valign="top">
					<th>
						<label for="term_caps_<?php echo esc_attr( $field ); ?>">
							<?php echo esc_html( $field_data[ 'label' ] ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="term_caps_<?php echo esc_attr( $field ); ?>" id="term_caps_<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $group->{$field} ); ?>" placeholder="<?php echo esc_attr( $field_data[ 'label' ] ); ?>" class="regular-text" />

						<?php
							if ( isset( $field_data[ 'description' ] ) && !empty( $field_data[ 'description' ] ) ) {
						?>
							<p><?php echo $field_data[ 'description' ]; ?></p>
						<?php
							}
						?>
					</td>
				</tr>
			<?php
				}
			?>

			<tr valign="top">
				<th>
					<label>
						<?php _e( 'Roles', 'term-capabilities' ); ?>
					</label>
				</th>
				<td>

					<table class="wp-list-table widefat fixed" style="width:auto;">
						<thead>
							<tr>
								<th scope="col" id="cb" class="manage-column column-cb check-column">
									<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' ); ?></label>
									<input id="cb-select-all-1" type="checkbox" name="term_caps_all_roles" value="1" />
								</th>
								<th scope="col" id="title" class="manage-column column-title" style="padding:0;vertical-align:middle;">
									<label for="cb-select-all-1">
										<?php _e( 'All Roles', 'term-capabilities' ); ?>
									</label>
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
										<input type="checkbox" name="term_caps_roles[]" value="<?php echo esc_attr( $role->name ); ?>" id="roles_<?php echo esc_attr( $role->name ); ?>"<?php checked( in_array( $role->name, $group->roles ) ); ?> />
									</th>
									<td style="padding:0;">
										<label for="roles_<?php echo esc_attr( $role->name ); ?>">
											<?php echo esc_html( $role->name ); ?>
										</label>
									</td>
								</tr>
							<?php
								}
							?>
						</tbody>
					</table>
				</td>
			</tr>

			<tr valign="top">
				<th>
					<label>
						<?php _e( 'Capabilities', 'term-capabilities' ); ?>
					</label>
				</th>
				<td>
					<table class="wp-list-table widefat fixed" style="width:auto;">
						<thead>
							<tr>
								<th scope="col" id="cb" class="manage-column column-cb check-column">
									<label class="screen-reader-text" for="cb-select-all-2"><?php _e( 'Select All' ); ?></label>
									<input id="cb-select-all-2" type="checkbox" name="term_caps_all_capabilities" value="1" />
								</th>
								<th scope="col" id="title" class="manage-column column-title" style="padding:0;vertical-align:middle;">
									<label for="cb-select-all-2">
										<?php _e( 'All Capabilities', 'term-capabilities' ); ?>
									</label>
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
										<input type="checkbox" name="term_caps_capabilities[]" value="<?php echo esc_attr( $capability ); ?>" id="capabilities_<?php echo esc_attr( $capability ); ?>"<?php checked( in_array( $capability, $group->capabilities ) ); ?> />
									</th>
									<td style="padding:0;">
										<label for="capabilities_<?php echo esc_attr( $capability ); ?>">
											<?php echo esc_html( $capability ); ?>
										</label>
									</td>
								</tr>
							<?php
								}
							?>
						</tbody>
					</table>
				</td>
			</tr>

			<?php
				$taxonomies = get_taxonomies( array(), 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
					if ( in_array( $taxonomy->name, array( 'post_format', 'nav_menu', 'link_category' ) ) ) {
						continue;
					}

					$allow_all_terms = false;
					$terms = array();

					$managed_taxonomy = $group->get_taxonomy( $taxonomy->name );
					if ( null != $managed_taxonomy ) {
						$allow_all_terms = $managed_taxonomy->allow_all_terms;
						$terms = $managed_taxonomy->term_ids;

						foreach ( $terms as $k => $term ) {
							$terms[ $k ] = get_term( $term, $taxonomy->name );

							if ( !empty( $terms[ $k ] ) ) {
								$terms[ $k ] = $terms[ $k ]->name;
							}
							else {
								unset( $terms[ $k ] );
							}
						}

						$terms = array_values( $terms );
					}
			?>
				<tr valign="top">
					<th>
						<label>
							<?php echo $taxonomy->label; ?>
						</label>
					</th>
					<td id="side-sortables">
						<label for="all_<?php echo $taxonomy->name; ?>">
							<input type="checkbox" name="term_caps_all_<?php echo $taxonomy->name; ?>" id="all_<?php echo $taxonomy->name; ?>" class="allow-all" value="1"<?php checked( $allow_all_terms ); ?> />
							<?php echo sprintf( __( 'Allow All %s', 'term-capabilities' ), $taxonomy->label ); ?>
						</label>

						<h4><?php echo sprintf( __( 'Allow Specific %s', 'term-capabilities' ), $taxonomy->label ); ?></h4>

						<div class="postbox" id="tagsdiv-<?php echo $taxonomy->name; ?>" style="background:none;border:none;">
							<?php
								ob_start();
								post_tags_meta_box( (object) array( 'ID' => 0 ), array( 'args' => array( 'taxonomy' => $taxonomy->name ) ) );
								$metabox = ob_get_clean();

								// Insert our terms
								$metabox = str_replace( '</textarea>', esc_textarea( implode( ',', $terms ) ) . '</textarea>', $metabox );

								echo $metabox;
							?>
						</div>
					</td>
				</tr>
			<?php
				}
			?>
		</tbody>
	</table>

	<?php submit_button(); ?>
</form>

<script>
	jQuery( function() {
		function term_caps_allow_all( $el ) {
			var $td = $el.closest( 'td' );

			if ( $el.prop( 'checked' ) ) {
				$td.find( 'h4, div.postbox' ).hide();
			}
			else {
				$td.find( 'h4, div.postbox' ).show();
			}
		}

		jQuery( 'input.allow-all' ).on( 'change', function() {
			term_caps_allow_all( jQuery( this ) );
		} ).each( function() {
			term_caps_allow_all( jQuery( this ) );
		} );
	} );
</script>