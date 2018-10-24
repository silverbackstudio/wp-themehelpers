<?php

namespace Svbk\WP\Helpers\Assets;

class Script extends Asset {

	public static $async_scripts = array();
	public static $defer_scripts = array();
	public static $tracking_scripts = array();
	public static $inline_scripts = array();

	public static function enqueue( $package, $files = '', $options = array() ) {
		$handle = self::register( $package, $files, $options );

		if ( $handle ) {
			wp_enqueue_script( $handle );
		} 
		
		if ( isset( $options['condition']) && !$options['condition'] ) {
			wp_dequeue_script( $handle );
		}
		
		return $handle;
	}

	public static function register( $package, $files, $options = array() ) {
		
		$defaults = array(
			'deps' => array(), 
			'version' => 'latest', 
			'in_footer' => true, 
			'overwrite' => false,
			'profiling' => false,			
			'async' => false,
			'defer' => get_theme_support('defer-scripts'),
			'inline' => false,
			'handle' => $package,
		);
		
		$opt = array_merge($defaults, $options);

		if ( wp_script_is( $opt['handle'] , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_script( $opt['handle'] );
			} else {
				return $opt['handle'];
			}
		}
		
		$url = self::get_url( $opt['handle'], $files, $opt );
		
		if ( !$url ) {
			return false;
		}
		
		wp_register_script( $opt['handle'], $url, $opt['deps'], $opt['version'], $opt['in_footer'] );
		
		if ( $opt['async'] ) {
			self::set_async( $opt['handle'] );
		}
		
		if ( $opt['defer'] ) {
			self::set_defer( $opt['handle'] );
		}		
		
		if ( $opt['profiling'] ) {
			self::set_tracking( $opt['handle'] );
		}
		
		return $opt['handle'];
	}

	public static function set_async( $handle, $enable = true ) {
		self::$async_scripts[$handle] = $enable;
	}

	public static function set_defer( $handle, $enable = true ) {
		self::$defer_scripts[$handle] = $enable;
	}

	public static function set_tracking( $handle, $enable = true ) {
		self::$tracking_scripts[$handle] = $enable;
	}
	
	public static function set_inline( $handle, $enable = true ) {
		self::$inline_scripts[$handle] = $enable;
	}
	
	public static function get_async( $handle, $default = false ) {
		return isset( self::$async_scripts[$handle] ) ? self::$async_scripts[$handle] : $default;
	}

	public static function get_defer( $handle, $default = false ) {
		return isset( self::$defer_scripts[$handle] ) ? self::$defer_scripts[$handle] : $default;
	}

	public static function get_tracking( $handle, $default = false ) {
		return isset( self::$tracking_scripts[$handle] ) ? self::$tracking_scripts[$handle] : $default;
	}
	
	public static function get_inline( $handle, $default = false ) {
		return isset( self::$inline_scripts[$handle] ) ? self::$inline_scripts[$handle] : $default;
	}	

	public static function settings( ) {
		
		$settings = wp_cache_get( 'script_managment_settings', 'svbk_assets' );
		
		if ( false !== $settings ) {
			return $settings;
		}
			
		$theme_support = get_theme_support('scripts-management');		
		
		if ( false === $theme_support ) {
			return false;
		}
		
		$defaults = array(
			'async' => true,
			'defer' => true,
			'tracking' => true,
			'default-async' => false,
			'default-defer' => false,
			'default-tracking' => false,
		);		
		
		$settings = array_merge( $defaults, isset($theme_support[0]) ? $theme_support[0] : array() );			
		
		wp_cache_set( 'script_managment_settings', $settings, 'svbk_assets', DAY_IN_SECONDS );
		
		return $settings;		
	}

	public static function manage_script( $tag, $handle ) {

		$settings = apply_filters( 'svbk_script_management_settings', self::settings(), $handle);
		
		if ( is_admin() || (false === $settings) ) {
			return $tag;
		}		
	
		if ( $settings['async'] && self::get_async( $handle, $settings['default-async'] ) ) {
			$tag = str_replace( ' src', ' async src', $tag );
		}

		if (  $settings['defer'] && self::get_defer( $handle, $settings['default-defer'] ) ) {
			$tag = str_replace( ' src', ' defer src', $tag );
			if ( ! $settings['async'] || ! self::get_async( $handle, $settings['default-async'] ) ) {
				$tag = preg_replace( "`<script type='text/javascript'>(.+)</script>`is", '<script type=\'text/javascript\'>window.addEventListener(\'DOMContentLoaded\', function() { $1 });</script>', $tag );
			}
		}
		
		if (  $settings['tracking'] && self::get_tracking( $handle, $settings['default-tracking'] ) ) {
			$tag = apply_filters( 'svbk_script_setup_tracking', $tag, $handle );
		}		

		return $tag;
	}

}

add_filter( 'script_loader_tag', array( Script::class, 'manage_script' ), 10, 2 );
