<?php

/**
 * Handles 'store list' custom field type.
 */
class CMB2_Render_Store_list_Field extends CMB2_Type_Base {


	
	/**
	 * List of stores. To translate, pass array of states in the 'state_list' field param.
	 *
	 * @var array
	 */
	protected static $store_icon = array (
		'fab fa-apple' => '&#xf179; iTunes',
		'fab fa-youtube' => '&#xf167; Video',									
		'fab fa-bandcamp' => '&#xf2d5; BandCamp',
		'fab fa-google-play' => '&#xf1a0; Google Play',
		'fab fa-spotify' => '&#xf1bc; Spotify',
		'fab fa-soundcloud' => '&#xf1be; SoundCloud',
		'fab fa-amazon' => '&#xf270; Amazon',
		'fab fa-patreon' => '&#xf3d9; Patreon',
		'fas fa-download' => '&#xf019; Download',
		'fas fa-shopping-cart' => '&#xf07a; Buy',
	);
	protected static $store_target = array (
		'' => 'Yes',
		'_self' => 'No',									
	);
	public static function init() {
		add_filter( 'cmb2_render_class_store_list', array( __CLASS__, 'class_name' ) );
		add_filter( 'cmb2_sanitize_store_list', array( __CLASS__, 'maybe_save_split_values' ), 12, 4 );
		/**
		 * The following snippets are required for allowing the store_list field
		 * to work as a repeatable field, or in a repeatable group
		 */
		add_filter( 'cmb2_sanitize_store_list', array( __CLASS__, 'sanitize' ), 10, 5 );
		add_filter( 'cmb2_types_esc_store_list', array( __CLASS__, 'escape' ), 10, 4 );
	}

	public static function class_name() { return __CLASS__; }

	/**
	 * Handles outputting the address field.
	 */
	public function render() {
		self::setup_scripts();
		// make sure we assign each part of the value we need.
		$value = wp_parse_args( $this->field->escaped_value(), array(
			'store-icon' => '',
			'store-name' => '',
			'store-link' => '',
			'store-target' => ''
		) );

		if ( $this->field->args( 'icon' ) ) {
			$store_icon = $this->field->args( 'store_icon', array() );
			
			if ( empty( $store_icon ) ) {
				$store_icon = self::$store_icon;
			}
			
			$store_icon = array( '' => esc_html( $this->_text( 'store_select_store_icon_text', 'Select a link Icon' ) ) ) + $store_icon;

			$store_icon_options = '';
			foreach ( $store_icon as $icon => $store ) {
				$store_icon_options .= '<option class="' . $icon . '" value="'. $icon .'" '. selected( $value['store-icon'], $icon, false ) .'>'. $store .'</option>';
			}
		}

		$store_target = $this->field->args( 'store_target', array() );
		if ( empty( $store_target ) ) {
			$store_target = self::$store_target;
		}
		$store_target_options = '';
		foreach ( $store_target as $target => $targetstore ) {
			$store_target_options .= '<option class="' . $target . '" value="'. $target .'" '. selected( $value['store-target'], $target, false ) .'>'. $targetstore .'</option>';
		}


		ob_start();
		// Do html
		?>
		<?php if( $this->field->args( 'icon' ) ) :?>
		<div class="store-icon" style="float:left;"><p><label for="<?php echo $this->_id( '_store_icon' ); ?>"><?php echo esc_html( $this->_text( 'store_icon_text', 'Link Icon' ) ); ?></label></p>
			<?php echo $this->types->select( array(
				'name'  => $this->_name( '[store-icon]' ),
				'id'    => $this->_id( '_store_icon' ),
				'show_option_none' => true,
				'options' => $store_icon_options,
				'desc'  => '',
				'class' => 'fab fas'
			) ); ?>
		</div>
		<?php endif ?>

		<div class="store-name" style="clear:both;float:left;"><p><label for="<?php echo $this->_id( '_store_name' ); ?>"><?php echo esc_html( $this->_text( 'store_name_text', 'Link Label' ) ); ?></label></p>
			<?php echo $this->types->input( array(
				'name'  => $this->_name( '[store-name]' ),
				'id'    => $this->_id( '_store_name' ),
				'value' => $value['store-name'],
				'class' => 'cmb2-text-medium',
				'desc'  => '',
			) ); ?>
		<p class="cmb2-metabox-description"><?php echo esc_html( $this->_text( 'store_name_desc') ); ?></p>	
		</div>
		<div class="store-link" style="float:left;"><p><label for="<?php echo $this->_id( '_store_link'); ?>"><?php echo esc_html( $this->_text( 'store_link_text', 'Link URL' ) ); ?></label></p>
			<?php echo $this->types->input( array(
				'name'  => $this->_name( '[store-link]' ),
				'id'    => $this->_id( '_store_link' ),
				'value' => $value['store-link'],
				'type' => 'text',
				'class' => 'cmb2-text-url cmb2-text-medium regular-text',
				'desc'  => '',
				) ); ?>
				<p class="cmb2-metabox-description"><?php echo esc_html( $this->_text( 'store_link_desc') ); ?></p>	
		</div>
		<div class="store-target" style="float:left;"><p><label for="<?php echo $this->_id( '_store_target'); ?>"><?php echo esc_html( $this->_text( 'store_target', 'Open in New Window?' ) ); ?></label></p>
			<?php echo $this->types->select( array(
				'name'  => $this->_name( '[store-target]' ),
				'id'    => $this->_id( '_store_target' ),
				'show_option_none' => true,
				'options' => $store_target_options,
				'desc'  => '',
				'class' => 'sr-select'
			) ); ?>
		</div>
		<p class="clear">
			<?php echo $this->_desc();?>
		</p>
		<?php

		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() );
	}

	public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}

		$store_keys = array( 'store-icon', 'store-name', 'store-link', 'store-target');

		foreach ( $store_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'store_'. $key, sanitize_text_field( $value[ $key ] ) );
			}
		}

		remove_filter( 'cmb2_sanitize_store_list', array( __CLASS__, 'sanitize' ), 10, 5 );

		// Tell CMB2 we already did the update

		return true;

	}

	public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {

		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
		}

		return array_filter($meta_value);
	}

	public static function escape( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
		}

		return array_filter($meta_value);
	}

	protected static function setup_scripts() {
    wp_enqueue_style( 'cmb2-store-list',  plugins_url( '/css/cmb2-store-list.css', __FILE__ ), array(), NULL );
  }

}