<?php

namespace Svbk\WP\Helpers\Theme;

class Config {
    
    public static function load_config($config_file='config.php'){
        
        $newconfig = array();
        
        $file = locate_template($config_file, false);
        
        if(file_exists($file)){
            $newconfig = include_once( $file );
            wp_cache_set( 'config', $newconfig, 'wpthemehelper', HOUR_IN_SECONDS );
        }
    
        return $newconfig;
    }         
    
    
    public static function get($group, $param=null, $default=null){
        
        $found = null;
        $config = wp_cache_get( 'config', 'wpthemehelper', false, $found  );
        
        if ( ! $found ) {
            $config = self::load_config();
        } 
        
        if( $group && isset($config[$group]) ){
            
            if($param) {
                return isset($config[$group][$param])?$config[$group][$param]:$default;
            } 
            
            return $config[$group];
        }
        
        return $default;

    }

    
}