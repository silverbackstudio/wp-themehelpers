<?php

namespace Svbk\WP\Helpers\Theme;

use Svbk\Helpers\CDN\JsDelivr;
use Svbk\Helpers\CDN\CdnJs;
use Exception;

class Script {

	public static $async_scripts = array();
	public static $defer_scripts = array();

	public static $default_cdn = '\Svbk\WP\Helpers\CDN\JsDelivr';

	public static function enqueue( $package, $files = '', $deps = array(), $version = 'latest', $in_footer = true, $overwrite = false, $cdn_class = null ) {
		self::register( $package, $files, $deps, $version, $in_footer, $overwrite, $cdn_class );

		wp_enqueue_script( $package );
	}

	public static function register( $package, $files, $options = array(), $cdn_class = null ) {

		$defaults = array(
			'deps' => array(), 
			'package' => $package,
			'version' => 'latest', 
			'in_footer' => true, 
			'overwrite' => false
		);
		
		$opt = array_merge($defaults, $options);

		if ( wp_script_is( $package , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_script( $package );
			} else {
				return false;
			}
		}

		if ( ! $cdn_class || ! class_exists( $cdn_class ) ) {
			$cdn_class = self::$default_cdn;
		}

		if ( class_exists( $cdn_class ) ) {
			$cdn = $cdn_class::get( $opt['package'], $options );
		} else {
			throw new Exception('CDN Class doesn\'t exists');
		}

		$files = (array)$files;

		if( ( count( $files ) > 1 ) && method_exists($cdn, 'combine') ) {
			$url = $cdn->combine( $files );
		} else {
			$url = $cdn->url( reset( $files ) );
		}

		if ( $url ) {
			wp_register_script( $package, $url, $opt['deps'], $opt['version'], $opt['in_footer'] );
		}
	}


	public static function set_async( $handle ) {

		if ( ! has_filter( 'script_loader_tag', array( __CLASS__, 'add_script_attributes' ) ) ) {
			add_filter( 'script_loader_tag', array( __CLASS__, 'add_script_attributes' ), 10, 2 );
		}

		self::$async_scripts = array_merge( self::$async_scripts, (array) $handle );
	}

	public static function set_defer( $handle ) {

		if ( ! has_filter( 'script_loader_tag', array( __CLASS__, 'add_script_attributes' ) ) ) {
			add_filter( 'script_loader_tag', array( __CLASS__, 'add_script_attributes' ), 10, 2 );
		}

		self::$defer_scripts = array_merge( self::$defer_scripts, (array) $handle );
	}

	public static function add_script_attributes( $tag, $handle ) {

		if ( in_array( $handle, self::$async_scripts ) ) {
			$tag = str_replace( ' src', ' async src', $tag );
		}

		if ( in_array( $handle, self::$defer_scripts ) ) {
			$tag = str_replace( ' src', ' defer src', $tag );
		}

		return $tag;
	}

}
