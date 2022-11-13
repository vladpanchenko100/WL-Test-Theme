<?php 

add_action("wp_enqueue_scripts", "wl_style_theme");
add_action( 'init', 'wl_create_taxonomies' );
add_action( 'init', 'wl_register_post_types' );
add_action('after_setup_theme', "wl_theme_setup");
add_action( 'customize_register', 'wl_theme_customize_register' );
add_action('genesis_site_description', 'add_custom_field');

function wl_theme_customize_register($wp_customize) {

    $wp_customize->add_panel( 'header_edits', array(
          'priority'       => 10,
          'theme_supports' => '',
          'title'          => __( 'Header edits', 'theme_name' ),
          'description'    => __( 'Set editable text for certain content in header.', 'theme_name' ),
     ) );

     $wp_customize->add_section( 'custom_title_text' , array(
          'title'    => __('Custom Text','theme-name'),
          'panel'    => 'header_edits',
          'priority' => 10
     ) );

     $wp_customize->add_setting( 'title_text_block', array(
          'default'           => __( '+00-000-000-00-00', 'theme-name' ),
          'sanitize_callback' => 'sanitize_text'
     ) );
 
     $wp_customize->add_control( new WP_Customize_Control(
          $wp_customize,
              'custom_title_text',
               array(
                    'label'    => __( 'Number', 'theme_name' ),
                    'section'  => 'custom_title_text',
                    'settings' => 'title_text_block',
                    'type'     => 'text'
               )
     )
 );


 function sanitize_text( $text ) {
     return sanitize_text_field( $text );
 }
          
}

function add_custom_field(){
     echo get_theme_mod( 'title_text_block');
 }

function wl_theme_setup() {
     add_theme_support("title-tag");
     add_theme_support("post-formats", ["aside", "gallery", "image"]);
     add_theme_support("customize_selective_refresh_widgets");
     add_theme_support( 'post-thumbnails' ); 
	set_post_thumbnail_size( 600, 600, true );
     add_theme_support("custom-logo", [
          "height" => 60,
          "weight" => 60,
          "flex-weight" => true,
          "flex-height" => true
     ]);
}

class Car_options {
	private $config = '{"title":"Car options","prefix":"car_options_","domain":"car-options","class_name":"Car-options","post-type":["post"],"context":"normal","priority":"default","cpt":"Car","fields":[{"type":"color","label":"Color","default":"#000000","color-picker":"1","id":"car_options_color"},{"type":"select","label":"Fuel","default":"option-one","options":"kerosene: Kerosene\r\nbiodiesel: Biodiesel\r\nsolar-oil: Solar Oil \r\nfuel oil: Fuel Oil ","id":"car_options_fuel"},{"type":"number","label":"KW Power","id":"car_options_kw-power"},{"type":"number","label":"Price ($)","id":"car_options_price"}]}';

	public function __construct() {
		$this->config = json_decode( $this->config, true );
		$this->process_cpts();
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'admin_head', [ $this, 'admin_head' ] );
		add_action( 'save_post', [ $this, 'save_post' ] );
	}

	public function process_cpts() {
		if ( !empty( $this->config['cpt'] ) ) {
			if ( empty( $this->config['post-type'] ) ) {
				$this->config['post-type'] = [];
			}
			$parts = explode( ',', $this->config['cpt'] );
			$parts = array_map( 'trim', $parts );
			$this->config['post-type'] = array_merge( $this->config['post-type'], $parts );
		}
	}

	public function add_meta_boxes() {
		foreach ( $this->config['post-type'] as $screen ) {
			add_meta_box(
				sanitize_title( $this->config['title'] ),
				$this->config['title'],
				[ $this, 'add_meta_box_callback' ],
				$screen,
				$this->config['context'],
				$this->config['priority']
			);
		}
	}

	public function admin_enqueue_scripts() {
		global $typenow;
		if ( in_array( $typenow, $this->config['post-type'] ) ) {
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	public function admin_head() {
		global $typenow;
		if ( in_array( $typenow, $this->config['post-type'] ) ) {
			?><script>
				jQuery.noConflict();
				(function($) {
					$(function() {
						$('.rwp-color-picker').wpColorPicker();
					});
				})(jQuery);
			</script><?php
		}
	}

	public function save_post( $post_id ) {
		foreach ( $this->config['fields'] as $field ) {
			switch ( $field['type'] ) {
				default:
					if ( isset( $_POST[ $field['id'] ] ) ) {
						$sanitized = sanitize_text_field( $_POST[ $field['id'] ] );
						update_post_meta( $post_id, $field['id'], $sanitized );
					}
			}
		}
	}

	public function add_meta_box_callback() {
		$this->fields_table();
	}

	private function fields_table() {
		?><table class="form-table" role="presentation">
			<tbody><?php
				foreach ( $this->config['fields'] as $field ) {
					?><tr>
						<th scope="row"><?php $this->label( $field ); ?></th>
						<td><?php $this->field( $field ); ?></td>
					</tr><?php
				}
			?></tbody>
		</table><?php
	}

	private function label( $field ) {
		switch ( $field['type'] ) {
			default:
				printf(
					'<label class="" for="%s">%s</label>',
					$field['id'], $field['label']
				);
		}
	}

	private function field( $field ) {
		switch ( $field['type'] ) {
			case 'number':
				$this->input_minmax( $field );
				break;
			case 'select':
				$this->select( $field );
				break;
			default:
				$this->input( $field );
		}
	}

	private function input( $field ) {
		if ( isset( $field['color-picker'] ) ) {
			$field['class'] = 'rwp-color-picker';
		}
		printf(
			'<input class="regular-text %s" id="%s" name="%s" %s type="%s" value="%s">',
			isset( $field['class'] ) ? $field['class'] : '',
			$field['id'], $field['id'],
			isset( $field['pattern'] ) ? "pattern='{$field['pattern']}'" : '',
			$field['type'],
			$this->value( $field )
		);
	}

	private function input_minmax( $field ) {
		printf(
			'<input class="regular-text" id="%s" %s %s name="%s" %s type="%s" value="%s">',
			$field['id'],
			isset( $field['max'] ) ? "max='{$field['max']}'" : '',
			isset( $field['min'] ) ? "min='{$field['min']}'" : '',
			$field['id'],
			isset( $field['step'] ) ? "step='{$field['step']}'" : '',
			$field['type'],
			$this->value( $field )
		);
	}

	private function select( $field ) {
		printf(
			'<select id="%s" name="%s">%s</select>',
			$field['id'], $field['id'],
			$this->select_options( $field )
		);
	}

	private function select_selected( $field, $current ) {
		$value = $this->value( $field );
		if ( $value === $current ) {
			return 'selected';
		}
		return '';
	}

	private function select_options( $field ) {
		$output = [];
		$options = explode( "\r\n", $field['options'] );
		$i = 0;
		foreach ( $options as $option ) {
			$pair = explode( ':', $option );
			$pair = array_map( 'trim', $pair );
			$output[] = sprintf(
				'<option %s value="%s"> %s</option>',
				$this->select_selected( $field, $pair[0] ),
				$pair[0], $pair[1]
			);
			$i++;
		}
		return implode( '<br>', $output );
	}

	private function value( $field ) {
		global $post;
		if ( metadata_exists( 'post', $post->ID, $field['id'] ) ) {
			$value = get_post_meta( $post->ID, $field['id'], true );
		} else if ( isset( $field['default'] ) ) {
			$value = $field['default'];
		} else {
			return '';
		}
		return str_replace( '\u0027', "'", $value );
	}

}
new Car_options;

function wl_create_taxonomies() {
     register_taxonomy( 'brand', [ 'Car' ], [
		'label'                 => '',
		'labels'                => [
			'name'              => 'brand',
			'singular_name'     => 'brand',
			'search_items'      => 'Search brand',
			'all_items'         => 'All brands',
			'view_item '        => 'View brand',
			'parent_item'       => 'Parent brand',
			'parent_item_colon' => 'Parent brand:',
			'edit_item'         => 'Edit brand',
			'update_item'       => 'Update brand',
			'add_new_item'      => 'Add New brand',
			'new_item_name'     => 'New brand name',
			'menu_name'         => 'Brand',
			'back_to_items'     => '← Back to Brand',
		],
		'description'           => 'brand of the car', 
		'public'                => true,
		'hierarchical'          => false,
		'rewrite'               => true,
	] );

     register_taxonomy( 'producing country', [ 'Car' ], [
		'label'                 => '',
		'labels'                => [
			'name'              => 'producing country',
			'singular_name'     => 'producing country',
			'search_items'      => 'Search producing country',
			'all_items'         => 'All producing countries',
			'view_item '        => 'View producing country',
			'parent_item'       => 'Parent producing country',
			'parent_item_colon' => 'Parent producing country:',
			'edit_item'         => 'Edit producing country',
			'update_item'       => 'Update producing country',
			'add_new_item'      => 'Add New producing country',
			'new_item_name'     => 'New producing country name',
			'menu_name'         => 'Producing country',
			'back_to_items'     => '← Back to producing country',
		],
		'description'           => 'Country, where car was manufactured', 
		'public'                => true,
		'hierarchical'          => false,
		'rewrite'               => true,
	] );
}

function wl_register_post_types(){

	register_post_type( 'Car', [
		'label'  => null,
		'labels' => [
			'name'               => 'Car', 
			'singular_name'      => 'Car', 
			'add_new'            => 'Add new car',
			'add_new_item'       => 'Adding car', 
			'edit_item'          => 'Edit car', 
			'new_item'           => 'New car',
			'view_item'          => 'View car', 
			'search_items'       => 'Search car', 
			'not_found'          => 'Not found',
			'not_found_in_trash' => 'Not found in trash',
			'parent_item_colon'  => '', 
			'menu_name'          => 'Car', 
		],
		'description'         => 'There are cars',
		'public'              => true,
		'publicly_queryable'  => true, 
		'exclude_from_search' => true, 
		'show_ui'             => true, 
		'show_in_nav_menus'   => true, 
		'show_in_menu'        => true, 
		'show_in_admin_bar'   => true,
		'show_in_rest'        => null, 
		'rest_base'           => null, 
		'menu_position'       => 4,
		'menu_icon'           => null,
		'hierarchical'        => false,
		'supports'            => [ 'title', 'editor', 'thumbnail', "custom-fields" ], // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
		'taxonomies'          => ["brand", "producing country"],
		'has_archive'         => false,
		'rewrite'             => true,
		'query_var'           => true,
	] );

}

function wl_style_theme() {
     wp_enqueue_style("style", get_stylesheet_uri());
     wp_enqueue_style("normalize", get_template_directory_uri() . "/assets/css/normalize.css");
     wp_enqueue_style("default", get_template_directory_uri() . "/assets/css/default.css");
     wp_enqueue_style("layout", get_template_directory_uri() . "/assets/css/layout.css");
     wp_enqueue_style("media-queries", get_template_directory_uri() . "/assets/css/media-queries.css");
}