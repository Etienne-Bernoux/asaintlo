<?php


function cmb2_init_store_list_field() {
	require_once dirname( __FILE__ ) . '/class-cmb2-render-store-list-field.php';
	CMB2_Render_Store_list_Field::init();
}
add_action( 'cmb2_init', 'cmb2_init_store_list_field' );
