<?php

namespace Svbk\WP\Helpers;

class Config {

	public static $expire = 3600; //an hour
	public static $configs = array();

	public static function load( $config_file = 'config.json', $config_name = 'global' ) {

		$newconfig = array();

		if ( file_exists( $config_file ) ) {
			$newconfig = json_decode( file_get_contents( $config_file ), true );
			
			if ( null === $newconfig ){
				throw new \Exception('Invalid JSON in config file: ' . $config_file);
			}
			
			self::$configs[ $config_name ] = $newconfig;
		} else {
			throw new \Exception('Cannot find config file: ' . $config_file);
		}
		
		return $newconfig;
	}


	public static function get( $path = array(), $config_name = 'global' ) {
		
		$path = (array)$path;
		
		$found = isset( self::$configs[ $config_name ] );

		$subject = self::$configs[ $config_name ];

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
		$found = isset( self::$configs[ $config_name ] );
		$config = self::$configs[ $config_name ];

		if ( ! $found ) {
			throw new \Exception('Invalid config name: ' . $config_name);
		}

		$subject = &$config;

		foreach( $path as $key ) {
			
			if( ! isset( $subject[ $key ] ) ) {
				$subject[ $key ] = array();		
			}			
			
			$subject = &$subject[ $key ];
		}
		
		$subject = $value;

		self::$configs[ $config_name ] = $config;
	}

}
