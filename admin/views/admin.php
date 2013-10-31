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

	<h3><?php _e( 'Groups', 'term-capabilities' ); ?></h3>

	<table class="wp-list-table widefat fixed" style="width:auto;min-width:400px;">
		<thead>
			<tr>
				<!--<th scope="col" id="cb" class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' ); ?></label>
					<input id="cb-select-all-1" type="checkbox">
				</th>-->
				<th scope="col" id="title" class="manage-column column-title">
					<?php _e( 'Group', 'term-capabilities' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$groups = new TermCapsGroups;

				foreach ( $groups->groups as $group ) {
			?>
				<tr>
					<!--<th scope="row" class="check-column">
						<input type="checkbox" name="groups[]" value="<?php echo esc_attr( $group->name ); ?>" />
					</th>-->
					<td>
						<a href="<?php echo add_query_arg( array( 'group' => $group->name ) ); ?>"><?php echo esc_html( $group->title ); ?></a>
					</td>
				</tr>
			<?php
				}
			?>
				<tr>
					<!--<th scope="row" class="check-column"></th>-->
					<td>
						<a href="<?php echo add_query_arg( array( 'action' => 'add' ) ); ?>" class="button button-primary alignright"><?php _e( 'Add New Group', 'term-capabilities' ); ?></a>
					</td>
				</tr>
		</tbody>
	</table>

<?php
/*

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
					$terms = get_terms( $taxonomy->name, array('hide_empty' => false) );

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
		}*/
	?>

</div>
