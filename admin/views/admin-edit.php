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
/**
 * @var TermCaps $termcaps
 */
$group = $termcaps->get_group( $_GET[ 'group' ] );
if ( empty( $group ) ) {
	include_once 'admin-add.php';
	return;
}
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<h3><?php _e( 'Edit Group', 'term-capabilities' ); ?></h3>

	<?php include_once 'admin-form.php'; ?>

</div>
