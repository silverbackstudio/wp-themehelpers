<?php

namespace Svbk\WP\Helpers\Assets;

class Style extends Asset {

	public static $async_styles = array();
	public static $inline_styles = array();
	
	public static function serverPush( $uri, $as = 'style', $crossorigin = false ){
		parent::serverPush( $uri, $as, $crossorigin );
	}	

	public static function enqueue( $package, $files = '', $options = array() ) {
		$handle = self::register( $package, $files, $options );

		if ( $handle ) {
			wp_enqueue_style( $handle );
		} 
		
		if ( isset( $options['condition']) && !$options['condition'] ) {
			wp_dequeue_style( $handle );
		}
		
		return $handle;
	}
	
	public static function inline( $package, $files = '', $options = array() ) {

		if ( isset( $options['condition']) && !$options['condition'] ) {
			wp_dequeue_style( $handle );
		}
		
		return $handle;
	}	

	public static function register( $package, $files, $options = array() ) {

		$defaults = array(
			'deps' => array(), 
			'version' => 'latest', 
			'media' => 'all', 
			'overwrite' => false,
			'handle' => $package,
			'inline' => null,
			'async' => null,
	        'prefetch' => false,
	        'preload' => false,			
		);
		
		$opt = array_merge($defaults, $options);

		if ( wp_style_is( $package , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_style( $opt['handle'] );
			} else {
				return $opt['handle'];
			}
		}
		
		$url = self::get_url( $opt['handle'], $files, $opt );
		
		if ( !$url ) {
			return false;
		}

		if ( null !== $opt['async'] ) {
			self::set_async( $opt['handle'], $opt['async'] );
		}
		
		if ( null !== $opt['inline'] ) {
			self::set_inline( $opt['handle'], $opt['inline'] ? $files : false );
		}		
		
		if ( false !== $opt['prefetch'] ) {
			self::hint( 'prefetch', $url );
		}
		
		if ( false !== $opt['preload'] ) {
			self::preload( $url, 'style', is_array($opt['preload']) ? $opt['preload'] : array() );
		}			

		wp_register_style( $package, $url, $opt['deps'], $opt['version'], $opt['media'] );
		
		return $opt['handle'];
	}

	public static function async() {
		add_theme_support('async-styles');
	}
	
	public static function set_async( $handle, $enable = true ) {
		self::$async_styles[$handle] = $enable;
	}
	public static function set_inline( $handle, $enable = true ) {
		self::$inline_styles[$handle] = $enable;
	}
	
	public static function get_async( $handle, $default = false ) {
		return isset( self::$async_styles[$handle] ) ? self::$async_styles[$handle] : $default;
	}
	
	public static function get_inline( $handle, $default = false ) {
		return isset( self::$inline_styles[$handle] ) ? self::$inline_styles[$handle] : $default;
	}	

	public static function settings( ) {
		
		$settings = wp_cache_get( 'styles_managment_settings', 'svbk_assets' );
		
		if ( false !== $settings ) {
			return $settings;
		}
			
		$theme_support = get_theme_support('styles-management');		
		
		if ( false === $theme_support ) {
			return false;
		}
		
		$defaults = array(
			'async' => true,
			'inline' => true,
			'default-async' => false,
			'default-inline' => false,
		);		
		
		$settings = array_merge( $defaults, isset($theme_support[0]) ? $theme_support[0] : array() );			
		
		wp_cache_set( 'styles_managment_settings', $settings, 'svbk_assets', DAY_IN_SECONDS );
		
		return $settings;		
	}

	public static function manage_style( $tag, $handle, $href, $media ) {

		$settings = apply_filters( 'svbk_style_management_settings', self::settings(), $handle);
		
		if ( is_admin() || (false === $settings) ) {
			return $tag;
		}
		
		$filePath = self::get_inline( $handle, $settings['default-inline']);
		
		if ( $settings['inline'] && $filePath && file_exists( $filePath ) ) {
			return '<style id="'.$handle.'-css" type="text/css" media="' . $media . '">' . wp_strip_all_tags( file_get_contents( $filePath ) ) . '</style>';
		}

		if ( $settings['async'] && self::get_async( $handle, $settings['default-async'] ) ) {
			$tag =  "<link rel='preload' id='$handle-css' href='$href' type='text/css' media='$media' as='style' onload=\"this.onload=null;this.rel='stylesheet'\" />";
			$tag .= "<noscript><link rel='stylesheet' id='$handle-noscript-css' media='$media' href='$href'></noscript>" . PHP_EOL;
		}
		
		return $tag;
	}
	
	public static function manage_src( $src, $handle ) {
		
		if ( strpos( $src, 'ver=' ) ){
        	$src = remove_query_arg( 'ver', $src );
		}
		
		return $src;
	}	
	
	public static function common() {
		Style::register( 'flickity',  'dist/flickity.min.css' , [ 'version' => '2' ] );
	}
	
}

add_filter( 'style_loader_tag', array( Style::class, 'manage_style' ), 10, 4 );
add_filter( 'style_loader_src', array( Style::class, 'manage_src' ), 100, 2 );