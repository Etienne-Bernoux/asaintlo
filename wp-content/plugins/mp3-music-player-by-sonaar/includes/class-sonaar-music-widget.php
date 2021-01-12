<?php
/**
* Radio Widget Class
*
* @since 1.6.0
* @todo  - Add options
*/

class Sonaar_Music_Widget extends WP_Widget{
    /**
    * Widget Defaults
    */
    
    public static $widget_defaults;
    
    /**
    * Register widget with WordPress.
    */
    
    function __construct (){
        
        
        $widget_ops = array(
        'classname'   => 'sonaar_music_widget',
        'description' => esc_html_x('A simple radio that plays a list of songs from selected albums.', 'Widget', 'sonaar-music')
        );
        
        self::$widget_defaults = array(
            'title'        => '',
            'store_title_text' => '',
            'albums'     	 => array(),
            'show_playlist' => 0,
            'hide_artwork' => 0,
            'sticky_player' => 0,
            'show_album_market' => 0,
            'show_track_market' => 0,
            //'remove_player' => 0, // deprecated and replaced by hide_timeline
            'hide_timeline' =>0,
            
            
            );
            
            if ( isset($_GET['load']) && $_GET['load'] == 'playlist.json' ) {
                $this->print_playlist_json();
        }
        
        parent::__construct('sonaar-music', esc_html_x('Sonaar: Music Player', 'Widget', 'sonaar-music'), $widget_ops);
        
    }
        
    /**
    * Front-end display of widget.
    */
    public function widget ( $args, $instance ){
        $instance = wp_parse_args( (array) $instance, self::$widget_defaults );
            $elementor_widget = (bool)( isset( $instance['hide_artwork'] ) )? true: false; //Return true if the widget is set in the elementor editor 
            $args['before_title'] = "<span class='heading-t3'></span>".$args['before_title'];
            $args['before_title'] = str_replace('h2','h3',$args['before_title']);
            $args['after_title'] = str_replace('h2','h3',$args['after_title']);
            /*$args['after_title'] = $args['after_title']."<span class='heading-b3'></span>";*/
            //if ( function_exists( 'run_sonaar_music_pro' ) ){
            $feed = ( isset( $instance['feed'] ) )? $instance['feed']: '';
            $feed_title =  ( isset( $instance['feed_title'] ) )? $instance['feed_title']: '';
            $feed_img =  ( isset( $instance['feed_img'] ) )? $instance['feed_img']: '';
            $el_widget_id = ( isset( $instance['el_widget_id'] ) )? $instance['el_widget_id']: '';
            $playlatestalbum = ( isset( $instance['play-latest'] ) ) ? true : false;
            $title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
            $albums = $instance['albums'];

            if($playlatestalbum){
                $recent_posts = wp_get_recent_posts(array('post_type'=>'album', 'post_status' => 'publish', 'numberposts' => 1));
                if (!empty($recent_posts)){
                    $albums = $recent_posts[0]["ID"];
                }

            }

            if( empty($albums) ) {
                // SHORTCODE IS DISPLAYED BUT NO ALBUMS ID ARE SET. EITHER GET INFO FROM CURRENT POST OR RETURN NO PLAYLIST SELECTED
                $trackSet = '';
                $albums = get_the_ID();
                $album_tracks =  get_post_meta( $albums, 'alb_tracklist', true);

                if (is_array($album_tracks)){
                    $fileOrStream =  $album_tracks[0]['FileOrStream'];
                       
                    switch ($fileOrStream) {
                        case 'mp3':
                            if ( isset( $album_tracks[0]["track_mp3"] ) ) {
                                $trackSet = true;
                            }
                            break;

                        case 'stream':
                            if ( isset( $album_tracks[0]["stream_link"] ) ) {
                                $trackSet = true;
                            }
                            break;
                    }
                }
                if (isset($feed) && strlen($feed) > 1 ){
                     $trackSet = true;

                }
                if ( ($album_tracks == 0 || !$trackSet) && (!isset($feed) && strlen($feed) < 1 )){
                    echo esc_html__("No playlist selected", 'sonaar-music');
                    return;
                }
                if (!$feed && !$trackSet){
                    echo esc_html__("No playlist selected", 'sonaar-music');
                    return;
                }
            }
            $scrollbar = ( isset( $instance['scrollbar'] ) )? $instance['scrollbar']: false;
            $show_album_market = (bool) ( isset( $instance['show_album_market'] ) )? $instance['show_album_market']: 0;
            $show_track_market = (bool) ( isset( $instance['show_track_market'] ) )? $instance['show_track_market']: 0;
            $store_title_text = $instance['store_title_text'];
            $hide_artwork= (bool)( isset( $instance['hide_artwork'] ) )? $instance['hide_artwork']: false; 
            $artwork= (bool)( isset( $instance['artwork'] ) )? $instance['artwork']: false;
            $remove_player = (bool) ( isset( $instance['remove_player'] ) )? $instance['remove_player']: false; // deprecated and replaced by hide_timeline. keep it for fallbacks
            $hide_timeline = (bool) ( isset( $instance['hide_timeline'] ) )? $instance['hide_timeline']: false;
            $notrackskip = get_post_meta($albums, 'no_track_skip', true);
            $sticky_player = (bool)( isset( $instance['sticky_player'] ) )? $instance['sticky_player']: false;
            $shuffle = (bool)( isset( $instance['shuffle'] ) )? $instance['shuffle']: false;
            $wave_color = (bool)( isset( $instance['wave_color'] ) )? $instance['wave_color']: false;
            $wave_progress_color = (bool)( isset( $instance['wave_progress_color'] ) )? $instance['wave_progress_color']: false;
            $show_playlist = (bool)( isset( $instance['show_playlist'] ) )? $instance['show_playlist']: false;
            $title_html_tag_playlist = ( isset( $instance['titletag_playlist'] ) )? $instance['titletag_playlist']: 'h3';
            $title_html_tag_soundwave = ( isset( $instance['titletag_soundwave'] ) )? $instance['titletag_soundwave']: 'div';
            $title_html_tag_playlist = ($title_html_tag_playlist == '') ? 'div' : $title_html_tag_playlist;
            $title_html_tag_soundwave = ($title_html_tag_soundwave == '') ? 'div' : $title_html_tag_soundwave;
            $playlist_title = ( isset( $instance['playlist_title'] ) )? $instance['playlist_title']: false;
            
            if($sticky_player){
                $sticky_player = ($instance['sticky_player']=="true" || $instance['sticky_player']==1) ? : false;      
            }

            if($show_playlist){
                $show_playlist = ($instance['show_playlist']=="true" || $instance['show_playlist']==1) ? : false;      
            }
        
            if($show_track_market){
                $show_track_market = ($instance['show_track_market']=="true" || $instance['show_track_market']==1) ? : false;      
            }
            if($show_album_market){
                $show_album_market = ($instance['show_album_market']=="true" || $instance['show_album_market']==1) ? : false;      
            }
            if($hide_artwork){
                $hide_artwork = ($instance['hide_artwork']=="true" || $instance['hide_artwork']==1) ? : false;      
            }
            
            if($remove_player){
                $remove_player = ($instance['remove_player']=="true" || $instance['remove_player']==1) ? true : false;      
            }

            if($hide_timeline){
                $hide_timeline = ($instance['hide_timeline']=="true" || $instance['hide_timeline']==1) ? true : false;      
            }
            $hide_timeline_style = ( $remove_player || $hide_timeline )? 'style="display: none;"': '' ;

            $store_buttons = array();

            $playlist = $this->get_playlist($albums, $title, $feed_title, $feed, $feed_img, $el_widget_id, $artwork);

            if (isset($playlist['tracks'][0]['poster']) =="" || !$playlist['tracks'][0]['poster'] && !$artwork ){
                $hide_artwork = true;
            }

            if ( isset($playlist['tracks']) && ! empty($playlist['tracks']) )
                $player_message = esc_html_x('Loading tracks...', 'Widget', 'sonaar-music');
            else
                $player_message = esc_html_x('No tracks founds...', 'Widget', 'sonaar-music');
            
            /***/
            
            if ( ! $playlist )
                return;
            
            if($show_playlist) {
                $args['before_widget'] = str_replace("iron_widget_radio", "iron_widget_radio playlist_enabled", $args['before_widget']);
            }
        
		/* Enqueue Sonaar Music related CSS and Js file */
		wp_enqueue_style( 'sonaar-music' );
		wp_enqueue_style( 'sonaar-music-pro' );
		wp_enqueue_script( 'sonaar-music-mp3player' );
		wp_enqueue_script( 'sonaar-music-pro-mp3player' );
		wp_enqueue_script( 'sonaar_player' );
		if ( function_exists('sonaar_player') ) {
			add_action('wp_footer','sonaar_player', 12);
		}
		
        echo $args['before_widget'];
        
        if ( ! empty( $title ) )
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
		
		if ( is_array($albums)) {
			$albums = implode(',', $albums);
		}
	   
        if ( FALSE === get_post_status( $albums ) || get_post_status ( $albums ) == 'trash') {
            echo esc_html__('No playlist selected. Please select a playlist', 'sonaar-music');
            return;
        }
    
        $firstAlbum = explode(',', $albums);
        $firstAlbum = $firstAlbum[0];

        $playlist_title = ( isset( $instance['playlist_title'] ) )? $instance['playlist_title']: false;

        $classShowPlaylist = '';
        $classShowArtwork = '';
       if($show_playlist) {
            $classShowPlaylist = 'show-playlist';   
        }
        if($hide_artwork=="true") {
          $classShowArtwork = 'sonaar-no-artwork';
        }
        
        $show_market = ( $show_album_market )? $albums : 0 ;
        
        $format_playlist ='';

        if(Sonaar_Music::get_option('show_artist_name') ){
            $artistSeparator = (Sonaar_Music::get_option('artist_separator') && Sonaar_Music::get_option('artist_separator') != '' && Sonaar_Music::get_option('artist_separator') != 'by')?Sonaar_Music::get_option('artist_separator'): __('by', 'sonaar-music');
            $artistSeparator = ' ' . $artistSeparator . ' ';
        }else{
            $artistSeparator = '';
        }
        foreach( $playlist['tracks'] as $track){
            $trackUrl = $track['mp3'] ;
            $showLoading = $track['loading'] ;
            $song_store_list = '<span class="store-list">';

            if ( $show_track_market && is_array($track['song_store_list']) ){
                if ($track['has_song_store'] && isset($track['song_store_list'][0]['store-link'])){
                    $song_store_list .= '<div class="song-store-list-menu"><i class="fas fa-ellipsis-v"></i><div class="song-store-list-container">';
                    
                    foreach( $track['song_store_list'] as $store ){
                        if(isset($store['store-icon'])){
                            if(!isset($store['store-name'])){
                                $store['store-name']='';
                            }
                            
                            if(!isset($store['store-link'])){
                                $store['store-link']='#';
                            }

                            $download="";
                            if($store['store-icon'] == "fas fa-download"){
                                $download = ' download';
                            }

                            if(!isset($store['store-icon'])){
                                $store['store-icon']='';
                            }

                            if(!isset($store['store-target'])){
                                $store['store-target']='_blank';
                            }

                            $song_store_list .= '<a href="' . $store['store-link'] . '"' . $download . ' class="song-store" target="' . $store['store-target'] . '" title="' . $store['store-name'] . '"><i class="' . $store['store-icon'] . '"></i></a>';
                         }
                    }
                    $song_store_list .= '</div></div>';
                }
            }
           
            $song_store_list .= '</span>';
            
            $store_buttons = ( !empty($track["track_store"]) )? '<a class="button" target="_blank" href="'. esc_url( $track['track_store'] ) .'">'. esc_textarea( $track['track_buy_label'] ).'</a>' : '' ;
            $artistSeparator_string = ($track['track_artist'])?$artistSeparator:'';//remove separator if no track doesnt have artist
            $format_playlist .= '<li
            data-audiopath="' . esc_url( $trackUrl ) . '"
            data-showloading="' . $showLoading .'"
            data-albumTitle="' . esc_attr( $track['album_title'] ) . '"
            data-albumArt="' . esc_url( $track['poster'] ) . '"
            data-releasedate="' . esc_attr( $track['release_date'] ) . '"
            data-trackTitle="' . $track['track_title'] . $artistSeparator_string . $track['track_artist'] . '"
            data-trackID="' . $track['id'] . '"
            data-trackTime="' . $track['lenght'] . '"
            data-notrackskip="' . $track['no_track_skip'] . '"
            >' . $song_store_list . '</li>';
        }

        if( Sonaar_Music::get_option('waveformType') === 'wavesurfer' ) {
            $fakeWave = '';
        }else{
            $fakeWave = '<div class="sonaar_fake_wave">
            <audio src="" class="sonaar_media_element"></audio>
            <div class="sonaar_wave_base"><svg></svg></div>
            <div class="sonaar_wave_cut"><svg></svg></div>
            </div>';
        }
        $feedurl = ($feed) ? '1' : '0';
        echo
        '<div class="iron-audioplayer ' . $classShowPlaylist . ' ' . $classShowArtwork . '" id="'. esc_attr( $args["widget_id"] ) .'-' . bin2hex(random_bytes(5)) . '" data-albums="'. $albums .'"data-url-playlist="' . esc_url(home_url('?load=playlist.json&amp;title='.$title.'&amp;albums='.$albums.'&amp;feed_title='.$feed_title.'&amp;feed='.$feed.'&amp;feed_img='.$feed_img.'&amp;el_widget_id='.$el_widget_id.'&amp;artwork='.$artwork.'')) . '" data-sticky-player="'. $sticky_player . '" data-shuffle="'. $shuffle . '" data-playlist_title="'. $playlist_title . '" data-scrollbar="'. $scrollbar . '" data-wave-color="'. $wave_color .'" data-wave-progress-color="'. $wave_progress_color . '" data-no-wave="'. $hide_timeline . '" data-feedurl="'. $feedurl .'" style="opacity:0;">
            
            <div class="sonaar-grid sonaar-grid-2 sonaar-grid-fullwidth-mobile">
                '.(!$hide_artwork || $hide_artwork!="true" ?
                    '<div class="sonaar-Artwort-box">
                        <div class="album">
                            <div class="album-art">
                                <img alt="album-art">
                            </div>
                        </div>
                    </div>'
                : '').'
                <div class="playlist">
                    <'.$title_html_tag_playlist.' class="sr_it-playlist-title">'. $playlist_title .'</'.$title_html_tag_playlist.'>
                    <div class="sr_it-playlist-release-date"><span class="sr_it-date-value">'. ( ( get_post_meta( $firstAlbum, 'alb_release_date', true ) )? get_post_meta($firstAlbum, 'alb_release_date', true ): '' ) . '</span></div>
                    <ul>' . $format_playlist . '</ul>
                </div>
            </div>
            <div class="album-player" ' . $hide_timeline_style .'>
                <'.$title_html_tag_soundwave.' class="track-title"></'.$title_html_tag_soundwave.'>
                <div class="album-title"></div>
                
                <div class="player">
                    <div class="currentTime"></div>
                    <div id="'.esc_attr($args["widget_id"]). '-' . bin2hex(random_bytes(5)) . '-wave" class="wave">
                    ' . $fakeWave . ' 
                    </div>
                    <div class="totalTime"></div>
                    <div class="control">
                        <a class="previous" style="opacity:0;">
                            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 10.2 11.7" style="enable-background:new 0 0 10.2 11.7;" xml:space="preserve">
                            <polygon points="10.2,0 1.4,5.3 1.4,0 0,0 0,11.7 1.4,11.7 1.4,6.2 10.2,11.7"/>
                            </svg>
                        </a>
                        <a class="play" style="opacity:0;">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 17.5 21.2" style="enable-background:new 0 0 17.5 21.2;" xml:space="preserve">
                            <path d="M0,0l17.5,10.9L0,21.2V0z"/>
                            
                            <rect width="6" height="21.2"/>
                            <rect x="11.5" width="6" height="21.2"/>
                            </svg>
                        </a>
                        <a class="next" style="opacity:0;">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 10.2 11.7" style="enable-background:new 0 0 10.2 11.7;" xml:space="preserve">
                            <polygon points="0,11.7 8.8,6.4 8.8,11.7 10.2,11.7 10.2,0 8.8,0 8.8,5.6 0,0"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="album-store">' . $this->get_market( $show_market, $store_title_text, $feedurl, $el_widget_id ) . '</div>
        </div>';
        
        
        //Temp. removed: Not required
        // echo $action;
        echo $args['after_widget'];
    }
    

    private function get_market($album_id = 0, $store_title_text, $feedurl = 0, $el_widget_id = 0){

        if( $album_id == 0 && !$feedurl)
        return;

        if (!$feedurl){ // source if from albumid
            $firstAlbum = explode(',', $album_id);
            $firstAlbum = $firstAlbum[0];
            $storeList = get_post_meta($firstAlbum, 'alb_store_list', true);

        } else if($feedurl = 1) { // source if from elementor widget
            if ($el_widget_id == 0)
            return;

            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                //__A. WE ARE IN EDITOR SO USE CURRENT POST META SOURCE TO UPDATE THE WIDGET LIVE OTHERWISE IT WONT UPDATE WITH LIVE DATA
                $storeList =  get_post_meta( $album_id, 'alb_store_list', true);
                if($storeList == ''){
                    return;
                }   
            }else{
                //__B. WE ARE IN FRONT-END SO USE SAVED POST META SOURCE
                $elementorData = get_post_meta( $album_id, '_elementor_data', true);
                $elementorData = json_decode($elementorData, true);
                $id = $el_widget_id;
                $results=[];

                if($elementorData){
                   $this->findData( $elementorData, $id, $results );
                   $storeList = (!empty($results['settings']['storelist_repeater'])) ? $results['settings']['storelist_repeater'] : '';
                }else{
                    return;
                } 
            }
        }

        if ( $storeList ){
            $output = '<div class="buttons-block"><div class="ctnButton-block">
            <div class="available-now">';
            if($store_title_text == NULL){
              $output .=  esc_html__("Available now on:", 'sonaar-music');
            }else{
              $output .=  esc_html__($store_title_text);
            }
            $output .=  '</div><ul class="store-list">';
            if ($feedurl){
                foreach ($storeList as $store ) {
                    if(!isset($store['store_name'])){
                        $store['store_name']="";
                    }
                    if(!isset($store['store_link'])){
                        $store['store_link']="";
                    }

                    if(array_key_exists ( 'store_icon' , $store )){
                        $icon = ( $store['store_icon']['value'] )? '<i class="' . $store['store_icon']['value'] . '"></i>': '';
                    }else{
                        $icon ='';
                    }
                    $output .= '<li><a class="button" href="' . esc_url( $store['store_link'] ) . '" target="_blank">'. $icon . $store['store_name'] . '</a></li>';
                }
            }else{
                foreach ($storeList as $store ) {
                    if(!isset($store['store-name'])){
                        $store['store-name']="";
                    }
                    if(!isset($store['store-link'])){
                        $store['store-link']="";
                    }

                    if(array_key_exists ( 'store-icon' , $store )){
                        $icon = ( $store['store-icon'] )? '<i class="' . $store['store-icon'] . '"></i>': '';
                    }else{
                        $icon ='';
                    }
                    $output .= '<li><a class="button" href="' . esc_url( $store['store-link'] ) . '" target="_blank">'. $icon . $store['store-name'] . '</a></li>';
                }
            }
            $output .= '</ul></div></div>';
            
            return $output;
        }
        
       
    }

    /**
    * Back-end widget form.
    */
    
    public function form ( $instance ){
        $instance = wp_parse_args( (array) $instance, self::$widget_defaults );
            
            $title = esc_attr( $instance['title'] );
            $albums = $instance['albums'];
            $show_playlist = (bool)$instance['show_playlist'];
            $sticky_player = (bool)$instance['sticky_player'];
            $hide_artwork = (bool)$instance['hide_artwork'];
            $show_album_market = (bool)$instance['show_album_market'];
            $show_track_market = (bool)$instance['show_track_market'];
            //$remove_player = (bool)$instance['remove_player']; // deprecated and replaced by hide_timeline
            $hide_timeline = (bool)$instance['hide_timeline'];
            
            $all_albums = get_posts(array(
            'post_type' => 'album'
            , 'posts_per_page' => -1
            , 'no_found_rows'  => true
            ));
            
            if ( !empty( $all_albums ) ) :?>

  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">
      <?php _ex('Title:', 'Widget', 'sonaar-music'); ?>
    </label>
    <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" placeholder="<?php _e('Popular Songs', 'sonaar-music'); ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('albums'); ?>">
      <?php _ex('Album:', 'Widget', 'sonaar-music'); ?>
    </label>
    <select class="widefat" id="<?php echo $this->get_field_id('albums'); ?>" name="<?php echo $this->get_field_name('albums'); ?>[]" multiple="multiple">
      <?php foreach($all_albums as $a): ?>

        <option value="<?php echo $a->ID; ?>" <?php echo ( is_array($albums) && in_array($a->ID, $albums) ? ' selected="selected"' : ''); ?>>
          <?php echo esc_attr($a->post_title); ?>
        </option>

        <?php endforeach; ?>
    </select>
  </p>
<?php if ( function_exists( 'run_sonaar_music_pro' ) ): ?>
  <p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('sticky_player'); ?>" name="<?php echo $this->get_field_name('sticky_player'); ?>" <?php checked( $sticky_player ); ?> />
    <label for="<?php echo $this->get_field_id('sticky_player'); ?>">
      <?php _e( 'Enable Sticky Audio Player', 'sonaar-music'); ?>
    </label>
    <br />
  </p>
<?php endif ?>
  <p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_playlist'); ?>" name="<?php echo $this->get_field_name('show_playlist'); ?>" <?php checked( $show_playlist ); ?> />
    <label for="<?php echo $this->get_field_id('show_playlist'); ?>">
      <?php _e( 'Show Playlist', 'sonaar-music'); ?>
    </label>
    <br />
  </p>

  <p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_album_market'); ?>" name="<?php echo $this->get_field_name('show_album_market'); ?>" <?php checked( $show_album_market ); ?> />
    <label for="<?php echo $this->get_field_id('show_album_market'); ?>">
      <?php _e( 'Show Album store', 'sonaar-music'); ?>
    </label>
    <br />
  </p>
  <p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_artwork'); ?>" name="<?php echo $this->get_field_name('hide_artwork'); ?>" <?php checked( $hide_artwork ); ?> />
    <label for="<?php echo $this->get_field_id('hide_artwork'); ?>">
      <?php _e( 'Hide Album Cover', 'sonaar-music'); ?>
    </label>
    <br />
  </p>
  <p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_track_market'); ?>" name="<?php echo $this->get_field_name('show_track_market'); ?>" <?php checked( $show_track_market ); ?> />
    <label for="<?php echo $this->get_field_id('show_track_market'); ?>">
      <?php _e( 'Show Track store', 'sonaar-music'); ?>
    </label>
    <br />
  </p>
  </p>
  <p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_timeline'); ?>" name="<?php echo $this->get_field_name('hide_timeline'); ?>" <?php checked( $hide_timeline ); ?> />
    <label for="<?php echo $this->get_field_id('hide_timeline'); ?>">
      <?php _e( 'Remove Visual Timeline', 'sonaar-music'); ?>
    </label>
    <br />
  </p>

  <?php
            else:
                
            echo wp_kses_post( '<p>'. sprintf( _x('No albums have been created yet. <a href="%s">Create some</a>.', 'Widget', 'sonaar-music'), esc_url(admin_url('edit.php?post_type=album')) ) .'</p>' );
            
            endif;
    }
    
    
    
    
    
    
    /**
    * Sanitize widget form values as they are saved.
    */
    
    public function update ( $new_instance, $old_instance )
    {
        $instance = wp_parse_args( $old_instance, self::$widget_defaults );
            
            $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
            $instance['albums'] = $new_instance['albums'];
            $instance['show_playlist']  = (bool)$new_instance['show_playlist'];
            $instance['hide_artwork']  = (bool)$new_instance['hide_artwork'];
            $instance['sticky_player']  = (bool)$new_instance['sticky_player'];
            $instance['show_album_market']  = (bool)$new_instance['show_album_market'];
            $instance['show_track_market']  = (bool)$new_instance['show_track_market'];
            //$instance['remove_player']  = (bool)$new_instance['remove_player']; deprecated and replaced by hide_timeline
            $instance['hide_timeline']  = (bool)$new_instance['hide_timeline'];
            
            return $instance;
    }
    
    
    private function print_playlist_json() {
        $jsonData = array();
        
        $title = !empty($_GET["title"]) ? $_GET["title"] : null;
        $albums = !empty($_GET["albums"]) ? $_GET["albums"] : array();
        $feed_title = !empty($_GET["feed_title"]) ? $_GET["feed_title"] : null;
        $feed = !empty($_GET["feed"]) ? $_GET["feed"] : null;
        $feed_img = !empty($_GET["feed_img"]) ? $_GET["feed_img"] : null;
        $el_widget_id = !empty($_GET["el_widget_id"]) ? $_GET["el_widget_id"] : null;
        $artwork =  !empty($_GET["artwork"]) ? $_GET["artwork"] : null;
        $playlist = $this->get_playlist($albums, $title, $feed_title, $feed, $feed_img, $el_widget_id, $artwork);
        
        if(!is_array($playlist) || empty($playlist['tracks']))
        return;
        
        wp_send_json($playlist);
        
    }
    private function findData($arr, $id, &$results = []){
        foreach ($arr as $data) {           
            if ( is_array($data) ){
                if (array_key_exists('id', $data)) {
                    if($data['id'] == $id){
                        $results = $data;
                    }
                }
                $this->findData( $data, $id, $results);     
            }
        }
        return false ;
    }
    private function get_playlist($album_ids = array(), $title = null, $feed_title = null, $feed = null, $feed_img = null, $el_widget_id = null, $artwork = null) {
        
        global $post;
        $playlist = array();
        $tracks = array();
        $albums = '';

        if(!is_array($album_ids)) {
            $album_ids = explode(",", $album_ids);
        }
        $albums = get_posts(array(
            'numberposts' => -1,
            'post_type' => ( Sonaar_Music::get_option('srmp3_posttypes') != null ) ? Sonaar_Music::get_option('srmp3_posttypes') : 'album',//array('album', 'post', 'product'),
            'post__in' => $album_ids
        ));

        if(Sonaar_Music::get_option('show_artist_name') ){
            $artistSeparator = (Sonaar_Music::get_option('artist_separator') && Sonaar_Music::get_option('artist_separator') != '' && Sonaar_Music::get_option('artist_separator') != 'by' )?Sonaar_Music::get_option('artist_separator'): __('by', 'sonaar-music');
            $artistSeparator = ' ' . $artistSeparator . ' ';
        }else{
            $artistSeparator = '';
        }

        if( $feed == '1' ){
            //001. FEED = 1 MEANS ITS A FEED BUILT WITH ELEMENTOR AND USE TRACKS UPLOAD. IF A PREDEFINED PLAYLIST IS SET, GO TO 003. FEED = 1 VALUE IS SET IN THE SR-MUSIC-PLAYER.PHP

            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                //__A. WE ARE IN EDITOR SO USE CURRENT POST META SOURCE TO UPDATE THE WIDGET LIVE OTHERWISE IT WONT UPDATE WITH LIVE DATA
                $album_tracks =  get_post_meta( $album_ids[0], 'srmp3_elementor_tracks', true);
                if($album_tracks == ''){
                    return;
                }   
            }else{
                //__B. WE ARE IN FRONT-END SO USE SAVED POST META SOURCE
                $elementorData = get_post_meta( $album_ids[0], '_elementor_data', true);
                $elementorData = json_decode($elementorData, true);
                $id = $el_widget_id;
                $results=[];

                $this->findData( $elementorData, $id, $results );

                $album_tracks = $results['settings']['feed_repeater'];

                $artwork = ( isset($results['settings']['album_img']['id'] )) ? wp_get_attachment_image_src( $results['settings']['album_img']['id'], 'large' )[0] : '';
            }
        
            $num = 1;
            for($i = 0 ; $i < count($album_tracks) ; $i++) {

                $track_title = ( isset($album_tracks[$i]['feed_track_title'] )) ? $album_tracks[$i]['feed_track_title'] : false;
                $track_length = false;
                $album_title = false;

                if ( isset( $album_tracks[$i]['feed_track_img']['id'] ) && $album_tracks[$i]['feed_track_img']['id'] != ''){
                    $thumb_url = wp_get_attachment_image_src( $album_tracks[$i]['feed_track_img']['id'], 'large' )[0];
                }else{
                   $thumb_url = $artwork;
                }
                

                if( isset( $album_tracks[$i]['feed_source_file']['url'] ) ){
                    // TRACK SOURCE IS FROM MEDIA LIBRARY
                    $audioSrc = $album_tracks[$i]['feed_source_file']['url'];
                    $mp3_id = $album_tracks[$i]['feed_source_file']['id'];
                    $mp3_metadata = wp_get_attachment_metadata( $mp3_id );
                    $track_length = ( isset( $mp3_metadata['length_formatted'] ) && $mp3_metadata['length_formatted'] !== '' )? $mp3_metadata['length_formatted'] : false;
                    $album_title = ( isset( $mp3_metadata['album'] ) && $mp3_metadata['album'] !== '' )? $mp3_metadata['album'] : false;
                    $track_artist = ( isset( $mp3_metadata['artist'] ) && $mp3_metadata['artist'] !== '' && Sonaar_Music::get_option('show_artist_name') )? $mp3_metadata['artist'] : false;
                    $track_title = ( isset( $mp3_metadata["title"] ) && $mp3_metadata["title"] !== '' )? $mp3_metadata["title"] : false ;
                    $track_title = ( get_the_title( $mp3_id ) !== '' && $track_title !== get_the_title( $mp3_id ) ) ? get_the_title( $mp3_id ) : $track_title;
                    $track_title = html_entity_decode( $track_title, ENT_COMPAT, 'UTF-8' );


                }else if( isset( $album_tracks[$i]['feed_source_external_url']['url'] ) ){
                     // TRACK SOURCE IS AN EXTERNAL LINK
                    $audioSrc = $album_tracks[$i]['feed_source_external_url']['url'];
                }else{
                    $audioSrc = '';
                }
                $showLoading = true;

                ////////
                
                $album_tracks[$i] = array();
                $album_tracks[$i]["id"] = '';
                $album_tracks[$i]["mp3"] = $audioSrc;
                $album_tracks[$i]["loading"] = $showLoading;
                $album_tracks[$i]["track_title"] = ( $track_title )? $track_title : "Track ". $num;
                $album_tracks[$i]["track_artist"] = ( isset( $track_artist ) && $track_artist != '' )? $track_artist : '';
                $album_tracks[$i]["lenght"] = $track_length;
                $album_tracks[$i]["album_title"] = ( $album_title )? $album_title : '';
                $album_tracks[$i]["poster"] = ( $thumb_url )? $thumb_url : null;
                $album_tracks[$i]["release_date"] = false;
                $album_tracks[$i]["song_store_list"] ='';
                $album_tracks[$i]["has_song_store"] = false;
                $album_tracks[$i]["no_track_skip"] = false;
                $num++;
            }
                $tracks = array_merge($tracks, $album_tracks);

        }else if ( $feed && $feed != '1'){
            // 002. FEED MEANS SOURCE IS USED DIRECTLY IN THE SHORTCODE ATTRIBUTE

            $thealbum = array();

            $feed_ar = explode('||', $feed);
            $feed_title_ar = explode('||', $feed_title);
            $feed_img_ar = explode('||', $feed_img);

            $thealbum = [$feed_ar];
            
            foreach($thealbum as $a) {
                $album_tracks = $feed_ar;
                $num = 1;
                for($i = 0 ; $i < count($feed_ar) ; $i++) {
                    $track_title = ( isset( $feed_title_ar[$i] )) ? $feed_title_ar[$i] : false;

                    if ( isset($feed_img_ar[$i]) ){
                        $thumb_url = $feed_img_ar[$i];
                    }else{
                       $thumb_url = $artwork;
                    }
                    
                    ////////
                    $audioSrc = $feed_ar[$i];
                    $showLoading = true;
                    ////////
                    $album_tracks[$i] = array();
                    $album_tracks[$i]["id"] = '';
                    $album_tracks[$i]["mp3"] = $audioSrc;
                    $album_tracks[$i]["loading"] = $showLoading;
                    $album_tracks[$i]["track_title"] = ( $track_title )? $track_title : "Track ". $num;
                    $album_tracks[$i]["track_artist"] = ( isset( $track_artist ) && $track_artist != '' )? $track_artist : '';
                    $album_tracks[$i]["lenght"] = false;
                    $album_tracks[$i]["album_title"] = '';
                    $album_tracks[$i]["poster"] = ( $thumb_url )? $thumb_url : $artwork;
                    $album_tracks[$i]["release_date"] = false;
                    $album_tracks[$i]["song_store_list"] ='';
                    $album_tracks[$i]["has_song_store"] = false;
                    $album_tracks[$i]["no_track_skip"] = false;
                    $num++;
                }

                $tracks = array_merge($tracks, $album_tracks);
            }     
        } else {
            // 003. FEED SOURCE IS A POSTID -> ALB_TRACKLIST POST META

            foreach ( $albums as $a ) {
                $album_tracks =  get_post_meta( $a->ID, 'alb_tracklist', true);
        
                if ( get_post_meta( $a->ID, 'reverse_tracklist', true) ){
                    $album_tracks = array_reverse($album_tracks); //reverse tracklist order option
                }
                
                if ($album_tracks!=''){ 
                    for($i = 0 ; $i < count($album_tracks) ; $i++) {

                       
                        $fileOrStream =  $album_tracks[$i]['FileOrStream'];
                        $thumb_id = get_post_thumbnail_id($a->ID);
                        $thumb_url = ( $thumb_id )? wp_get_attachment_image_src($thumb_id, Sonaar_Music::get_option('music_player_coverSize'), true)[0] : false ;
                        if ($artwork){ //means artwork is set in the shortcode so prioritize this image instead of the the post featured image.
                            $thumb_url = $artwork;
                        }
                       
                        $track_title = false;
                        $album_title = false;
                        $mp3_id = false;
                        $audioSrc = '';
                        $song_store_list = isset($album_tracks[$i]["song_store_list"]) ? $album_tracks[$i]["song_store_list"] : '' ;
                        $has_song_store =false;
                        if (isset($song_store_list[0])){
                            $has_song_store = true; 
                        }
                        
                        $showLoading = false;
                        $track_length = false;

                        switch ($fileOrStream) {
                            case 'mp3':
                                
                                if ( isset( $album_tracks[$i]["track_mp3"] ) ) {
                                    $mp3_id = $album_tracks[$i]["track_mp3_id"];
                                    $mp3_metadata = wp_get_attachment_metadata( $mp3_id );
                                    $track_title = ( isset( $mp3_metadata["title"] ) && $mp3_metadata["title"] !== '' )? $mp3_metadata["title"] : false ;
                                    $track_title = ( get_the_title($mp3_id) !== '' && $track_title !== get_the_title($mp3_id))? get_the_title($mp3_id): $track_title;
                                    $track_title = html_entity_decode($track_title, ENT_COMPAT, 'UTF-8');
                                    $track_artist = ( isset( $mp3_metadata['artist'] ) && $mp3_metadata['artist'] !== '' && Sonaar_Music::get_option('show_artist_name') )? $mp3_metadata['artist'] : false;
                                    $album_title = ( isset( $mp3_metadata['album'] ) && $mp3_metadata['album'] !== '' )? $mp3_metadata['album'] : false;
                                    $track_length = ( isset( $mp3_metadata['length_formatted'] ) && $mp3_metadata['length_formatted'] !== '' )? $mp3_metadata['length_formatted'] : false;
                                    $audioSrc = wp_get_attachment_url($mp3_id);
                                    $showLoading = true;
                                }
                                break;

                            case 'stream':
                                
                                $audioSrc = ( $album_tracks[$i]["stream_link"] !== '' )? $album_tracks[$i]["stream_link"] : false;
                                $track_title = ( $album_tracks[$i]["stream_title"] !== '' )? $album_tracks[$i]["stream_title"] : false;
                                $album_title = ( isset ($album_tracks[$i]["stream_album"]) && $album_tracks[$i]["stream_album"] !== '' )? $album_tracks[$i]["stream_album"] : false;
                                $showLoading = true;
                                break;
                            
                            default:
                                $album_tracks[$i] = array();
                                break;
                        }
                
                        $num = 1;
                       
                        $album_tracks[$i] = array();
                        $album_tracks[$i]["id"] = ( $mp3_id )? $mp3_id : '' ;
                        $album_tracks[$i]["mp3"] = $audioSrc;
                        $album_tracks[$i]["loading"] = $showLoading;
                        $album_tracks[$i]["track_title"] = ( $track_title )? $track_title : "Track ". $num++;
                        $album_tracks[$i]["track_artist"] = ( isset( $track_artist ) && $track_artist != '' )? $track_artist : '';
                        $album_tracks[$i]["lenght"] = $track_length;
                        $album_tracks[$i]["album_title"] = ( $album_title )? $album_title : $a->post_title;
                        $album_tracks[$i]["poster"] = $thumb_url;
                        $album_tracks[$i]["release_date"] = get_post_meta($a->ID, 'alb_release_date', true);
                        $album_tracks[$i]["song_store_list"] = $song_store_list;
                        $album_tracks[$i]["has_song_store"] = $has_song_store;
                        $album_tracks[$i]["no_track_skip"] = get_post_meta($a->ID, 'no_track_skip', true);
                    }
            
                    $tracks = array_merge($tracks, $album_tracks);

                }

            }
        }
        $playlist['playlist_name'] = $title;
        if ( empty($playlist['playlist_name']) ) $playlist['playlist_name'] = "";

        $playlist['artist_separator'] = $artistSeparator;

        $playlist['tracks'] = $tracks;
        if ( empty($playlist['tracks']) ) $playlist['tracks'] = array();
        return $playlist;

    }

}