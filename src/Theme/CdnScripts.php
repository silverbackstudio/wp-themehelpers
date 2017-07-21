<?php 

namespace Svbk\WP\Helpers\Theme;

class CdnScripts {
    
    static function enqueue_script( $package, $files='', $deps = array(), $version='latest', $in_footer=true, $overwrite=false){
        self::register_script($package, $files, $deps, $version, $in_footer, $overwrite );
        
        wp_enqueue_script($package);
    }
    
    static function register_script($package, $files, $deps = array(), $version='latest', $in_footer=true, $overwrite=false ){
	
	    if( wp_script_is($package , 'registered')){
	        if($overwrite){
	            wp_deregister_script( $package );
	        } else {
	            return false;
	        }
	    }
	
		$url = self::getUrl($package, $files, $version);

	    wp_register_script($package, $url, $deps, null, $in_footer);
    }
    
    static function register_style($package, $files, $deps = array(), $version='latest', $media='all', $overwrite=false ){
	
	    if( wp_style_is($package, 'registered')){
	        if($overwrite){
	            wp_deregister_style( $package );
	        } else {
	            return false;
	        }
	    }
	
		$url = self::getUrl($package, $files, $version);

	    wp_register_style($package, $url, $deps, null, $media);
    }    
    
    static function enqueue_style( $package, $files='', $deps = array(), $version='latest', $media='all', $overwrite=false){
        self::register_script($package, $files, $deps, $version, $media, $overwrite );
        
        wp_enqueue_style($package);
    }    

	static function getUrl($package, $files, $version='latest'){
		
    	if(is_array($files)){
    		$template = '//cdn.jsdelivr.net/g/%1$s@%3$s(%2$s)';
    		$files = implode('+', $files);
    	} else {
    		$template = '//cdn.jsdelivr.net/%1$s/%3$s/%2$s';		
    	}		
		
		return sprintf($template, $package, $files, $version);
	}

    
}