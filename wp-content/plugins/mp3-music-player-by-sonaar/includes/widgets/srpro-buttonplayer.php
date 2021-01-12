<?php
namespace Elementor;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;
use Elementor\Controls_Manager;


/**
 * Elementor Hello World
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */

class SRPRO_ButtonPlayer extends Widget_Base {

	
	public function __construct() {
		parent::__construct();
		$this->init_control();
	}

	public function get_name() {
		return 'sr-buttonplayer';
	}

	public function register_controls($element, $args) {

		$element->start_controls_section(
			'sr_buttonplayer_section',
			[
				'label' => __( 'Launch the Sticky Audio Player', 'sonaar-music' ),
				
			]
		);
		$element->add_control(
			'sr_music_cpt_toggle',
			[
				'label' => __( 'Launch Sticky Audio Player', 'sonaar-music' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => __( 'Important: Make sure the button link parameter is blank in the section above (eg: remove the hashtag #)', 'sonaar-music' ),
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);
		$element->add_control(
			'sr_music_cpt_getlatest',
			[
				'label' => __( 'Play Most Recent Playlist', 'sonaar-music' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
				'condition' => ['sr_music_cpt_toggle' => 'yes','sr_play_current_id!' => 'yes',],
			]
		);
		$element->add_control(
			'playlist_list',
				[
					'label' => esc_html__( 'Select Playlist(s)', 'sonaar-music' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::SELECT,
					'multiple' => true,
					'options'               => sr_plugin_elementor_select_playlist(),            	
					'condition' => ['sr_music_cpt_toggle' => 'yes', 'sr_play_current_id!' => 'yes', 'sr_music_cpt_getlatest!' => 'yes'],
				]
		);
		$element->add_control(
			'sr_play_current_id',
			[
				'label'							 	=> __( 'Play its own Post Tracklist', 'sonaar-music' ),
				'description' 						=> __( 'Check this case if you want to launch the sticky player with the tracks found in this post', 'sonaar-music' ),
				'type' 								=> \Elementor\Controls_Manager::SWITCHER,
				'yes' 								=> __( 'Yes', 'sonaar-music' ),
				'no' 								=> __( 'No', 'sonaar-music' ),
				'return_value' 						=> 'yes',
				'default' 							=> '',

			]
		);

			$element->end_controls_section();

		}

		public function renderfnc($button) {

			 if( 'button' === $button->get_name() ) {
			    // Get the settings
			    $settings = $button->get_settings();
			    if( $settings['sr_music_cpt_toggle'] =="yes" && $settings['playlist_list'] ||  $settings['sr_play_current_id'] == 'yes') {
					wp_enqueue_style( 'sonaar-music' );
					wp_enqueue_style( 'sonaar-music-pro' );
					wp_enqueue_script( 'sonaar-music-mp3player' );
					wp_enqueue_script( 'sonaar-music-pro-mp3player' );
					wp_enqueue_script( 'sonaar_player' );
					if ( function_exists('sonaar_player') ) {
						add_action('wp_footer','sonaar_player', 12);
					}
			    
					if ( $settings['sr_play_current_id']=='yes' ){ //If "Play its own Post ID track" option is enable
						$display_playlist_ar = get_the_ID();
					} else if( $settings['sr_music_cpt_getlatest'] != 'yes' ){
			    		$display_playlist_ar = $settings['playlist_list'];
			    	} else {
			    		$display_playlist_ar = sr_plugin_elementor_getLatestPost('album');
			    	}
					$audiocall = "javascript:IRON.sonaar.player.setPlayerAndPlay({id:" . $display_playlist_ar . "})";
					//print_r($audiocall);					
					$button->add_render_attribute( 'button', 'href', $audiocall, true );
			    }
			   
			  }
		}
		
		protected function init_control() {
			add_action( 'elementor/element/button/section_button/after_section_end', [ $this, 'register_controls' ], 10, 2 );
			add_action( 'elementor/widget/before_render_content', [ $this, 'renderfnc' ], 10, 2 );

		}
	}

Plugin::instance(new SRPRO_ButtonPlayer() );