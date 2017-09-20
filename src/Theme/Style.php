<?php

namespace Svbk\WP\Helpers\Theme;

use Svbk\Helpers\CDN\JsDelivr;
use Svbk\Helpers\CDN\CdnJs;

class Style {

	public static $default_cdn = '\Svbk\WP\Helpers\CDN\JsDelivr';

	public static function enqueue( $package, $files = '', $options = array(), $cdn_class = null ) {
		self::register( $package, $files, $options, $cdn_class );

		wp_enqueue_style( $package );
	}

	public static function register( $package, $files, $options = array(), $cdn_class = null ) {

		$defaults = array(
			'deps' => array(), 
			'package' => $package,
			'version' => 'latest', 
			'media' => 'all', 
			'overwrite' => false
		);
		
		$opt = array_merge($defaults, $options);

		if ( wp_style_is( $package , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_style( $package );
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
			wp_register_style( $package, $url, $opt['deps'], $opt['version'], $opt['media'] );
		}
	}


}
