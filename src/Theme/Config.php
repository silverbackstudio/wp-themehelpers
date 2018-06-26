<?php

namespace Svbk\WP\Helpers\Theme;

class Config {

	public static $expire = HOUR_IN_SECONDS;

	public static function load_config( $config_file = 'config.php' ) {

		$newconfig = array();

		$file = locate_template( $config_file, false );

		if ( file_exists( $file ) ) {
			$newconfig = include_once( $file );
			wp_cache_set( 'config', $newconfig, 'svbkwphelpers', self::$expire );
		}

		return $newconfig;
	}


	public static function get( $group, $param = null, $default = null ) {

		$found = null;
		$config = wp_cache_get( 'config', 'svbkwphelpers', false, $found );

		if ( ! $found ) {
			$config = self::load_config();
		}

		if ( $group && isset( $config[ $group ] ) ) {

			if ( $param ) {
				return isset( $config[ $group ][ $param ] )?$config[ $group ][ $param ]:$default;
			}

			return $config[ $group ];
		}

		return $default;
	}

	public static function set( $value, $group = null, $param = null ) {

		$found = null;
		$config = wp_cache_get( 'config', 'svbkwphelpers', false, $found );

		if ( ! $found ) {
			$config = self::load_config();
		}

		if ( $group ) {

			if ( $param ) {
				$config[ $group ][ $param ] = $value;
			} else {
				$config[ $group ] = $value;
			}
		} else {
			$config = $value;
		}

		wp_cache_replace( 'config', $config, 'svbkwphelpers', self::$expire );

	}

}
