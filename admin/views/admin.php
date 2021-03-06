<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Term_Capabilities
 * @author    Phil Lewis, Scott Kingsley Clark <lol@scottkclark.com>
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
			/**
			 * @var TermCaps $termcaps
			 */
			foreach ( $termcaps->groups as $group ) {
			?>
				<tr>
					<!--<th scope="row" class="check-column">
						<input type="checkbox" name="groups[]" value="<?php echo esc_attr( $group->name ); ?>" />
					</th>-->
					<td>
						<strong>
							<a href="<?php echo add_query_arg( array( 'action' => 'edit', 'group' => $group->name ) ); ?>">
								<?php echo esc_html( $group->title ); ?>
							</a>
						</strong>

						<div class="row-actions">
							<span class="edit"><a href="<?php echo add_query_arg( array( 'action' => 'edit', 'group' => $group->name ) ); ?>" title="<?php esc_attr_e( 'Edit this item' ); ?>"><?php _e( 'Edit' ); ?></a> | </span>
							<span class="delete"><a href="<?php echo add_query_arg( array( 'action' => 'delete', 'group' => $group->name ) ); ?>" title="<?php esc_attr_e( 'Delete this item' ); ?>"><?php _e( 'Delete' ); ?></a></span>
						</div>
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

</div>
