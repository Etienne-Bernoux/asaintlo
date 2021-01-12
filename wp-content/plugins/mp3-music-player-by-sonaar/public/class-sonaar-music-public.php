<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       sonaar.io
 * @since      1.0.0
 *
 * @package    Sonaar_Music
 * @subpackage Sonaar_Music/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Sonaar_Music
 * @subpackage Sonaar_Music/public
 * @author     Edouard Duplessis <eduplessis@gmail.com>
 */
class Sonaar_Music_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $post;

		function addFontStyle($formatedFontfamily, $selector) {
			$fontItalic = false;
			$formatedFontfamily = str_replace('_safe_', '',$formatedFontfamily);
			$formatedFontfamily = str_replace('+', ' ',$formatedFontfamily);
			if( strstr( $formatedFontfamily, ':' ) ){
				
				$fontWeightAndStyle = substr($formatedFontfamily, strpos($formatedFontfamily, ":") + 1); 
				
				if( strstr( $fontWeightAndStyle, 'italic' ) ){
					$fontItalic = true;
					$fontWeight = strstr( $fontWeightAndStyle, 'italic', true); 
				}else{
					$fontWeight = $fontWeightAndStyle;
				}
				if($fontWeight == 'regular'){
					$fontWeight = '400';
				}

				$formatedFont= array(
					'family' => strstr( $formatedFontfamily, ':', true), 
					'weight' => $fontWeight,
					'italic' => $fontItalic
				);
				
				$result = $selector . '{ font-family:' . $formatedFont['family'] . '; font-weight:' . $formatedFont['weight'].';';
				$result .=  ( $formatedFont['italic'] )?'font-style:italic;':'';
				$result .=  '}';
				
			}else{
				$result = $selector . '{ font-family:' . $formatedFontfamily . '}';
			}	
			return  $result;
		}
		
		wp_register_style( 'sonaar-music', plugin_dir_url( __FILE__ ) . 'css/sonaar-music-public.css', array(), $this->version, 'all' );
		$data = "";
		
		/* Enqueue Sonaar Music css file on single Album Page */
		if ( is_single() && get_post_type() == 'album' ) {
			wp_enqueue_style( 'sonaar-music' );
			wp_enqueue_style( 'sonaar-music-pro' );
		}
		$font = Sonaar_Music::get_option('music_player_playlist');
		$fontTitle = Sonaar_Music::get_option('music_player_album_title');
		$fontdate = Sonaar_Music::get_option('music_player_date');
		if ( function_exists( 'run_sonaar_music_pro' ) ){
			$fontStickyPlayer = Sonaar_Music::get_option('sticky_player_typo');
			$data .= ( isset($fontStickyPlayer['font-family']) && $fontStickyPlayer['font-family'] != NULL && !strpos($fontStickyPlayer['font-family'], '_safe_') )?'@import url(//fonts.googleapis.com/css?family='. $fontStickyPlayer['font-family'] .');':'';
		}
		$data .= ( isset($font['font-family']) && $font['font-family'] != NULL && !strpos($font['font-family'], '_safe_') )?'@import url(//fonts.googleapis.com/css?family='. $font['font-family'] .');':'';
		$data .= ( isset($fontTitle['font-family']) && $fontTitle['font-family'] != NULL && !strpos($fontTitle['font-family'], '_safe_') )?'@import url(//fonts.googleapis.com/css?family='. $fontTitle['font-family'] .');':'';
		$data .= ( isset($fontdate['font-family']) && $fontdate['font-family'] != NULL && !strpos($fontdate['font-family'], '_safe_') )?'@import url(//fonts.googleapis.com/css?family='. $fontdate['font-family'] .');':'';
		
			
		if( isset($font['font-family']) && $font['font-family'] !== ''){
			$data .= addFontStyle( $font['font-family'], '.iron-audioplayer  .playlist .audio-track, .iron-audioplayer .track-title, .iron-audioplayer .album-store, .iron-audioplayer  .playlist .track-number, .iron-audioplayer .sr_it-playlist-title');
		}
		$data .= (  isset($font['font-size']) && $font['font-size'] !== '' )? '.iron-audioplayer  .playlist .audio-track, .iron-audioplayer .track-title, .iron-audioplayer .album-store, .iron-audioplayer  .playlist .track-number, .iron-audioplayer .sr_it-playlist-title{ font-size:' . $font['font-size'] . ';}' :'';
		$data .= ( isset($font['color']) && $font['color'] !== '' )? '.iron-audioplayer  .playlist .audio-track, .iron-audioplayer .track-title, .iron-audioplayer .album-store, .iron-audioplayer  .playlist .track-number, .iron-audioplayer .sr_it-playlist-title{ color:' . $font['color'] . ';}' :'';

		if( isset($fontTitle['font-family']) && $fontTitle['font-family'] !== ''){
			$data .= addFontStyle( $fontTitle['font-family'], '.iron-audioplayer .sr_it-playlist-title' );
		}

		$data .= ( isset($fontTitle['font-size']) && $fontTitle['font-size'] !== '' )? '.iron-audioplayer .sr_it-playlist-title{ font-size:' . $fontTitle['font-size'] . ';}' :'';
		$data .= ( isset($fontTitle['color']) && $fontTitle['color'] !== '' )? ' .iron-audioplayer .sr_it-playlist-title{ color:' . $fontTitle['color'] . ';}' :'';
		
		
		if( isset($fontdate['font-family']) && $fontdate['font-family'] !== ''){
			$data .= addFontStyle( $fontdate['font-family'], '.iron-audioplayer .sr_it-playlist-release-date' );
		}	
		$data .= ( isset($fontdate['font-size']) && $fontdate['font-size'] !== '' )? '.iron-audioplayer .sr_it-playlist-release-date{ font-size:' . $fontdate['font-size'] . ';}' :'';
		$data .= ( isset($fontdate['color']) && $fontdate['color'] !== '' )? '.iron-audioplayer .sr_it-playlist-release-date{ color:' . $fontdate['color'] . ';}' :'';
		
		if ( function_exists( 'run_sonaar_music_pro' ) ){
			//set color typography styles
			if( isset($fontStickyPlayer['font-family']) && $fontStickyPlayer['font-family'] !== ''){
				$data .= addFontStyle( $fontStickyPlayer['font-family'], '#sonaar-player' );
			}
			$data .= ( isset($fontStickyPlayer['font-size']) && $fontStickyPlayer['font-size'] !== '' )? '#sonaar-player{ font-size:' . $fontStickyPlayer['font-size'] . ';}' :'';
			$data .= ( isset($fontStickyPlayer['color']) && $fontStickyPlayer['color'] !== '' )? 'div#sonaar-player{ color:' . $fontStickyPlayer['color'] . ';}' :'';
			
			//set Featured Color styles
			$data .= ( Sonaar_Music::get_option('sticky_player_featured_color') !== '' )? '#sonaar-player .player, #sonaar-player .player .wavesurfer .volume .slider-container, #sonaar-player .close.btn_playlist:before, #sonaar-player .close.btn_playlist:after{border-color:' . Sonaar_Music::get_option('sticky_player_featured_color') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_featured_color') !== '' )? '#sonaar-player .player .wavesurfer .volume .slider-container:before{border-top-color:' . Sonaar_Music::get_option('sticky_player_featured_color') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_featured_color') !== '' )? '#sonaar-player .playlist button.play, #sonaar-player.list-type-podcast .playlist .tracklist .sonaar-callToAction, #sonaar-player.list-type-podcast .store .track-store li a, #sonaar-player .close.btn-player, #sonaar-player .mobileProgress, #sonaar-player .ui-slider-handle, .ui-slider-range{background-color:' . Sonaar_Music::get_option('sticky_player_featured_color') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_featured_color') !== '' )? '#sonaar-player .playlist .tracklist li.active, #sonaar-player .playlist .tracklist li.active a, #sonaar-player .playlist .title{color:' . Sonaar_Music::get_option('sticky_player_featured_color') . ';}' : '';
			
			// set Labels and Buttons styles
			$data .= ( Sonaar_Music::get_option('sticky_player_labelsandbuttons') !== '' )? '#sonaar-player .player .wavesurfer .timing, #sonaar-player .album-title, #sonaar-player .playlist .tracklist li, #sonaar-player .playlist .tracklist li a, #sonaar-player .player .store .track-store li a, #sonaar-player .track-store li, #sonaar-player .sonaar-extend-button{color:' . Sonaar_Music::get_option('sticky_player_labelsandbuttons') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_labelsandbuttons') !== '' )? '#sonaar-player .playlist .track-number svg path, #sonaar-player .playlist .track-number svg rect, #sonaar-player .control rect, #sonaar-player .control path, #sonaar-player .control polygon, .volume .icon path, #sonaar-player .shuffle path{fill:' . Sonaar_Music::get_option('sticky_player_labelsandbuttons') . ';}' : '';

			// set sticky background color
			$data .= ( Sonaar_Music::get_option('sticky_player_background') !== '' )? '#sonaar-player, #sonaar-player .player, #sonaar-player .store, #sonaar-player .player .wavesurfer .volume .slider-container{background-color:' . Sonaar_Music::get_option('sticky_player_background') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_background') !== '' )? '#sonaar-player .player .wavesurfer .volume .slider-container:after{border-top-color:' . Sonaar_Music::get_option('sticky_player_background') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_background') !== '' )? '#sonaar-player .playlist button.play, #sonaar-player.list-type-podcast .playlist .tracklist .sonaar-callToAction,#sonaar-player.list-type-podcast .player .store .track-store li a{color:' . Sonaar_Music::get_option('sticky_player_background') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_background') !== '' )? '#sonaar-player .close.btn-player rect{fill:' . Sonaar_Music::get_option('sticky_player_background') . ';}' : '';
			$data .= ( Sonaar_Music::get_option('sticky_player_background') !== '' )? '#sonaar-player .close.btn-player.enable:after, #sonaar-player .close.btn-player.enable:before{border-color:' . Sonaar_Music::get_option('sticky_player_background') . ';}' : '';

			// set sticky Mobile Progress bar color
			$data .= ( Sonaar_Music::get_option('sticky_player_featured_color') !== '' )? '#sonaar-player .mobileProgressing, #sonaar-player .progressDot{background-color:' . Sonaar_Music::get_option('sticky_player_soundwave_progress_bars') . ';}' : '';
			if( Sonaar_Music::get_option('mobile_progress_bars') != '' ){
				$data .= ( Sonaar_Music::get_option('sticky_player_featured_color') !== '' )? '#sonaar-player div.mobileProgressing, #sonaar-player div.progressDot{background-color:' . Sonaar_Music::get_option('mobile_progress_bars') . ';}' : '';
			}
		}
		


		$data .= ( Sonaar_Music::get_option('music_player_featured_color') !== '' )? '.iron-audioplayer  .playlist a,.iron-audioplayer .playlist .current .audio-track, .playlist .current .track-number{color:' . Sonaar_Music::get_option('music_player_featured_color') . ';}' : '';
		$data .= ( Sonaar_Music::get_option('music_player_store_drawer') !== '' )? '.iron-audioplayer  .playlist .song-store-list-menu .fa-ellipsis-v{color:' . Sonaar_Music::get_option('music_player_store_drawer') . ';}' : '';
		$data .= ( Sonaar_Music::get_option('music_player_featured_color') !== '' )? '.iron-audioplayer  .playlist .audio-track path, .iron-audioplayer  .playlist .audio-track rect{fill:' . Sonaar_Music::get_option('music_player_featured_color') . ';}' : '';
		$data .= ( Sonaar_Music::get_option('music_player_icon_color') !== '' )? '.iron-audioplayer .control rect, .iron-audioplayer .control path, .iron-audioplayer .control polygon{fill:' . Sonaar_Music::get_option('music_player_icon_color') . ';}' : '';
		$data .= ( Sonaar_Music::get_option('music_player_progress_color') !== '' )? '.iron-audioplayer .sonaar_fake_wave .sonaar_wave_cut rect{fill:' . Sonaar_Music::get_option('music_player_progress_color') . ';}' : '';
		$data .= ( Sonaar_Music::get_option('music_player_progress_color') !== '' )? '#sonaar-player .sonaar_fake_wave .sonaar_wave_base rect{fill:' . Sonaar_Music::get_option('sticky_player_soundwave_bars') . ';}' : '';
		$data .= ( Sonaar_Music::get_option('music_player_progress_color') !== '' )? '#sonaar-player .sonaar_fake_wave .sonaar_wave_cut rect{fill:' . Sonaar_Music::get_option('sticky_player_soundwave_progress_bars') . ';}' : '';
		
		wp_add_inline_style( $this->plugin_name, $data );
		
	}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Register Script for elementor
		wp_register_script( 'sr-scripts', plugin_dir_url( __FILE__ ) . 'js/sr-scripts.js', array( 'jquery' ), $this->version, true );
		// other scripts
		wp_register_script( 'sonaar-music', plugin_dir_url( __FILE__ ) . 'js/sonaar-music-public.js', array( 'jquery' ), $this->version, true );		
		wp_register_script( 'moments', plugin_dir_url( __FILE__ ) . 'js/iron-audioplayer/00.moments.min.js', array(), $this->version, true );
		wp_register_script( 'wave', plugin_dir_url( __FILE__ ) . 'js/iron-audioplayer/00.wavesurfer.min.js', array(), $this->version, true );
		wp_register_script( 'sonaar-music-mp3player', plugin_dir_url( __FILE__ ) . 'js/iron-audioplayer/iron-audioplayer.js', array( 'jquery','sr-scripts', 'sonaar-music' ,'moments', 'wave', 'd3' ), $this->version, true );
		
		wp_enqueue_script('d3', '//cdn.jsdelivr.net/npm/d3@5/dist/d3.min.js', array(), NULL, true);
		/* Enqueue Sonaar Music mp3player js file on single Album Page */
		if ( is_single() && get_post_type() == 'album' ) {
			wp_enqueue_script( 'sonaar-music-mp3player' );			
		}
		
		wp_localize_script( $this->plugin_name . '-mp3player', 'sonaar_music', array(
			'plugin_dir_url'=> plugin_dir_url( __FILE__ ),
			'option' => Sonaar_Music::get_option( 'allOptions' )
		));

	}
	public function srbodyclass(){ // add special class if onair2 theme is used
		return ['qt-html5audio-disable'];
	}
	public function editor_enqueue_scripts() {
		/* Enqueue Sonaar Music related CSS and Js file */
		global $pagenow;
		if ( !is_admin()  && !isset($_REQUEST['elementor-preview']) ) {			
			return;
		}
		
		wp_enqueue_style( 'sonaar-music' );
		wp_enqueue_style( 'sonaar-music-pro' );
		wp_enqueue_script( 'sonaar-music-mp3player' );
		wp_enqueue_script( 'sonaar-music-pro-mp3player' );
		wp_enqueue_script( 'sonaar_player' );
		
		if ( function_exists('sonaar_player') ) {
			add_action('wp_footer','sonaar_player', 12);
		}
	}
	/**
	 * Inline style for the plugin
	 **/
	/*public function inline_style(){
		$data = "";
		$font = Sonaar_Music::get_option('music_player_playlist');
		// var_dump($font);
		// die();

		

		$data .= ( $font['font-family'] !== '' && !strpos($font['font-family'], '_safe_') )?'@import url(//fonts.googleapis.com/css?family='. $font['font-family'] .');':'';
		var_dump($data);
		die();
		return $data;
	}*/

}
