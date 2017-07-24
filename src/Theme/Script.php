<?php 

namespace Svbk\WP\Helpers\Theme;

use Svbk\Helpers\CDN\JsDelivr;
use Svbk\Helpers\CDN\CdnJs;

class Script {
    
    public static $async_scripts = array();
    public static $defer_scripts = array();
    
    public static $default_cdn = '\Svbk\Helpers\CDN\JsDelivr'; 
    
    public static function enqueue( $package, $files = '', $deps = array(), $version = 'latest', $in_footer = true, $overwrite = false){
        self::register( $package, $files, $deps, $version, $in_footer, $overwrite );
        
        wp_enqueue_script( $package );
    }
    
    public static function register( $package, $files, $deps = array(), $version='latest', $in_footer = true, $overwrite = false ){
	
	    if ( wp_script_is( $package , 'registered' ) ) {
	        if ( $overwrite ) {
	            wp_deregister_script( $package );
	        } else {
	            return false;
	        }
	    }
	
		$url = self::getUrl( $package, $files, $version );

		if ( $url ) {
	    	wp_register_script( $package, $url, $deps, null, $in_footer );
		}
    }
     
    public static function register_style( $package, $files, $deps = array(), $version = 'latest', $media = 'all', $overwrite = false ){
	
	    if ( wp_style_is( $package, 'registered' )){
	        if ( $overwrite ) {
	            wp_deregister_style( $package );
	        } else {
	            return false;
	        }
	    }
	
		$url = self::getUrl( $package, $files, $version );

		if ( $url ) {
	    	wp_register_style( $package, $url, $deps, null, $media );
		}
    }    


	public static function getUrl( $package, $files, $version = 'latest', $cdn_class = null ){
		
		if ( ! $cdn_class ) {
			$cdn_class = self::$default_cdn;
		}
		
		if ( class_exists( $cdn_class ) ) {
			return $cdn_class::get_script_url();
		}
		
	}

    public static function set_async( $handle ) { 
    	
    	if ( ! has_filter( 'script_loader_tag', array( __CLASS__ , 'add_script_attributes') ) ) {
    		add_filter('script_loader_tag', array( __CLASS__ , 'add_script_attributes'), 10, 2); 
    	}
    	
    	self::$async_scripts = array_merge( self::$async_scripts, (array) $handle );
    }  
    
    public static function set_defer( $handle ) { 
    	
    	if ( ! has_filter( 'script_loader_tag', array( __CLASS__ , 'add_script_attributes') ) ) {
    		add_filter('script_loader_tag', array( __CLASS__ , 'add_script_attributes'), 10, 2); 
    	}    	
    	
    	self::$defer_scripts = array_merge( self::$defer_scripts, (array) $handle );
    }      
    
    public static function add_script_attributes($tag, $handle) {
        
        if (in_array($handle, self::$async_scripts)){
        	$tag = str_replace( ' src', ' async src', $tag );
        } 
        
        if (in_array($handle, self::$defer_scripts)){
        	$tag = str_replace( ' src', ' defer src', $tag );
        }
            
        return $tag;
    }        

}