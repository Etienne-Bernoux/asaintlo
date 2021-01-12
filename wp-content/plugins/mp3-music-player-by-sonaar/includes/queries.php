<?php

/**
 * Get Artist
 */
    function sr_plugin_elementor_select_artist(){
        $sr_artist_list = get_posts(array(
            'post_type' => 'artist',
            'showposts' => 999,
        ));
        $options = array();

        if ( ! empty( $sr_artist_list ) && ! is_wp_error( $sr_artist_list ) ){
            foreach ( $sr_artist_list as $post ) {
                $options[ $post->ID ] = $post->post_title;
            }
        } else {
            $options[0] = esc_html__( 'Create an Artist First', 'sonaar-music' );
        }
        return $options;
    }

/**
 * Get Music Playlist
 */

    function sr_plugin_elementor_select_playlist(){
        $sr_playlist_list = get_posts(array(
            'post_type' => 'album',
            'showposts' => 999,
        ));
        $options = array();

        if ( ! empty( $sr_playlist_list ) && ! is_wp_error( $sr_playlist_list ) ){
            foreach ( $sr_playlist_list as $post ) {
                $options[ $post->ID ] = $post->post_title;
            }
        } else {
            $options[0] = esc_html__( 'Create a Playlist First', 'sonaar-music' );
        }
        return $options;
    }

/**
 * Get Latest Published Post
 */
    function sr_plugin_elementor_getLatestPost($posttype){
        $arg = wp_get_recent_posts(array('post_type'=>$posttype, 'post_status' => 'publish', 'numberposts' => 1));
        if (!empty($arg)){
            return $arg[0]["ID"];
        }
    }