<?php

namespace Svbk\WP\Helpers\Gallery;

class Flickity {
    
    static function gallery($html){
        
    	$extra_data = array(
    		'data-flickity-options' => array(
                'cellSelector'=>'.gallery-item',
                'imagesLoaded'=>'true',
                'contain'=> true,
                'pageDots' => false,
                'prevNextButtons'=>true
    			)
    		);
    
    
    	foreach ( (array) $extra_data as $data_key => $data_values ) {
    		$html = str_replace( '<div ', '<div ' . esc_attr( $data_key ) . "='" . json_encode( $data_values ) . "' ", $html );
    	}
        
        $html = str_replace( 'gallery ', 'gallery js-flickity ', $html );
    
        return $html;
    }
    
    public static function custom_image_sizes( $sizes ){
    	$custom_sizes = array(
    		'post-slider'	=>	'Post Slider'
    	);
    	return array_merge( $sizes, $custom_sizes );
    }    

    static function register(){
        add_filter( 'gallery_style',  array(__CLASS__, 'gallery') );    
        add_filter( 'image_size_names_choose', array(__CLASS__, 'custom_image_sizes') );
        
        add_image_size( 'post-slider', 1320, 400, false );
    }
    
}