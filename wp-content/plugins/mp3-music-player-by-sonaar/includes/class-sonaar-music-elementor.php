<?php
/**
 * Main Elementor Sonaar Class
 *
 * The init class that runs the Elementor for Sonaar plugin.
 * Intended To make sure that the plugin's minimum requirements are met.
 *
 * You should only modify the constants to match your plugin's needs.
 *
 * Any custom code should go inside Plugin Class in the plugin.php file.
 * @since 1.2.0
 */
final class Elementor_Sonaar_Plugin {

	public function __construct() {

		// Load translation
		//add_action( 'init', array( $this, 'i18n' ) );

		// Init Plugin
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}
	/**
	 * Initialize the plugin
	 *
	 * Validates that Elementor is already loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed include the plugin class.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	
	public function init() {
		// Once we get here, We have passed all validation checks so we can safely include our plugin
			require_once( 'plugin.php' );
	}

}
// if elementor exist
if ( did_action( 'elementor/loaded' ) ) {
	new Elementor_Sonaar_Plugin();
}