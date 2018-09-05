<?php

namespace Svbk\WP\Helpers\Gallery;

class Flickity {

	public $width = 1320;
	public $height = 400;
	public $crop = false;
	public $name = 'post-slider';   // format to select in Admin Gallery Editor
	public $label;

	public static $_options = array(
		'cellSelector' => '.gallery-item',
		'imagesLoaded' => 'true',
		'contain' => true,
		'pageDots' => false,
		'prevNextButtons' => true,
	);

	public $options = array();

	static function register( $properties = array() ) {

		$class = get_called_class();

		return new $class($properties);
	}

	public function __construct( $properties = array() ) {
		foreach ( $properties as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->$property = $value;
			}
		}

		if ( ! $this->label ) {
			$this->label = __( 'Post Slider','svbk-helpers' );
		}

		if ( isset( $properties['options'] ) ) {
			$this->options = wp_parse_args( $properties['options'], self::$_options );
		} else {
			$this->options = self::$_options;
		}

		if ( ! has_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_script' ) ) ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_script' ), 12 );
		}
	}

	public function setDefault() {

		add_filter( 'gallery_style',  array( $this, 'post_gallery' ) );
		add_filter( 'image_size_names_choose', array( $this, 'custom_image_sizes' ) );

		add_image_size( $this->name, $this->width, $this->height, $this->crop );
	}

	public function post_gallery( $html ) {     // $html yet structured

		if ( strpos( $html, 'gallery-size-' . $this->name ) === false ) {   // if $html does NOT contain 'gallery-size-post-slider' => user has NOT selected that format
			return $html;   // exit (use Wordpress default gallery)
		}

		$html = str_replace( 'gallery ', 'gallery js-flickity ', $html );
		$html = str_replace( '<div ', '<div ' . $this->formatHtmlAttributes() . ' ', $html );

		return $html;
	}

	public static function formatHtmlAttributes( $options = array() ) {

		if ( ! isset( $this ) || empty( $this->options ) ) {
			$options = array_merge( self::$_options, $options );
		}

		return "data-flickity='" . esc_attr( json_encode( $options ) ) . "' ";
	}

	public static function gallery( $image_ids, $options = array() ) {
		return self::render($image_ids, 'medium', $options);
	}

	public static function render( $image_ids, $size = 'medium', $flickity_options = array(), $html_options = array() ) {

		$output = '';

		$attributes = array();

		$flickity_options = wp_parse_args( $flickity_options, array( 
				'cellSelector' => isset( $flickity_options['cellClass'] ) ? ( '.' .$flickity_options['cellClass']) : '.carousel-cell-image' ,
				'cellClass' => 'carousel-cell-image',
			)
		);

		$classes = array(
			'js-flickity'
		);

		if ( !empty($html_options['class']) ){
			$classes = array_merge( $classes, explode( ' ', $html_options['class'] ) );
			unset( $html_options['class'] );
		}

		foreach ( $html_options as $key => $value ) {
			$attributes[] = esc_html( $value ) . '="' . esc_attr( $value ) . "'";
		}

		$output .= '<div class="' . implode( ' ', $classes ) . '" ' . implode( ' ', $attributes ) . ' ' . self::formatHtmlAttributes($flickity_options) . '>';
		
		$image_index = 0;
		
		foreach ( $image_ids as $image_id ) {
			$image_index++;
			$image_src = wp_get_attachment_image_src( $image_id, $size );
			
			$output .= '<div class="' . esc_attr($flickity_options['cellClass']) . '">';
			
			if ( empty($flickity_options['lazyLoad']) /*|| $image_index <= $flickity_options['lazyLoad'] */ ) {
				$output .=  wp_get_attachment_image( $image_id, $size );
			} else {
				$output .= '<img
				  data-flickity-lazyload-srcset="' . wp_get_attachment_image_srcset( $image_id, $size ) . '"
				  sizes="' . wp_calculate_image_sizes($size, $image_src[0], null, $image_id) . '"
				  data-flickity-lazyload-src="' . $image_src[0] . '"
				  />';				
			}
			
			$output .= '</div>';
		}
		
		$output .= '</div>';

		return $output;
	}


	public function custom_image_sizes( $sizes ) {
		$custom_sizes = array(
			$this->name	=> $this->label,
		);
		return array_merge( $custom_sizes, $sizes );
	}

	static function enqueue_script() {
		wp_enqueue_script( 'flickity' );
		wp_enqueue_style( 'flickity' );
		wp_add_inline_script( 'flickity', '
            (function($){ 
                $(document.body).on( \'post-load\', function(){ 
                    $(\'.js-flickity\').not(\'.flickity-enabled\').each( function(){ 
                        $(this).flickity( $(this).data(\'flickityOptions\') );
                    });  
                });  
            })(jQuery);', 'after');
	}

}
