<?php
/*
Plugin Name: Responsive Scrolling Tables
Description: Responsive Scrolling Tables detects when tables are bigger then their containers and makes them scroll instead of flowing over the boundary of the container.
Version: 1.2.1
Author: T J Dixon Limited
Author URI: http://www.tjdixon.com/
Plugin URI: http://www.tjdixon.com/
License: GPLv3
*/

defined('WPINC') or die();

function addRSTJS() {
	wp_register_script('responsive-scrolling-tables', plugins_url('/js/responsivescrollingtables.js', __FILE__));
	wp_enqueue_script('responsive-scrolling-tables');
}

add_action('wp_enqueue_scripts', 'addRSTJS');


/** Add the hook to define the menu item (this is a sub menu item of settings) */
//add_action( 'admin_menu', 'rst_menu' );

/** The function that adds the options page to the admin area */
//function rst_menu() {
//	add_options_page( 'Responsive Scrolling Tables Options', 'Responsive Scrolling Tables', 'manage_options', 'responsive-scrolling-tables-settings', 'rst_options' );
//}

/** The output of the options page */
function rst_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// variables for the field and option names
    $opt_name = 'table_min_width';
    $hidden_field_name = 'rst_submit_hidden';
    $data_field_name = 'table_min_width';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );

        // Put an settings updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Settings saved.', 'responsive-scrolling-tables-settings' ); ?></strong></p></div>
<?php
    }
?>
<div class="wrap">

<h2><?php echo __( 'Responsive Scrolling Tables Options', 'responsive-scrolling-tables-settings' ); ?></h2>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Minimum width of tables:", 'responsive-scrolling-tables-settings' ); ?>
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">
</p><hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>
<?php
}
?>
