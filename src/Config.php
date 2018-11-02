<?php

namespace Svbk\WP\Helpers;

class Config {

	public static $expire = HOUR_IN_SECONDS;

	public static function load( $config_file = 'config.json', $config_name = 'global' ) {

		$newconfig = array();

		if ( file_exists( $config_file ) ) {
			$newconfig = json_decode( file_get_contents( $config_file ), true );
			
			if ( null === $newconfig ){
				throw new \Exception('Invalid theme config file');
			}
			
			wp_cache_set( $config_name, $newconfig, 'svbkconfig', self::$expire );
		}
		
		return $newconfig;
	}


	public static function get( $path, $config_name = 'global' ) {
		
		$path = (array)$path;
		
		$found = null;
		$config = wp_cache_get( $config_name, 'svbkconfig', false, $found );

		if ( ! $found ) {
			$config = self::load();
		}

		$subject = $config;

		foreach( $path as $key ) {
			
			if( ! isset( $subject[ $key ] ) ) {
				$subject = null;
				break;
			}			
			
			$subject = $subject[ $key ];
		}

		return $subject;
	}

	public static function set( $path, $value = null, $config_name = 'global' ) {

		$path = (array)$path;
		$found = null;
		$config = wp_cache_get( $config_name, 'svbkconfig', false, $found );

		if ( ! $found ) {
			$config = self::load();
		}

		$subject = &$config;

		foreach( $path as $key ) {
			
			if( ! isset( $subject[ $key ] ) ) {
				$subject[ $key ] = array();		
			}			
			
			$subject = &$subject[ $key ];
		}
		
		$subject = $value;

		wp_cache_replace( $config_name, $config, 'svbkconfig', self::$expire );

	}

}
