<?php

namespace Svbk\WP\Helpers\Theme;

use Svbk\Helpers\CDN\JsDelivr;
use Svbk\Helpers\CDN\CdnJs;
use Exception;

class Script {

	public static $async_scripts = array();
	public static $defer_scripts = array();
	public static $tracking_scripts = array();

	public static $default_cdn = '\Svbk\WP\Helpers\CDN\JsDelivr';

	public static function enqueue( $package, $files = '', $options = array() ) {
		$handle = self::register( $package, $files, $options );

		wp_enqueue_script( $handle );
		
		return $handle;
	}

	public static function register( $package, $files, $options = array() ) {
		
		$defaults = array(
			'deps' => array(), 
			'package' => $package,
			'version' => 'latest', 
			'in_footer' => true, 
			'overwrite' => false,
			'cdn_class' => self::$default_cdn,
			'async' => false,
			'defer' => false,
			'profiling' => false,
			'handle' => $package,
		);
		
		$opt = array_merge($defaults, $options);

		if ( wp_script_is( $opt['handle'] , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_script( $opt['handle'] );
			} else {
				return false;
			}
		}

		$cdn_class = $opt['cdn_class'];

		if ( false === $cdn_class ) {
			$url = $files;
		} elseif ( class_exists( $cdn_class ) ) {
			$cdn = new $cdn_class( $opt['package'], $options );
			$url = $cdn->url( $files );
		} else {
			throw new Exception('CDN Class ' . $cdn_class . ' doesn\'t exists');
		}
		
		if ( !$url ) {
			return;
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


	public static function set_async( $handle ) {
		self::$async_scripts = array_merge( self::$async_scripts, (array) $handle );
	}

	public static function set_defer( $handle ) {
		self::$defer_scripts = array_merge( self::$defer_scripts, (array) $handle );
	}

	public static function set_tracking( $handle ) {
		self::$tracking_scripts = array_merge( self::$tracking_scripts, (array) $handle );
	}

	public static function add_script_attributes( $tag, $handle ) {

		if ( in_array( $handle, self::$async_scripts ) ) {
			$tag = str_replace( ' src', ' async src', $tag );
		}

		if ( in_array( $handle, self::$defer_scripts ) ) {
			$tag = str_replace( ' src', ' defer src', $tag );
		}
		
		if ( in_array( $handle, self::$tracking_scripts ) ) {
			$tag = apply_filters( 'svbk_script_setup_tracking', $tag, $handle );
		}		

		return $tag;
	}

}

add_filter( 'script_loader_tag', array( Script::class, 'add_script_attributes' ), 10, 2 );
