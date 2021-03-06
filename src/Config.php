<?php

namespace Svbk\WP\Helpers;

class Config {

	public static $expire = 3600;
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

	public static function exists( $config_name  ) {
		return isset( self::$configs[ $config_name ] );
	}

	public static function get( $path = array(), $config_name = 'global' ) {

		if ( ! self::exists($config_name) ) {
			throw new \Exception('Invalid config name: ' . $config_name);
		}

		$path = (array)$path;
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

		if ( ! self::exists($config_name) ) {
			throw new \Exception('Invalid config name: ' . $config_name);
		}

		$path = (array)$path;
		$config = self::$configs[ $config_name ];
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
