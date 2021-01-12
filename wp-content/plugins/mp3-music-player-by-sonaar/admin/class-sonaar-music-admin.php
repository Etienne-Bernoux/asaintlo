<?php

/**
* The admin-specific functionality of the plugin.
*
* @link       sonaar.io
* @since      1.0.0
*
* @package    Sonaar_Music
* @subpackage Sonaar_Music/admin
*/

/**
* The admin-specific functionality of the plugin.
*
* Defines the plugin name, version, and two examples hooks for how to
* enqueue the admin-specific stylesheet and JavaScript.
*
* @package    Sonaar_Music
* @subpackage Sonaar_Music/admin
* @author     Edouard Duplessis <eduplessis@gmail.com>
*/
class Sonaar_Music_Admin {
    
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
    * @param      string    $plugin_name       The name of this plugin.
    * @param      string    $version    The version of this plugin.
    */
    public function __construct( $plugin_name, $version ) {
        
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->load_dependencies();
        
    }
    
    /**
    * Load the required dependencies for the admin area.
    *
    * Include the following files that make up the plugin:
    *
    * @since		1.0.0
    */
    public function load_dependencies(){
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2/init.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-calltoaction/cmb2-calltoaction.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-conditionals/cmb2-conditionals.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-attached-posts/cmb2-attached-posts-field.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-store-list/song-store-field-type.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-typography/typography-field-type.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-multiselect/cmb2-multiselect.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/cmb2-switch-button-metafield/switch_metafield.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sonaar-music-widget.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sonaar-music-block.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/library/Shortcode_Button/shortcode-button.php';    
    }

    /**
    * Register the stylesheets for the admin area.
    *
    * @since    1.0.0
    */
    public function editor_scripts() {
        wp_enqueue_style( 'sonaar-elementor-editor', plugin_dir_url(dirname(__FILE__)) . 'admin/css/elementor-editor.css', array(), $this->version, 'all' );
    }

    public function enqueue_styles() {
        wp_enqueue_style( 'sonaar-music-admin', plugin_dir_url( __FILE__ ) . 'css/sonaar-music-admin.css', array(), $this->version, 'all' );
         wp_enqueue_style( 'cmb2_switch-css', plugin_dir_url( __FILE__ ) . 'library/cmb2-switch-button-metafield/switch_metafield.css', false, $this->version ); //CMB2 Switch Styling
         wp_enqueue_script( 'cmb2_switch-js', plugin_dir_url( __FILE__ ) . 'library/cmb2-switch-button-metafield/switch_metafield.js' , '', '1.0.0', true );  // CMB2 Switch Event
    }

    /**
    * Register the JavaScript for the admin area.
    *
    * @since    1.0.0
    */
    public function enqueue_scripts( $hook ) {
        if ($hook == 'album_page_iron_music_player' || $hook == 'album_page_sonaar_music_promo') {
            wp_enqueue_script( 'vuejs', "//cdn.jsdelivr.net/npm/vue/dist/vue.min.js" , array(), $this->version, false );
            wp_enqueue_script( 'polyfill', "//unpkg.com/babel-polyfill@latest/dist/polyfill.min.js" , array(), $this->version, false );
            wp_enqueue_script( 'bootstrap-vue', "//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.js" , array(), $this->version, false );
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sonaar-music-admin.js', array( 'jquery','vuejs','polyfill','bootstrap-vue' ), $this->version, true );
            wp_enqueue_script( 'cmb2_conditionallogic-js', plugin_dir_url( __FILE__ ) . 'library/cmb2-conditional-logic/cmb2-conditional-logic.js' , '', '1.0.0', true );  // Used for plugin settings page only. it does not work on group repeater fields
        }
        
    }

    public function init_options() {

        function defaultWaveform(){
            if( Sonaar_Music::get_option('music_player_coverSize') != null && Sonaar_Music::get_option('waveformType') == null ){
                return 'wavesurfer';
            }else{
                return 'mediaElement';
            }
        }
        function get_the_cpt(){
                $post_types = get_post_types(['public'   => true, 'show_ui' => true], 'objects');
                $posts = array();
                foreach ($post_types as $post_type) {
                    if ($post_type->name == 'attachment' || $post_type->name == 'elementor_library' )
                        continue; 

                    $posts[$post_type->name] = $post_type->labels->singular_name;
                }
                return $posts;
        }
        function getPlaylists(){
            
            $args = array(
                'post_type'     => 'album',
                'post_status'   => 'publish',
                'orderby'       => 'title',
                'order'         => 'ASC',
                'posts_per_page'=>-1
            );
            $posts = get_posts( $args );
            $post_options = array();
            if ( $posts ) {
                foreach ( $posts as $post ) {
                    $post_options[ $post->ID ] = $post->post_title;
                }
            }
            return $post_options;
        }
        function music_player_coverSize(){
            $music_player_coverSize = array();
            $imageSizes = get_intermediate_image_sizes();
            foreach ($imageSizes as $value) {
                $music_player_coverSize[$value] = $value;
            }
            return $music_player_coverSize;
        }

        $cmb_options = new_cmb2_box( array(
            'id'            => 'sonaar_music_network_option_metabox',
            'title'         => esc_html__( 'Sonaar Music', 'sonaar-music' ),
            'object_types'  => array( 'options-page' ),
            'option_key'    => 'iron_music_player', // The option key and admin menu page slug.
            'icon_url'      => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
            'menu_title'    => esc_html__( 'Settings', 'sonaar-music' ), // Falls back to 'title' (above).
            'parent_slug'   => 'edit.php?post_type=album', // Make options page a submenu item of the themes menu.
            'capability'    => 'manage_options', // Cap required to view options-page.
            'position'      => 1,
        ) );        
        /**
        * Manually render a field.
        *
        * @param  array      $field_args Array of field arguments.
        * @param  CMB2_Field $field      The field object
        */
        function banner_row( $field_args, $field ) {
            require_once plugin_dir_path( __FILE__ ) . 'partials/sonaar-music-admin-display.php';
        }
        
        $cmb_options->add_field( array(
            'name'          => esc_html__('Audio Player General Settings', 'sonaar-music'),
            'type'          => 'title',
            'id'            => 'music_player_title'
        ) );

        $cmb_options->add_field( array(
            'name'          => esc_html__('Waveform Type', 'sonaar-music'),
            'id'            => 'waveformType',
            'type'          => 'select',
            'options'       => array(
                'mediaElement'  => 'Synthetic Waveform (faster)',
                'wavesurfer'    => 'Dynamic Waveform (slower)'
            ),
            'default'       => defaultWaveform()
        ) ); 

        $cmb_options->add_field( array(
            'name'          => esc_html('Post Types', 'sonaar-music'),
            'desc'          => esc_html('Select the post types for which you want to enable playlist creation', 'sonaar-music'),
            'id'            => 'srmp3_posttypes',
            'type'          => 'multicheck',
            'select_all_button' => false,
            'options'       => get_the_cpt(),
            'default'        => array('album', 'product'),
        ) );
        
        $cmb_options->add_field( array(
            'name'          => esc_html__('Display Artist Name', 'sonaar-music'),
            'id'            => 'show_artist_name',
            'type'          => 'checkbox',
        ) );
        $cmb_options->add_field( array(
            'name'          => esc_html__('Artist Name Prefix Separator', 'sonaar-music'),
            'id'            => 'artist_separator',
            'type'          => 'text_small',
            'default'       => __('by', 'sonaar-music'),
            'description'   => esc_html__('To be used as a separator between track title and artist name. Eg: Track Title "by" Artist Name', 'sonaar-music'),
            'attributes'    => array(
                'data-conditional-id'    => 'show_artist_name',
                'data-conditional-value' => 'on',
            ),
        ) );  

        if ( function_exists( 'run_sonaar_music_pro' ) ){
            $cmb_options->add_field( array(
                'name'          => esc_html__('Sticky Player Settings', 'sonaar-music'),
                'type'          => 'title',
                'id'            => 'music_player_sticky_title'
            ) );
            $cmb_options->add_field( array(
                'name'          => esc_html__('Enable Sticky Player on every pages', 'sonaar-music'),
                'desc'          => __('By selecting playlist(s), a sticky player will be displayed on every pages of your site, except for the page(s) for which a sticky player has already been set.','sonaar-music'),
                'id'            => 'overall_sticky_playlist',
                'type'          => 'multicheck',
                'options'       => getPlaylists(),
            ) );
            $cmb_options->add_field( array(
                'name'          => esc_html__('Enable Continuous Player', 'sonaar-music'),
                'desc'          => __('Sticky player will resume and play the audio where it left when new page is loaded. Also known as persistent player. Read our <a href="https://sonaar.io/blog" target="_blank">blog post</a> about it.','sonaar-music'),
                'id'            => 'enable_continuous_player',
                'type'          => 'switch',
                'default'       => 0,
            ) );
            $cmb_options->add_field( array(
                'name'          => esc_html__('Enable shuffle mode', 'sonaar-music'),
                'id'            => 'overall_shuffle',
                'type'          => 'checkbox'
            ) );
        }

        $cmb_options->add_field( array(
            'name'          => esc_html__('Audio Player Typography and Colors', 'sonaar-music'),
            'type'          => 'title',
            'id'            => 'music_player_typography'
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_playlist',
            'type'          => 'typography',
            'name'          => esc_html__('Playlist', 'sonaar-music'),
            'description'   => esc_html__('Choose a font, font size and color', 'sonaar-music'),
            'fields'        => array(
                'font-weight' 		=> false,
                'background' 		=> false,
                'text-align' 		=> false,
                'text-transform'    => false,
                'line-height' 		=> false,
            )
        ) );
        
        $cmb_options->add_field( array(
            'id'            => 'music_player_album_title',
            'type'          => 'typography',
            'name'          => esc_html__('Album Title', 'sonaar-music'),
            'description'   => esc_html__('Choose a font, font size and color', 'sonaar-music'),
            'fields'        => array(
                'font-weight' 		=> false,
                'background' 		=> false,
                'text-align' 		=> false,
                'text-transform' 	=> false,
                'line-height' 		=> false,
            )
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_date',
            'type'          => 'typography',
            'name'          => esc_html__('Album Subtitle', 'sonaar-music'),
            'description'   => esc_html__('Choose a font, font size and color', 'sonaar-music'),
            'fields'        => array(
                'font-weight' 		=> false,
                'background' 		=> false,
                'text-align' 		=> false,
                'text-transform' 	=> false,
                'line-height' 		=> false,
            )
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_featured_color',
            'type'          => 'colorpicker',
            'name'          => esc_html__('Button Colors', 'sonaar-music'),
            'class'         => 'color',
            'default'       => 'rgba(0, 0, 0, 1)',
            'options'       => array(
                'alpha'         => true, // Make this a rgba color picker.
            ),
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_store_drawer',
            'type'          => 'colorpicker',
            'name'          => esc_html__('Track Store Drawer Colors', 'sonaar-music'),
            'class'         => 'color',
            'default'       => '#BBBBBB',
            'options'       => array(
                'alpha'         => true, // Make this a rgba color picker.
            ),
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_icon_color',
            'type'          => 'colorpicker',
            'name'          => esc_html__('Player Control', 'sonaar-music'),
            'class'         => 'color',
            'default'       => 'rgba(127, 127, 127, 1)',
            'options'       => array(
                'alpha'         => true, // Make this a rgba color picker.
            ),
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_timeline_color',
            'type'          => 'colorpicker',
            'name'          => esc_html__('SoundWave Bars', 'sonaar-music'),
            'class'         => 'color',
            'default'       => 'rgba(31, 31, 31, 1)',
            'options'       => array(
                'alpha'         => true, // Make this a rgba color picker.
            ),
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_progress_color',
            'type'          => 'colorpicker',
            'name'          => esc_html__('SoundWave Progress Bar', 'sonaar-music'),
            'class'         => 'color',
            'default'       => 'rgba(13, 237, 180, 1)',
            'options'       => array(
                'alpha'         => true, // Make this a rgba color picker.
            ),
        ) );
        $cmb_options->add_field( array(
            'id'            => 'music_player_coverSize',
            'type'          => 'select',
            'name'          => esc_html('Album cover size image source', 'sonaar-music'),
            'show_option_none' => false,
            'default'       => 'custom',
            'options'       => music_player_coverSize(),
        ) ); 

        // STICKY PLAYER OPTIONS IF PRO PLUGIN IS INSTALLED
        if ( function_exists( 'run_sonaar_music_pro' ) ){
            $cmb_options->add_field( array(
                'name'          => esc_html__('Sticky Player Typography and Colors', 'sonaar-music'),
                'type'          => 'title',
                'id'            => 'music_player_sticky_lookandfeel_title'
            ) );
            $cmb_options->add_field( array(
                'id'            => 'sticky_player_typo',
                'type'          => 'typography',
                'name'          => esc_html__('Typography', 'sonaar-music'),
                'fields'        => array(
                    'font-weight'       => false,
                    'background'        => false,
                    'text-align'        => false,
                    'text-transform'    => false,
                    'line-height'       => false,
                )
            ) );
            $cmb_options->add_field( array(
                'id'            => 'sticky_player_featured_color',
                'type'          => 'colorpicker',
                'name'          => esc_html__('Featured Color', 'sonaar-music'),
                'class'         => 'color',
                'default'       => 'rgba(116, 221, 199, 1)',
                'options'       => array(
                    'alpha'         => true, // Make this a rgba color picker.
                ),
            ) );
            $cmb_options->add_field( array(
                'id'            => 'sticky_player_labelsandbuttons',
                'type'          => 'colorpicker',
                'name'          => esc_html__('Labels and Buttons', 'sonaar-music'),
                'class'         => 'color',
                'default'       => 'rgba(255, 255, 255, 1)',
                'options'       => array(
                    'alpha'         => true, // Make this a rgba color picker.
                ),
            ) );            
            $cmb_options->add_field( array(
                'id'            => 'sticky_player_soundwave_bars',
                'type'          => 'colorpicker',
                'name'          => esc_html__('Soundwave Bars', 'sonaar-music'),
                'class'         => 'color',
                'default'       => 'rgba(79, 79, 79, 1)',
                'options'       => array(
                    'alpha'         => true, // Make this a rgba color picker.
                ),
            ) );
            $cmb_options->add_field( array(
                'id'            => 'sticky_player_soundwave_progress_bars',
                'type'          => 'colorpicker',
                'name'          => esc_html__('Soundwave Progress Bars', 'sonaar-music'),
                'class'         => 'color',
                'default'       => 'rgba(116, 221, 199, 1)',
                'options'       => array(
                    'alpha'         => true, // Make this a rgba color picker.
                ),
            ) );
            $cmb_options->add_field( array(
                'id'            => 'mobile_progress_bars',
                'type'          => 'colorpicker',
                'name'          => esc_html__('Mobile Progress Bars', 'sonaar-music'),
                'class'         => 'color',
                'default'       => '',
                'options'       => array(
                    'alpha'         => true, // Make this a rgba color picker.
                ),
            ) );
            $cmb_options->add_field( array(
                'id'            => 'sticky_player_background',
                'type'          => 'colorpicker',
                'name'          => esc_html__('Sticky Background Color', 'sonaar-music'),
                'class'         => 'color',
                'default'       => 'rgba(0, 0, 0, 1)',
                'options'       => array(
                    'alpha'         => true, // Make this a rgba color picker.
                ),
            ) );          
        }

        // DISPLAY GO PRO TAB IN SIDE MENU
        $cmb_promo = new_cmb2_box( array(
            'id'            => 'sonaar_music_promo',
            'title'        	=> esc_html__( 'Go Pro', 'sonaar-music' ),
            'object_types' 	=> array( 'options-page' ),
            'option_key'    => 'sonaar_music_promo', // The option key and admin menu page slug.
            'icon_url'      => 'dashicons-chart-pie', // Menu icon. Only applicable if 'parent_slug' is left empty.
            'menu_title'    => esc_html__( 'Go Pro', 'sonaar-music' ), // Falls back to 'title' (above).
            'parent_slug'   => 'edit.php?post_type=album', // Make options page a submenu item of the themes menu.
            'capability'    => 'manage_options', // Cap required to view options-page.
            'enqueue_js'    => false,
            'cmb_styles'    => false,
            'display_cb'    => 'sonaar_music_promo_display',
            'position'      => 9999,
        ) );
        
        function sonaar_music_promo_display(){
            require_once plugin_dir_path( __FILE__ ) . 'partials/sonaar-music-promo-display.php';
        }

    }
    
    /**
    * Register post fields
    **/
    public function init_postField(){
        $cmb_album = new_cmb2_box( array(
            'id'            => 'acf_albums_infos',
            'title'         => esc_html__( 'Album infos', 'sonaar-music' ),
            'object_types'  => ( Sonaar_Music::get_option('srmp3_posttypes') != null ) ? Sonaar_Music::get_option('srmp3_posttypes') : 'album',
            'context'       => 'normal',
            'priority'      => 'low',
            'show_names'    => true,
            'capability'    => 'manage_options', // Cap required to view options-page.
        ) );
        $cmb_album->add_field( array(
            'id'            => 'alb_release_date',
            'name'          => __('Album Subtitle', 'sonaar-music'),
            'description'   => 'E.g. Release Date: 2019, New Album, Sold Out, etc. ',
            'type'          => 'text'
        ) );

        if ( function_exists( 'run_sonaar_music_pro' ) ){
            $cmb_album->add_field( array(
                'id'            => 'no_track_skip',
                'name'          => __('Do not skip to the next track', 'sonaar-music'),
                'description'   => 'When the current track ends, do not play the second track automatically.',
                'type'          => 'checkbox'
            ));
        }

        $cmb_album->add_field( array(
            'id'            => 'reverse_tracklist',
            'name'          => __('Reverse Order', 'sonaar-music'),
            'description'   => 'Display tracklist in reverse order on the front-end',
            'type'          => 'checkbox'
        ));
        $tracklist = $cmb_album->add_field( array(
            'id'            => 'alb_tracklist',
            'type'          => 'group',
            'name' 			=> __('Tracklist','sonaar-music'),
            'repeatable'    => true, // use false if you want non-repeatable group
            'options'       => array(
                'group_title'   => __( 'Track {#}', 'sonaar-music' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'    => __( 'Add Another track', 'sonaar-music' ),
                'remove_button' => __( 'Remove track', 'sonaar-music' ),
                'sortable'      => true, // beta
                'closed'        => false, // true to have the groups closed by default
            ),
        ) );
        $cmb_album->add_group_field( $tracklist ,array(
            'name'              => esc_html__('Source File', 'sonaar-music'),
            'description'       => 'Please select which type of audio source you want for this track',
            'id'                => 'FileOrStream',
            'type'              => 'radio',
            'show_option_none'  => false,
            'options'           => array(
                'mp3'               => 'Local MP3',
                'stream'            => 'External MP3'
            ),
            'default'           => 'mp3'
        ));
        
        $cmb_album->add_group_field($tracklist, array(
            'id'            => 'track_mp3',
            'name'          => __('MP3 File','sonaar-music'),
            'type'          => 'file',
            'description'   => __('Only .MP3 file accepted','sonaar-music'),
            'query_args'    => array(
                'type'          => 'audio/mpeg',
            ),
            'attributes'    => array(
                'required'               => false, // Will be required only if visible.
                'data-conditional-id'    => wp_json_encode( array( $tracklist, 'FileOrStream' )),
                'data-conditional-value' => 'mp3',
            )
        ));
        
        $cmb_album->add_group_field($tracklist, array(
            'id'            => 'stream_link',
            'classes'       => 'sr-stream-url-field',
            'name'          => __('External Audio link','sonaar-music'),
            'type'          => 'text_url',
            'description'   => __('See <a href="https://sonaar.ticksy.com/article/15845" target="_blank">this article</a> for supported providers. Enter URL that points to your audio file.','sonaar-music'),
            'attributes'    => array(
                'required'               => false, // Will be required only if visible.
                'data-conditional-id'    => wp_json_encode( array( $tracklist, 'FileOrStream' )),
                'data-conditional-value' => 'stream',
            )
        ));
        $cmb_album->add_group_field($tracklist, array(
            'id'            => 'stream_title',
            'classes'       => 'sr-stream-title-field',
            'name'          => __('Track title','sonaar-music'),
            'type'          => 'text',
            'attributes'    => array(
                'required'               => false, // Will be required only if visible.
                'data-conditional-id'    => wp_json_encode( array( $tracklist, 'FileOrStream' )),
                'data-conditional-value' => 'stream',
            )
        ));
        $cmb_album->add_group_field($tracklist, array(
            'id'            => 'stream_album',
            'classes'       => 'sr-stream-album-field',
            'name'          => __('Track Album','sonaar-music'),
            'description'   => __("Leave blank if it's the same as the playlist",'sonaar-music'),
            'type'          => 'text',
            'attributes'    => array(
                'required'               => false, // Will be required only if visible.
                'data-conditional-id'    => wp_json_encode( array( $tracklist, 'FileOrStream' )),
                'data-conditional-value' => 'stream',
            )
        ));
        
        $cmb_album->add_group_field( $tracklist, array(
            'id'            => 'song_store_list',
            'type'          => 'store_list',
            'name' 			=> __('Where to buy/download this track?','sonaar-music'),
            'repeatable'    => true,
            'icon'          => true,
            'options'       => array(
                'sortable'      => true, // beta
            ),
            'text'          => array(
                'add_row_text'      => 'Add store',
                'store_icon_text'   => '',
                'store_name_desc'   => __('Eg: iTunes, SoundCloud, Buy Now', 'sonaar-music'),
                'store_link_desc'   => __('Link to the online store', 'sonaar-music'),
            
            ) 
        ));
        $cmb_album->add_field( array(
            'id'            => 'alb_store_list',
            'type'          => 'store_list',
            'name'          => __('External Links Buttons','sonaar-music'),
            'repeatable'    => true,
            'icon'          => true,
            'text'          => array(
                'add_row_text'      => 'Add Link',
            )
        ));

        $cmb_album_promo = new_cmb2_box( array(
            'id'            => 'sonaar_promo',
            'title'        	=> esc_html__( 'Why MP3 Player PRO?', 'sonaar-music' ),
            'object_types' 	=> array( 'album' ),
            'context'       => 'side',
            'priority'      => 'low',
            'show_names'    => false,
            'capability'    => 'manage_options', // Cap required to view options-page.
        ) );
        
        $cmb_album_promo->add_field( array(
            'id'            => 'calltoaction',
            'name'	        => __('sonaar pro', 'sonaar-music'),
            'type'          => 'calltoaction',
            'href'          => 'https://goo.gl/mVUJEJ',
            'img'           => 'https://assets.sonaar.io/marketing/sonaar-music-pro/sonaar-music-pro-banner-cpt.jpg'
        ));
    }
    
    /**
    * Create custom posttype
    **/
    public function srmp3_create_postType(){
        
        $album_args = array(
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'has_archive'         => true,
            'query_var'           => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_icon'           => 'dashicons-format-audio',
            'exclude_from_search' => false,
            'delete_with_user'    => false,
        );
        
        $album_args['labels'] = array(
            'name'               => esc_html__('Playlists', 'sonaar-music'),
            'singular_name'      => esc_html__('Playlist', 'sonaar-music'),
            'name_admin_bar'     => esc_html__('Playlist', 'add new on admin bar', 'sonaar-music'),
            'menu_name'          => esc_html__('MP3 Player', 'sonaar-music'),
            'all_items'          => esc_html__('All Playlists', 'sonaar-music'),
            'add_new'            => esc_html__('Add New Playlist', 'playlist', 'sonaar-music'),
            'add_new_item'       => esc_html__('Add New Playlist', 'sonaar-music'),
            'edit_item'          => esc_html__('Edit Playlist', 'sonaar-music'),
            'new_item'           => esc_html__('New Playlist', 'sonaar-music'),
            'view_item'          => esc_html__('View playlist', 'sonaar-music'),
            'search_items'       => esc_html__('Search Playlists', 'sonaar-music'),
            'not_found'          => esc_html__('No playlists found.', 'sonaar-music'),
            'not_found_in_trash' => esc_html__('No playlists found in the Trash.', 'sonaar-music'),
            'featured_image'     => esc_html__('Playlist Cover Image', 'sonaar-music'),
            'set_featured_image' => esc_html__('Set Playlist Cover', 'sonaar-music'),
            'remove_featured_image' => esc_html__('Remove Playlist Cover', 'sonaar-music')
        );
        
        $album_args['supports'] = array(
            'title',
            'editor',
            'excerpt',
            'author',
            'thumbnail'
        );     
        
        register_post_type( 'album' , $album_args);

        $album_category_args = array(
            'public'            => true,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => false,
            'show_admin_column' => true,
            'show_tagcloud'     => false,
            'query_var'         => false,
            'rewrite'           => true,
            'hierarchical'      => true,
            'sort'              => false,
            'labels'            => array(
                'name'          => esc_html_x('Playlist Categories',  'Taxonomy : name',          'sonaar-music'),
                'all_items'     => esc_html_x('All Categories',       'Taxonomy : all_items',     'sonaar-music'),
                'singular_name' => esc_html_x('Category',             'Taxonomy : singular_name', 'sonaar-music'),
                'add_new_item'  => esc_html_x('Add New Category',     'Taxonomy : add_new_item',  'sonaar-music'),
                'not_found'     => esc_html_x('No categories found.', 'Taxonomy : not_found',     'sonaar-music')
            ),
        );    
  
        register_taxonomy('playlist-category', 'album', $album_category_args);

        if ( function_exists('add_theme_support') ) {
            add_theme_support( 'post-thumbnails', array( 'album' ) );
        }

        flush_rewrite_rules(); 
    }
    
    public function register_widget(){
        register_widget( 'Sonaar_Music_Widget' );
    }
    

    public function srmp3_add_shortcode(){
    
        function sonaar_shortcode_audioplayer( $atts ) {
            
    		/* Enqueue Sonaar Music related CSS and Js file */
    		wp_enqueue_style( 'sonaar-music' );
    		wp_enqueue_style( 'sonaar-music-pro' );
    		wp_enqueue_script( 'sonaar-music-mp3player' );
    		wp_enqueue_script( 'sonaar-music-pro-mp3player' );
    		wp_enqueue_script( 'sonaar_player' );
    		
    		if ( function_exists('sonaar_player') ) {
    			add_action('wp_footer','sonaar_player', 12);
    		}
    		
            extract( shortcode_atts( array(
                'title' => '',
                'albums' => '',
                'show_playlist' => '',
                'hide_artwork' => '',
                'show_album_market' => '',
                'show_track_market' => '',
                'remove_player' => '',
                'enable_sticky_player' => '',
            ), $atts ) );
            
            ob_start();
            
            the_widget('Sonaar_Music_Widget', $atts, array('widget_id'=>'arbitrary-instance-'.uniqid(), 'before_widget'=>'<article class="widget iron_widget_radio '. (( function_exists('getCSSAnimation') )? getCSSAnimation( $css_animation ):'') .'">', 'after_widget'=>'</article>'));
                $output = ob_get_contents();
                ob_end_clean();
                
                return $output;
        }

        add_shortcode( 'sonaar_audioplayer', 'sonaar_shortcode_audioplayer' );

    }

    public function init_my_shortcode_button() {
        
        $button_slug = 'sonaar_audioplayer';
        
        $js_button_data = array(
            'qt_button_text' => __( 'MP3 Player Shortcode Generator', 'sonaar-music' ),
            'button_tooltip' => __( 'MP3 Player Shortcode Generator', 'sonaar-music' ),
            'icon'           => 'dashicons-format-audio',
            'author'         => 'Sonaar Music',
            'authorurl'      => 'https://sonaar.io',
            'infourl'        => 'https://sonaar.io',
            'version'        => '1.0.0',
            'include_close'  => true, // Will wrap your selection in the shortcode
            'mceView'        => false, // Live preview of shortcode in editor. YMMV.
            'l10ncancel'     => __( 'Cancel', 'sonaar-music' ),
            'l10ninsert'     => __( 'Insert Shortcode', 'sonaar-music' ),
        );
        
        $all_albums = get_posts(array(
            'post_type' => 'album',
            'posts_per_page' => -1,
            'no_found_rows'  => true
        ));

        $album_options = array();
        
        if( array_key_exists('post', $_GET) ){
            $sr_postypes = ( Sonaar_Music::get_option('srmp3_posttypes') != null ) ? Sonaar_Music::get_option('srmp3_posttypes') : array('album');
    		if( is_array( $sr_postypes ) ){
                if( in_array( get_post_type( $_GET['post'] ), $sr_postypes ) ){ 
                    $album_options[ $_GET['post'] ] = __( 'Current Post Tracklist', 'sonaar-music-pro' ); // Add this Option if the current post type is selected in the mp3 option. 
                }
            }
        }
        
        foreach ($all_albums as $album ) {
            $album_options[$album->ID] = $album->post_title;
        }
        
        $additional_args = array(
            // Can be a callback or metabox config array
            'cmb_metabox_config'   => array(
                'id'                    => 'shortcode_'. $button_slug,
                'fields'                => array(
                    array(
                        'name'              => __( 'Choose Playlist Type:', 'sonaar-music' ),
                        'id'                => 'playlist_type',
                        'type'              => 'select',
                        'show_option_none'  => true,
                        'options'           => array(
                            'predefined'        => __( 'Predefined Playlists', 'cmb2' ),
                            'feed'              => __( 'Audio URL inputs (advanced)', 'cmb2' ),
                        ),
                        'default'           => '',
                    ),
                    array(
                        'name'              => __( 'Choose Playlist(s)', 'sonaar-music' ),
                        'description'       => sprintf( esc_html__('To create new playlist, go to MP3 Player > Add New Playlist or %s', 'sonaar-music'), '<a href="' . esc_url(get_admin_url( null, 'post-new.php?post_type=album' )) . '" target="_blank">click here</a>'),
                        'id'                => 'albums',
                        'type'              => 'select_multiple',
                        'show_option_none'  => false,
                        'options'           => $album_options,
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'predefined',
                        ),
                    ),
                    array(
                        'name'              => __( 'Playlist Title', 'sonaar-music' ),
                        'id'                => 'playlist_title',
                        'type'              => 'text',
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'feed',
                        ),
                    ),
                    array(
                        'name'              => __( 'Playlist Cover Image', 'sonaar-music' ),
                        'id'                => 'artwork',
                        'type'              => 'file',
                        'text'              => array(
                            'add_upload_file_text' => 'Add Image' // Change upload button text. Default: "Add or Upload File"
                        ),
                        // query_args are passed to wp.media's library query.
                        'query_args'        => array(
                            // Or only allow gif, jpg, or png images
                            'type'  => array(
                                 'image/gif',
                                 'image/jpeg',
                                 'image/png',
                            ),
                        ),
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'feed',
                        ),
                    ),
                    array(
                        'name'              => __( 'Track MP3 URLs', 'sonaar-music' ),
                        'id'                => 'feed',
                        'description'       => __('eg: https://yourdomain.com/01.mp3 || https://yourdomain.com/02.mp3 . URL must be separated by || . See <a href="https://sonaar.ticksy.com/article/16450" target="_blank">this article</a> for supported streaming providers.', 'sonaar-music'),
                        'type'              => 'textarea_small',
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'feed',
                        ),
                    ),
                    array(
                        'name'              => __( 'Track Titles', 'sonaar-music' ),
                        'id'                => 'feed_title',
                        'description'       => __('eg: trackname 01 || trackname 02. Titles must be separated by ||', 'sonaar-music'),
                        'type'              => 'textarea_small',
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'feed',
                        ),
                    ),
                    array(
                        'name'              => __( 'Track Image URLs', 'sonaar-music' ),
                        'id'                => 'feed_img',
                        'description'       => __('eg: https://yourdomain.com/img01.jpg || https://yourdomain.com/img02.jpg . URL must be separated by ||', 'sonaar-music'),
                        'type'              => 'textarea_small',
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'feed',
                        ),
                    ),
                    array(
                        'name'              => __( 'Hide Cover Image', 'sonaar-music' ),
                        'id'                => 'hide_artwork',
                        'type'              => 'switch',
                        'label'             => array('off'=> 'Show', 'on'=> 'Hide'), //default On, Off
                        'default'           => false,
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'predefined',
                        ),
                    ),
                    array(
                        'name'              => __( 'Show Tracklist', 'sonaar-music' ),
                        'id'                => 'show_playlist',
                        'type'              => 'switch',
                        'label'             => array('on'=> 'Yes', 'off'=> 'No'), //default On, Off
                        'default'           => true,
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value'  => wp_json_encode( array( 'predefined', 'feed' ) ),
                        ),
                    ),
                    array(
                        'name'              => __( 'Show Track Store', 'sonaar-music' ),
                        'id'                => 'show_track_market',
                        'type'              => 'switch',
                        'label'             => array('on'=> 'Yes', 'off'=> 'No'), //default On, Off
                        'default'           => true,
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'predefined',
                        ),
                    ),
                    array(
                        'name'              => __( 'Show Album Store', 'sonaar-music' ),
                        'id'                => 'show_album_market',
                        'type'              => 'switch',
                        'label'             => array('on'=> 'Yes', 'off'=> 'No'), //default On, Off
                        'default'           => true,
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value' => 'predefined',
                        ),
                    ),
                    array(
                        'name'              => __( 'Remove Soundwave Spectrum', 'sonaar-music' ),
                        'id'                => 'hide_timeline',
                        'type'              => 'switch',
                        'label'             => array('on'=> 'Yes', 'off'=> 'No'), //default On, Off
                        'default'           => false,
                        'attributes'        => array(
                            'data-conditional-id'    => 'playlist_type',
                            'data-conditional-value'  => wp_json_encode( array( 'predefined', 'feed' ) ),
                        ),
                    ),
                ),

                'show_on'           => array( 'key' => 'options-page', 'value' => $button_slug ),

            ),

            // Set the conditions of the shortcode buttons
            'conditional_callback'  => 'shortcode_button_only_pages',
        );
    
        if ( function_exists( 'run_sonaar_music_pro' ) ){
            $proParameters = array(
                array(
                    'name'          => __( 'Enable Scrollbar on Tracklist', 'sonaar-music' ),
                    'id'            => 'scrollbar',
                    'type'          => 'switch',
                    'default'       => false,
                    'attributes'    => array(
                        'data-conditional-id'       => 'playlist_type',
                        'data-conditional-value'    => wp_json_encode( array( 'predefined', 'feed' ) ),
                    ),

                ),
                array(
                    'name'          => __( 'Enable Shuffle', 'sonaar-music' ),
                    'id'            => 'shuffle',
                    'type'          => 'switch',
                    'default'       => false,
                    'attributes'    => array(
                        'data-conditional-id'       => 'playlist_type',
                        'data-conditional-value'    => wp_json_encode( array( 'predefined', 'feed' ) ),
                    ),
                ),
                array(
                    'name'          => __( 'Enable Sticky Player', 'sonaar-music' ),
                    'id'            => 'sticky_player',
                    'label'         => array('on'=> 'Yes', 'off'=> 'No'), //default On, Off
                    'type'          => 'switch',
                    'default'       => true,
                    'attributes'    => array(
                        'data-conditional-id'       => 'playlist_type',
                        'data-conditional-value'    => wp_json_encode( array( 'predefined', 'feed' ) ),
                    ),
                ),
            );

            foreach ($proParameters as &$parameter) {
                array_push( $additional_args['cmb_metabox_config']['fields'], $parameter);
            }
        }

        $button = new Shortcode_Button( $button_slug, $js_button_data, $additional_args );
    }


    /**
    * Callback dictates that shortcode button will only display if we're on a 'page' edit screen
    *
    * @return bool Expects a boolean value
    */
    function shortcode_button_only_pages() {
        if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
            return false;
        }
        
        $current_screen = get_current_screen();
        
        if ( ! isset( $current_screen->parent_base ) || $current_screen->parent_base != 'edit' ) {
            return false;
        }
        
        if ( ! isset( $current_screen->post_type ) || $current_screen->post_type != 'page' ) {
            return false;
        }
        
        // Ok, guess we're on a 'page' edit screen
        return true;
    }



    public function manage_album_columns ($columns){
        $iron_cols = array(
            'alb_tracklist'     => esc_html('# Tracks'),
            'alb_store_list'    => esc_html('# Stores'),
            'alb_shortcode'     => esc_html('')
        );
        
        $columns = Sonaar_Music::array_insert($columns, $iron_cols, 'date', 'before');
        
        $iron_cols = array('alb_icon' => '');
        
        $columns = Sonaar_Music::array_insert($columns, $iron_cols, 'title', 'before');
        
        $columns['date'] = esc_html__('Published', 'sonaar-music');   // Renamed date column
        
        return $columns;
    }


    public function manage_album_custom_column ($column, $post_id){
        switch ($column){
            
            case 'alb_tracklist':
    			$list = get_post_meta($post_id, 'alb_tracklist', true);
                if ( !empty($list) )
                    echo count($list);
                else
                    echo esc_html__('N/A', 'sonaar-music');
                break;
            
            case 'alb_store_list':
    			$list = get_post_meta($post_id, 'alb_store_list', true);
                if ( !empty($list)  )
                    echo count($list);
                else
                    echo esc_html__('N/A', 'sonaar-music');
                break;
            
            case 'alb_shortcode':
                add_thickbox();
                echo '<div id="my-content-' . $post_id . '" style="display:none;">
                <h1>Playlist Shorcode</h1>
                <p>Here you can copy and paste the following shortcode anywhere your page</p>
                <textarea name="" id="" style="width:100%; height:150px;"> [sonaar_audioplayer title="' . get_the_title( $post_id ) . '" albums="' . $post_id . '" hide_artwork="false" show_playlist="true" show_track_market="true" show_album_market="true" hide_timeline="true"][/sonaar_audioplayer]</textarea>
                </div>';
                echo '<a href="#TB_inline?width=600&height=300&inlineId=my-content-' . $post_id . '" class="thickbox"><span class="dashicons dashicons-format-audio"></span></a>';
                break;
            case 'alb_icon':
                $att_title = _draft_or_post_title();
                
                echo '<a href="' . esc_url(get_edit_post_link( $post_id, true )) . '" title="' . esc_attr( sprintf( esc_html__('Edit &#8220;%s&#8221;', 'sonaar-music'), $att_title ) ) . '">';
                
                if ( $thumb = get_the_post_thumbnail( $post_id, array(64, 64) ) ){
                    echo $thumb;
            }else{
                echo '<img width="46" height="60" src="' . wp_mime_type_icon('image/jpeg') . '" alt="">';
            }
            
            echo '</a>';
            
            break;
        }
    }

    public function checkAlbumVersion(){
        $albums = get_posts( array(
            'post_type' => 'album',
            'post_status' => 'publish',
            'posts_per_page' => -1
    	));
    	foreach ( $albums as $album ) {
    		$oldVersion = ( get_post_meta($album->ID,'_alb_tracklist', true) !== '');

    		if ( $oldVersion ) {
                $meta = get_post_meta( $album->ID );
                $newList = array();

                for ($i=0; $i < $meta['alb_tracklist'][0] ; $i++) { 
                    
                    $newStructure = array(
                        'FileOrStream' =>  $meta['alb_tracklist_'. $i .'_FileOrStream'][0],
                        'track_mp3_id' =>  $meta['alb_tracklist_0_track_mp3'][0],
                        'track_mp3' =>  $meta['alb_tracklist_'. $i .'_track_mp3'][0],
                        'stream_link' =>  $meta['alb_tracklist_'. $i .'_stream_link'][0],
                        'stream_title' =>  $meta['alb_tracklist_'. $i .'_stream_title'][0],
                        'stream_artist' =>  $meta['alb_tracklist_'. $i .'_stream_artist'][0],
                        'stream_album' =>  $meta['alb_tracklist_'. $i .'_stream_album'][0],
                        'song_store_list' => array()
                    );

                    for ($a=0; $a < $meta['alb_tracklist_' . $i . '_song_store_list'][0] ; $a++) {
                        $newStructure['song_store_list'][$a] = array(
                            'store-icon'=> 'fab ' . $meta['alb_tracklist_' . $i . '_song_store_list_' . $a . '_song_store_icon'][0],
                            'store-name'=> $meta['alb_tracklist_' . $i . '_song_store_list_' . $a . '_song_store_name'][0],
                            'store-link'=> $meta['alb_tracklist_' . $i . '_song_store_list_' . $a . '_store_link'][0],
                            'store-target'=> $meta['alb_tracklist_' . $i . '_song_store_list_' . $a . '_song_store_target'][0],
                        );
                    }
                    $newList[$i] = $newStructure; 
                }
                    
                delete_post_meta( $album->ID, '_alb_tracklist' );
                update_post_meta( $album->ID, 'alb_tracklist', $newList );

            }
        }
    }

}