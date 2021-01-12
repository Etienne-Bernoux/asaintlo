<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              sonaar.io
 * @since             1.0.0
 * @package           Sonaar_Music
 *
 * @wordpress-plugin
 * Plugin Name:       MP3 Music Player by Sonaar
 * Plugin URI:        https://goo.gl/mVUJEJ
 * Description:       This MP3 Audio Player will boosts your music website with its unique soundwave design. Manage your tracks, playlists and albums easily within WordPress.
 * Version:           2.2.1
 * Author:            Sonaar Music
 * Author URI:        https://goo.gl/tTcJ1Y
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sonaar-music
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('SRMP3_VERSION', '2.2.1'); // important to avoid cache issues on update

if ( get_option( 'template') != 'sonaar' && !class_exists( 'Sonaar_Music' )) {

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-sonaar-music.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */


	function add_action_links ( $links ) {
		$mylinks = array('<a href="' . admin_url( 'edit.php?post_type=album&page=iron_music_player' ) . '">Settings</a>');
		if ( !function_exists( 'run_sonaar_music_pro' ) ){
			array_push($mylinks, '<span><a href="https://sonaar.io/free-mp3-music-player-plugin-for-wordpress/?utm_source=Sonaar+Music+Free+Plugin&utm_medium=plugin" style="color:#39b54a;font-weight:700;">Go Pro</a></span>');
		}
		return array_merge( $links, $mylinks );
	}
	function srmp3_get_cpt_template( $single_template ) {
		global $post;

		if ( 'album' === $post->post_type ) {
			$single_template = dirname( __FILE__ ) . '/templates/single-album.php';
		}

		return $single_template;
	}
	function srmp3_register_elementor_locations( $elementor_theme_manager ) {
		//$elementor_theme_manager->register_all_core_location();
		$elementor_theme_manager->register_location( 'playlist' );
		$elementor_theme_manager->register_location( 'archive' );

	}
	add_action( 'elementor/theme/register_locations', 'srmp3_register_elementor_locations' );
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );
	add_filter( 'single_template', 'srmp3_get_cpt_template' );

	function run_sonaar_music() {
		$plugin = new Sonaar_Music();
		$plugin->run();
	}

	run_sonaar_music();

}