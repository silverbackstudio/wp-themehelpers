<?php

namespace Svbk\WP\Helpers\Assets;

use Exception;

class Asset {

	/*
	 * @param string Default CDN Class
	 */	
	public static $default_source = '\Svbk\WP\Helpers\CDN\JsDelivr';
	
	/*
	 * @param array An array of assets to preload
	 *	[
	 *		'uri' => '', 
	 *		'as' => '', 
	 *		'type' => '', 
	 *		'crossorigin' => '', 
 	 *		'push' => '',
	 *	]
	 *
	 */
	public static $preload_assets = array();
	
	/*
	 * @param array Store resource hints
	 */	
	public static $resource_hints = array();

	public static function hint( $relation_type, $resource ){
		if ( !isset( Asset::$resource_hints[$relation_type] ) ) {
			Asset::$resource_hints[$relation_type] = array();
		}
		
		Asset::$resource_hints[$relation_type][] = $resource;
	}
	
	public static function preload( $uri, $as, $options = array() ){

		$defaults = array(
			'uri' => $uri, 
			'as' => $as, 
			'type' => false, 
		 	'crossorigin' => false, 
	 	 	'push' => false,			
		);
		
		Asset::$preload_assets[] = array_merge( $defaults, $options );
	}
	
	public static function serverPush( $uri, $as, $crossorigin = false ){
		self::preload($uri, $as, false, $crossorigin, true);
	}

	public static function enqueue( $package, $files = '', $options = array() ){ }

	public static function register( $package, $files, $options = array() ) { }

	public static function get_url( $package, $files, $params = array() ) {
		
		$defaults = array(
			'version' => 'latest', 
			'source' => self::$default_source,
			'source_options' => array(),
			'package' => $package,
			
			// backward compat
			'cdn_class' => null,
		);		
		
		$params = array_merge($defaults, $params);
		
		$params['source_options'] = array_merge(
			array(
				'version' => $params['version']
			),
			$params['source_options']
		);		
		
		$source_class = (null !== $params['cdn_class']) ? $params['cdn_class'] : $params['source'];

		if ( false === $source_class ) {
			$url = $files;
		} elseif ( 'theme' === $source_class ) {
			$url = get_theme_file_uri( $files );			
		} elseif ( class_exists( $source_class ) ) {
			$cdn = new $source_class( $params['package'], $params['source_options'] );
			$url = $cdn->url( $files );
		} else {
			throw new Exception('Asset source class ' . $source_class . ' doesn\'t exists');
		}
		
		return $url;
	}

	public static function output_preload_tags(){
		array_walk( static::$preload_assets, array( static::class, 'preload_tag' ) );
	}
	
	public static function preload_tag( $asset ) {
		extract( $asset );

		if ( !$uri || !$as ) {
			return;
		}
		
		$tag = '<link rel="preload" href="' . esc_attr($uri). '" as="' . esc_attr($as) . '"' ;
		
		if ( true === $crossorigin ) {
			$tag .= ' crossorigin="anonymous"';
		} else if( $crossorigin ) {
			$tag .= ' crossorigin="' . esc_attr( $crossorigin ) . '"';
		}
		
		if ( $type ) {
			$tag .= ' type="' . esc_attr( $type ) . '"';
		}		
		
		$tag .=  ' >';
		
		echo $tag;
	}	
	
	public static function output_preload_headers(){
		array_walk( static::$preload_assets, array( static::class, 'link_header' ) );
	}	
	
	public static function link_header( $asset ){
		extract( $asset );

		if ( !$uri || !$as ) {
			return;
		}

		header("Link: <{$uri}>; rel=preload; as={$as};" . ( !$push ? ' nopush;' : '') . ( $crossorigin ? ' crossorigin;' : '' ) , false);
	}		

	public static function resource_hints($urls, $relation_type ){

		if  ( !empty( Asset::$resource_hints[$relation_type] ) ) {
			$urls = array_merge( $urls, Asset::$resource_hints[$relation_type] );
		}
		
		return $urls;		
	}

}

add_action( 'template_redirect', array( Asset::class, 'output_preload_headers' ), 11 );
add_action( 'wp_head', array( Asset::class, 'output_preload_tags' ) );
add_filter( 'wp_resource_hints', array( Asset::class, 'resource_hints' ), 10, 2 );