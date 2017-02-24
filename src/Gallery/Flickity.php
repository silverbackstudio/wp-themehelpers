<?php

namespace Svbk\WP\Helpers\Gallery;

use Svbk\WP\Helpers\CdnScripts;

class Flickity {
    
    public $width = 1320;
    public $height = 400;
    public $crop = false;
    public $name = 'post-slider';
    public $label;
    
    public static $_options = array(
        'cellSelector'=>'.gallery-item',
        'imagesLoaded'=>'true',
        'contain'=> true,
        'pageDots' => false,
        'prevNextButtons'=>true
	);
	
	public $options = array();
    
    public function __construct($properties=array()){
        foreach($properties as $property => $value){
            if(property_exists($this, $property)) {
                $this->$property = $value;
            }
        }         
        
        if(!$this->label){
            $this->label = __('Post Slider','svbk-themehelper');
        }
        
        if(isset($properties['options'])){
            $this->options = wp_parse_args($properties['options'], self::$_options);
        } else {
            $this->options = self::$_options;
        }
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_script'), 12, 2 );
        
        add_filter( 'gallery_style',  array($this, 'gallery') );    
        add_filter( 'image_size_names_choose', array($this, 'custom_image_sizes') );
        
        add_image_size( $this->name, $this->width, $this->height, $this->crop );        
        
    }
    
    function gallery($html){
        
        if(strpos($html, 'gallery-size-'.$this->name) === false){
            return $html;
        }
        
    	$extra_data = array('data-flickity-options' => $this->options);
    	
    	foreach ( (array) $extra_data as $data_key => $data_values ) {
    		$html = str_replace( '<div ', '<div ' . esc_attr( $data_key ) . "='" . json_encode( $data_values ) . "' ", $html );
    	}
        
        $html = str_replace( 'gallery ', 'gallery js-flickity ', $html );
    
        return $html;
    }
    
    public function custom_image_sizes( $sizes ){
    	$custom_sizes = array(
    		'post-slider'	=>	__('Post Slider','svbk-themehelper')
    	);
    	return array_merge($custom_sizes, $sizes );
    }    
    
    static function enqueue_script(){
        wp_enqueue_script('flickity');
        wp_enqueue_style('flickity');
    }

    static function register($properties=array()){
        
        $class = get_called_class();
        
        return new $class($properties);
    }
    
}