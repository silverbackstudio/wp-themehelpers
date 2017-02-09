<?php 

namespace Svbk\WP\Helpers;

class CdnScripts {
    
    static function enqueue_script( $package, $files='', $deps = array(), $version='latest', $in_footer=true, $overwrite=false){
        self::register_script($package, $files, $deps, $version, $in_footer );
        
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
	
    	if(is_array($files)){
    		$template = '//cdn.jsdelivr.net/g/%1$s@%3$s(%2$s)';
    		$files = implode('+', $files);
    	} else {
    		$template = '//cdn.jsdelivr.net/%1$s/%3$s/%2$s';		
    	}

	    wp_register_script($package, sprintf($template, $package, $files, $version), $deps, null, $in_footer);
    }

    
}