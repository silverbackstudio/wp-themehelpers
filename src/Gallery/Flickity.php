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
    
    static function register($properties=array()){
        
        $class = get_called_class();
        
        return new $class($properties);
    }    
    
    public function __construct($properties=array()){
        foreach($properties as $property => $value){
            if(property_exists($this, $property)) {
                $this->$property = $value;
            }
        }         
        
        if(!$this->label){
            $this->label = __('Post Slider','svbk-helpers');
        }
        
        if(isset($properties['options'])){
            $this->options = wp_parse_args($properties['options'], self::$_options);
        } else {
            $this->options = self::$_options;
        }
        
        if(!has_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_script'))){
            add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_script'), 12 );
        }
    }
    
    public function setDefault(){
        
        add_filter( 'gallery_style',  array($this, 'post_gallery') );    
        add_filter( 'image_size_names_choose', array($this, 'custom_image_sizes') );
        
        add_image_size( $this->name, $this->width, $this->height, $this->crop );
    }
    
    public function post_gallery($html){
        
        if(strpos($html, 'gallery-size-'.$this->name) === false){
            return $html;
        }
    	
    	$html = str_replace( 'gallery ', 'gallery js-flickity ', $html );
    	$html = str_replace( '<div ', '<div ' . $this->formatHtmlAttributes() . ' ', $html );
    
        return $html;
    }
    
    
    public static function formatHtmlAttributes($options=array()){
        
        if(!isset($this) || empty($this->options)){
            $options = array_merge(self::$_options, $options);
        }
        
        return "data-flickity-options='" . esc_attr(json_encode( $options )) . "' ";
    }
    
    public static function gallery($image_ids, $options=array()){
        
    	$extra_data = array('data-flickity-options' => $this->options);
    	
    	foreach ( (array) $extra_data as $data_key => $data_values ) {
    		$html = str_replace( '<div ', '<div ' . esc_attr( $data_key ) . "='" . json_encode( $data_values ) . "' ", $html );
    	}
        
        $html = str_replace( 'gallery ', 'gallery js-flickity ', $html );
    
        return $html;
    }    
    
    
    public function custom_image_sizes( $sizes ){
    	$custom_sizes = array(
    		$this->name	=>	$this->label
    	);
    	return array_merge($custom_sizes, $sizes );
    }    
    
    static function enqueue_script(){
        wp_enqueue_script('flickity');
        wp_enqueue_style('flickity');
    }
    
}