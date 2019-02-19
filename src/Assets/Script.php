<?php

namespace Svbk\WP\Helpers\Assets;

class Script extends Asset {

	public static $async_scripts = array();
	public static $defer_scripts = array();
	public static $tracking_scripts = array();
	public static $inline_scripts = array();

	public static function serverPush( $uri, $as = 'script', $crossorigin = false ){
		parent::serverPush( $uri, $as, $crossorigin );
	}

	public static function enqueue( $package, $files = '', $options = array() ) {
		
		$options = apply_filters( 'svbk_asset_script_params', $options, $package, $files );
		
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
			'tracking' => null,			
			'async' => null,
			'defer' => null,
			'inline' => false,
			'handle' => $package,
	        'prefetch' => false,
	        'preload' => false,
		);
		
		$opt = array_merge($defaults, $options);

		if ( wp_script_is( $opt['handle'] , 'registered' ) ) {
			if ( $opt['overwrite'] ) {
				wp_deregister_script( $opt['handle'] );
			} else {
				return $opt['handle'];
			}
		}
		
		$url = self::get_url( $package, $files, $opt );
		
		if ( !$url ) {
			return false;
		}
		
		wp_register_script( $opt['handle'], $url, $opt['deps'], $opt['version'], $opt['in_footer'] );
		
		if ( null !== $opt['async'] ) { 
			self::set_async( $opt['handle'], $opt['async'] );
		}
		
		if ( null !== $opt['defer'] ) {
			self::set_defer( $opt['handle'], $opt['defer'] );
		}
		
		if ( null !== $opt['tracking'] ) {
			self::set_tracking( $opt['handle'], $opt['tracking'] );
		}
		
		if ( false !== $opt['prefetch'] ) {
			self::hint( 'prefetch', $url );
		}
		
		if ( false !== $opt['preload'] ) {
			self::preload( $url, 'script', is_array($opt['preload']) ? $opt['preload'] : array() );
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
		return apply_filters( 'script_management_get_async', isset( self::$async_scripts[$handle] ) ? self::$async_scripts[$handle] : $default, $handle);
	}

	public static function get_defer( $handle, $default = false ) {
		return apply_filters( 'script_management_get_defer', isset( self::$defer_scripts[$handle] ) ? self::$defer_scripts[$handle] : $default, $handle );;
	}

	public static function get_tracking( $handle, $default = false ) {
		return apply_filters( 'script_management_get_tracking', isset( self::$tracking_scripts[$handle] ) ? self::$tracking_scripts[$handle] : $default, $handle );;
	}
	
	public static function get_inline( $handle, $default = false ) {
		return apply_filters( 'script_management_get_inline', isset( self::$inline_scripts[$handle] ) ? self::$inline_scripts[$handle] : $default, $handle );;
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

		$doc = new \DOMDocument();
		$doc->loadHTML( $tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$scripts = $doc->getElementsByTagName('script');
	
		if ( empty( $scripts ) ) {
			return $tag;
		}
	
		foreach( $scripts as $script ) {
			
			$is_async = $settings['async'] && self::get_async( $handle, $settings['default-async'] );
			$is_defer = $settings['defer'] && self::get_defer( $handle, $settings['default-defer'] );
			
			if( $is_async && !$script->nodeValue ){
				$script->setAttribute('async', '');
			} else if( $is_async ) {
				//@TODO: Set async inline scripts
			}
	
			if ( $is_defer && !$script->nodeValue ) {
				$script->setAttribute('defer', '');
			} else if( $is_defer ) {
				$script->nodeValue = self::defer_inline_code( $script->nodeValue );
			}
		}	
		
		$tag = $doc->saveHTML();

		if (  $settings['tracking'] && self::get_tracking( $handle, $settings['default-tracking'] ) ) {
			$new_tag = apply_filters( 'svbk_script_setup_tracking', $new_tag, $handle );
		}	

		return $tag;
	}

	public static function manage_src( $src, $handle ) {
		if ( strpos( $src, 'ver=' ) ){
        	$src = remove_query_arg( 'ver', $src );
		}
		
		return $src;
	}

	public static function defer_inline_script( $tag ){
		$doc = new \DOMDocument();
		$doc->loadHTML( $tag );
		
		$scripts = $doc->getElementsByTagName('script');
	
		if ( empty( $scripts ) ) {
			return $tag;
		}
	
		foreach( $scripts as $script ) {
			if( $script->nodeValue ){
				$script->nodeValue = self::defer_inline_code( $script->nodeValue );
			} 
		}
		
		return $doc->saveHTML();
	}
	
	public static function defer_inline_code( $js ){
		if ( $js ) {
			$js = 'window.addEventListener(\'DOMContentLoaded\', function() { ' . $js . ' });';
		}
		
		return $js;
	}	

	public static function common() {
		
		Script::register( 'waypoints', 'lib/jquery.waypoints.min.js', [ 'version' => '4', 'deps' => 'jquery', 'defer' => true ] );
		Script::register( 'waypoints-sticky', 'lib/shortcuts/sticky.min.js', [ 'version' => '4', 'deps' => ['jquery', 'waypoints'], 'package' => 'waypoints', 'defer' => true ] );
		Script::register( 'jquery.collapse', 'src/jquery.collapse.js', [ 'version' => '1', 'deps' => 'jquery', 'package' => 'jquery-collapse', 'defer' => true ] );
		
		Script::register( 'flickity', 'dist/flickity.pkgd.min.js', [ 'version' => '2', 'defer' => true] );
		
		Script::register( 'masonry-native', 'dist/masonry.pkgd.min.js', ['version' => '4', 'package' => 'masonry-layout', 'defer' => true ] );
		Script::register( 'imagesloaded', 'imagesloaded.pkgd.min.js', ['version' => '4', 'package' => 'imagesloaded', 'defer' => true ] );
		Script::register( 'jquery.localscroll', 'jquery.localScroll.min.js', ['version' => '2', 'deps' => 'jquery', 'defer' => true]);
		Script::register( 'jquery.scrollto', 'jquery.scrollTo.min.js', [ 'version' => '2.1', 'deps' => 'jquery', 'defer' => true ] );
		
		Script::register( 'history.js', 'scripts/bundled/html4+html5/jquery.history.js', [ 'version' => '1.8', 'package' => 'historyjs', 'deps' => 'jquery' ] );
	}

}

add_filter( 'script_loader_tag', array( Script::class, 'manage_script' ), 10, 2 );
//add_filter( 'script_loader_src', array( Script::class, 'manage_src' ), 100, 2 );
