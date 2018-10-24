<?php

namespace Svbk\WP\Helpers\Assets;

use Exception;

class Asset {

	public static $default_source = '\Svbk\WP\Helpers\CDN\JsDelivr';

	public static function enqueue( $package, $files = '', $options = array() ){ }

	public static function register( $package, $files, $options = array() ) { }

	public static function get_url( $package, $files, $params = array() ) {
		
		$defaults = array(
			'version' => 'latest', 
			'source' => self::$default_source,
			'source_options' => array(),
			
			// backward compat
			'cdn_class' => null,
		);		
		
		$params = array_merge($defaults, $params);
		
		$params['source_options'] = array_merge(
			array(
				'version' => $params['version']
			),
			$params['source_options']);		
		
		$source_class = (null !== $params['cdn_class']) ? $params['cdn_class'] : $params['source'];

		if ( false === $source_class ) {
			$url = $files;
		} elseif ( 'theme' === $source_class ) {
			$url = get_theme_file_uri( $files );			
		} elseif ( class_exists( $source_class ) ) {
			$cdn = new $source_class( $package, $params['source_options'] );
			$url = $cdn->url( $files );
		} else {
			throw new Exception('Asset source class ' . $source_class . ' doesn\'t exists');
		}
		
		return $url;
	}

}
