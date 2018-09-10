<?php

namespace Svbk\WP\Helpers\Theme;

use Svbk\Helpers\CDN\JsDelivr;
use Svbk\Helpers\CDN\CdnJs;

class Style {

	public static $sync_styles = array();
	public static $default_source = '\Svbk\WP\Helpers\CDN\JsDelivr';

	public static function enqueue( $package, $files = '', $options = array() ) {
		$handle = self::register( $package, $files, $options );

		wp_enqueue_style( $package );
		
		return $handle;
	}

	public static function register( $package, $files, $options = array() ) {

		$defaults = array(
			'deps' => array(), 
			'package' => $package,
			'version' => 'latest', 
			'source' => self::$default_source,
			'media' => 'all', 
			'overwrite' => false,
			'handle' => $package,
			'source_options' => array(),
		);
		
		$opt = array_merge($defaults, $options);
		$opt['source_options'] = array_merge(
			array(
				'version' => $opt['version']
			),
			$opt['source_options']);

		$source_class =  $opt['source'];

		if ( wp_style_is( $package , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_style( $package );
			} else {
				return false;
			}
		}

		if ( false === $source_class ) {
			$url = $files;
		} elseif ( class_exists( $source_class ) ) {
			$cdn = new $source_class( $opt['package'], $opt['source_options'] );
			$url = $cdn->url( $files );
		} else {
			throw new Exception('Style source class ' . $source_class . ' doesn\'t exists');
		}
		
		if ( !$url ) {
			return;
		}

		wp_register_style( $package, $url, $opt['deps'], $opt['version'], $opt['media'] );
		
		return $opt['handle'];
	}

	public static function async() {
		add_filter( 'style_loader_tag', array( Style::class, 'add_style_attributes' ), 10, 4 );
	}

	public static function set_sync( $handle ) {
		self::$sync_styles = array_merge( self::$sync_styles, (array) $handle );
	}

	public static function add_style_attributes( $tag, $handle, $href, $media ) {

		if ( !in_array( $handle, self::$sync_styles ) ) {
			$tag =  "<link rel='preload' id='$handle-css' href='$href' type='text/css' media='$media' as='style' onload=\"this.onload=null;this.rel='stylesheet'\" />";
			$tag .= "<noscript><link rel='stylesheet' id='$handle-css-noscript' media='$media' href='$href'></noscript>" . PHP_EOL;
		}
		
		return $tag;
	}

}
