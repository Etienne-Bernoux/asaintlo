<?php
/**
 * Radio Block Class
 *
 * @since 1.6.0
 * @todo  - Add Block
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sonaar Block Class
 */
class Sonaar_Block {

	/**
	 * Contruction
	 */
	public function __construct() {
		$this->version = SRMP3_VERSION;
		add_action( 'init', array( $this, 'sonaar_block_editor_style_script' ) );

        add_action( 'enqueue_block_editor_assets', array($this, 'sonaar_block_editor_scripts') );
	}
    
    function sonaar_block_editor_scripts() {
		$sonaar_mp3player = 'sonaar-music-mp3player';

        // Register Script for elementor
		wp_register_script( 'sr-scripts', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/sr-scripts.js', array( 'jquery' ), $this->version, true );
		// other scripts
		wp_register_script( 'sonaar-music', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/sonaar-music-public.js', array( 'jquery' ), $this->version, true );		
		wp_register_script( 'moments', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/iron-audioplayer/00.moments.min.js', array(), $this->version, true );
		wp_register_script( 'wave', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/iron-audioplayer/00.wavesurfer.min.js', array(), $this->version, true );
		wp_register_script( 'sonaar-music-mp3player', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/iron-audioplayer/iron-audioplayer.js', array( 'jquery','sr-scripts', 'sonaar-music' ,'moments', 'wave', 'd3' ), $this->version, true );

		wp_enqueue_script('d3', '//cdn.jsdelivr.net/npm/d3@5/dist/d3.min.js', array(), NULL, true);
		/* Enqueue Sonnar Music mp3player js file on single Album Page */
		if ( is_single() && get_post_type() == 'album' ) {
			wp_enqueue_script( 'sonaar-music-mp3player' );			
		}

		wp_localize_script( 'sonaar-music-mp3player', 'sonaar_music', array(
			'plugin_dir_url'=> plugin_dir_url( dirname( __FILE__ ) ),
			'option' => Sonaar_Music::get_option( 'allOptions' )
		));

		
		

		/* Enqueue Sonaar Music related CSS and Js file */
		wp_enqueue_style( 'sonaar-music' );
		wp_enqueue_script( 'sr-scripts' );
		wp_enqueue_script( 'sonaar-music-mp3player' );

		if ( function_exists( 'run_sonaar_music_pro' ) ){
			$sonaar_music_pro = new Sonaar_Music_Pro_Public( 'sonaar-music-pro', '2.0.2' );
			$sonaar_music_pro->enqueue_styles();
	    	$sonaar_music_pro->enqueue_scripts();

			
			wp_enqueue_style( 'sonaar-music-pro' );
			wp_enqueue_script( 'sonaar-music-pro-mp3player' );
			wp_enqueue_script( 'sonaar_player' );

			$sonaar_mp3player = 'sonaar-music-pro-mp3player';
	   }

		if ( function_exists('sonaar_player') ) {
			add_action('wp_footer','sonaar_player', 12);
        }
        
		wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css' );
		wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js','1.0.0', true );
        
        wp_enqueue_script(
            'sonaar-block-js',
            plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/sonaar-block.js',
            array(
                $sonaar_mp3player,
                )
        );
        
    }

	/**
	 * Regester Block Scripts
	 *
	 * @return void
	 */
	function sonaar_block_editor_style_script() {

		$sonaar_mp3player = 'sonaar-music-mp3player';

		if ( function_exists( 'run_sonaar_music_pro' ) ){
			$sonaar_mp3player = 'sonaar-music-pro-mp3player';
	   }

		// Register required js and css files
		wp_register_style( 'sonaar-music', plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/sonaar-music-public.css', array(), $this->version, 'all' );
	
		// Register the tb1 block
		wp_register_script( 'sonaar-block-script', plugin_dir_url( dirname( __FILE__ ) ) . 'build/index.js', array( 'jquery', $sonaar_mp3player,'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-data', 'wp-editor'), $this->version );
		
	    
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'sonaar/sonaar-block',
				array(
					'attributes'    => $this->sr_plugin_block_attribute(),
					'editor_script' => 'sonaar-block-script',
					'editor_style'  => 'sonaar-block-editor-style',
                    'style'         => 'sonaar-block-frontend-style',
                    'render_callback' => array($this, 'render_sonaar_block'),
				)
			);
		}
    }
    
    function render_sonaar_block( $attributes ) {

		ob_start();
        $album_id = ( isset( $attributes['album_id'] ) && $attributes['album_id'] ) ? $attributes['album_id'] : '';
        $playlist_show_album_market = ( isset( $attributes['playlist_show_album_market'] ) && $attributes['playlist_show_album_market'] ) ? true : false;
        $playlist_hide_artwork      = ( isset( $attributes['playlist_hide_artwork'] ) && $attributes['playlist_hide_artwork'] ) ? true : false;
        $playlist_show_playlist     = ( isset( $attributes['playlist_show_playlist'] ) && $attributes['playlist_show_playlist'] ) ? true : false;
        $playlist_show_soundwave    = ( isset( $attributes['playlist_show_soundwave'] ) && $attributes['playlist_show_soundwave'] ) ? true : false;
		$play_current_id            = ( isset( $attributes['play_current_id'] ) && $attributes['play_current_id'] ) ? true : false;
		
		$sticky_player  = false;
		$shuffle = false;
		$scrollbar = false;
		$scrollbar_height = '200px';

		if ( function_exists( 'run_sonaar_music_pro' ) ){
			$sticky_player  = ( isset( $attributes['enable_sticky_player'] ) && $attributes['enable_sticky_player'] ) ? true : false;
			$shuffle = ( isset( $attributes['enable_shuffle'] ) && $attributes['enable_shuffle'] ) ? true : false;
			$scrollbar = ( isset( $attributes['enable_scrollbar'] ) && $attributes['enable_scrollbar'] ) ? true : false;
			$scrollbar_height = ( isset( $attributes['scrollbar_height'] ) && $attributes['scrollbar_height'] ) ? $attributes['scrollbar_height'] .'px' : '200px';
		}
		
		
		$shortcode = '[sonaar_audioplayer titletag_soundwave="div" titletag_playlist="h3" hide_artwork="' . $playlist_hide_artwork .'" show_playlist="' . $playlist_show_playlist .'" show_album_market="' . $playlist_show_album_market .'" hide_timeline="' . $playlist_show_soundwave .'" sticky_player="' . $sticky_player .'" wave_color="false" wave_progress_color="false" shuffle="' . $shuffle .'" show_track_market="true" ';
		
		if( $scrollbar && function_exists( 'run_sonaar_music_pro' ) ) {
			$shortcode .= 'scrollbar="true" ';
		}
		
		if ( $play_current_id  ){ //If "Play its own Post ID track" option is enable
			$postid = get_the_ID();
			$shortcode .= 'albums="' . $postid . '" ';
		} else {
			$display_playlist_ar = $album_id;

			if(is_array($display_playlist_ar)){
                $display_playlist_ar = implode(", ", $display_playlist_ar);
			}

			// WIP
			if (!$display_playlist_ar) { //If no playlist is selected, play the latest playlist
				$shortcode .= 'play-latest="yes" ';
			}else{
                $shortcode .= 'albums="' . $display_playlist_ar . '" ';
			}
        }
        
		$shortcode .= ']';

		$renadom_number = rand(10,100);
		$block_id = 'sonaar_music_' . $renadom_number;

		if( $scrollbar && function_exists( 'run_sonaar_music_pro' ) ) {			
			$scrollbar_css = " #$block_id .iron-audioplayer .playlist ul {
				height: $scrollbar_height;
				overflow-y: hidden;
				overflow-x: hidden;
			} ";

			if( is_admin() ) {
			} else {
				wp_add_inline_style( 'sonaar-music-pro', $scrollbar_css );
			}

			
			echo '<style>';
			echo $scrollbar_css;
			echo '</style>';
			
		}

		echo '<div id="'. $block_id .'">';
	   echo do_shortcode( $shortcode );
	   echo '</div>';
    
       return ob_get_clean();
    }
	
	private function sr_plugin_block_attribute() {
		$attributes_pro = array();

		$attributes = array(
			'run_pro' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'album_id' => array(
				'type' => 'array',
				'default' => [],
				'items'   => [
					'type' => 'integer',
				]
			),
			'playlist_list' => array(
				'type'    => 'array',
				'default' => $this->sr_plugin_block_select_playlist()
			),
			'enable_sticky_player' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'enable_shuffle' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'enable_scrollbar' => array(
				'type'    => 'boolean',
				'default' => false,
			),				
			'scrollbar_height' => array(
				'type'    => 'integer',
				'default' => 200,
			),
			'playlist_show_playlist' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'playlist_show_album_market' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'playlist_hide_artwork' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'playlist_show_soundwave' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'play_current_id' => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

		
		if ( function_exists( 'run_sonaar_music_pro' ) ) {
			
			$attributes['enable_sticky_player'] = array(
				'type'    => 'boolean',
				'default' => true,
			);
			$attributes['enable_shuffle'] = array(
				'type'    => 'boolean',
				'default' => false,
			);
			$attributes['enable_scrollbar'] = array(
				'type'    => 'boolean',
				'default' => false,
			);
			$attributes['scrollbar_height'] = array(
				'type'    => 'integer',
				'default' => 200,
			);

			$attributes['run_pro'] = array(
				'type'    => 'boolean',
				'default' => true,
			);
		}

		return $attributes;
	}

	private function insertValueAtPosition($arr, $insertedArray, $position) {
		$i = 0;
		$new_array=[];
		foreach ($arr as $key => $value) {
			if ($i == $position) {
				foreach ($insertedArray as $ikey => $ivalue) {
					$new_array[$ikey] = $ivalue;
				}
			}
			$new_array[$key] = $value;
			$i++;
		}
		return $new_array;
	}

    private function sr_plugin_block_select_playlist() {
        $sr_playlist_list = get_posts(array(
            'post_type' => 'album',
            'showposts' => 999,
        ));
        $options = array();

        if ( ! empty( $sr_playlist_list ) && ! is_wp_error( $sr_playlist_list ) ){
            
            foreach ( $sr_playlist_list as $post ) {
                $options[] = array(
                    'label' => $post->post_title,
                    'value' => $post->ID,
                );
            }
        } else {
            $options[0] = esc_html__( 'Create a Playlist First', 'sonaar-music' );
        }
        return $options;
	}

}

new Sonaar_Block();
