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
		
		wp_register_script( $opt['handle'], $url, (array) $opt['deps'], $opt['version'], $opt['in_footer'] );
		
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

	public static function manage_script( $tag, $handle, $src ) {

		global $wp_scripts;

		$settings = apply_filters( 'svbk_script_management_settings', self::settings(), $handle);
		
		if ( is_admin() || (false === $settings) ) {
			return $tag;
		}		

		$is_async = $settings['async'] && self::get_async( $handle, $settings['default-async'] );
		$is_defer = $settings['defer'] && self::get_defer( $handle, $settings['default-defer'] );
		
		if ( ! $is_async || $is_defer ) {
			return $tag;
		}
		
		$bundle = $handle !== 'jquery-core' ? $handle : 'jquery';

		$obj = $wp_scripts->registered[$handle];

		$deps = $obj->deps;
		$src = $obj->src;
		$cond_before = $cond_after = '';
		$conditional = isset( $obj->extra['conditional'] ) ? $obj->extra['conditional'] : '';

		if ( $conditional ) {
			$cond_before = "<!--[if {$conditional}]>\n";
			$cond_after = "<![endif]-->\n";
		}

		$before_handle = $wp_scripts->print_inline_script( $handle, 'before', false );
		$after_handle = $wp_scripts->print_inline_script( $handle, 'after', false );

		if ( $before_handle ) {
			$before_handle = sprintf( "<script type='text/javascript'>\n%s\n</script>\n", $before_handle );
		}

		if ( $after_handle ) {
			
			if ( $is_async ) {
				$before_handle = self::async_inline_code( $after_handle, [ $handle ] );
			} elseif( $is_defer ){
				$before_handle = self::defer_inline_code( $after_handle );
			}
			
			$after_handle = sprintf( "<script type='text/javascript'>\n%s\n</script>\n", $before_handle );
		}

		$has_conditional_data = $conditional && $wp_scripts->get_data( $handle, 'data' );

		$translations = $wp_scripts->print_translations( $handle, false );
		if ( $translations ) {
			$translations = sprintf( "<script type='text/javascript'>\n%s\n</script>\n", $translations );
		}

		if ( 'jquery-migrate' === $handle ) {
			$deps[] = 'jquery';
		}

		if ( $is_async ) {
			$load_async = self::async_inline_code( "loadjs('{$src}', '{$bundle}');", $deps );
			$tag = "{$translations}{$cond_before}{$before_handle}<script type='text/javascript'>{$load_async}</script>\n{$after_handle}{$cond_after}";
		} elseif( $is_defer ){
			$tag = "{$translations}{$cond_before}{$before_handle}<script type='text/javascript' defer src='$src'>{$load_async}</script>\n{$after_handle}{$cond_after}";
		}

		if (  $settings['tracking'] && self::get_tracking( $handle, $settings['default-tracking'] ) ) {
			$tag = apply_filters( 'svbk_script_setup_tracking', $tag, $handle );
		}	

		return $tag;
	}

	public static function manage_src( $src, $handle ) {
		if ( strpos( $src, 'ver=' ) ){
        	$src = remove_query_arg( 'ver', $src );
		}
		
		return $src;
	}

	public static function async_inline_script( $tag, $deps = array()){
		
		if ( !$deps ) {
			return $code;
		}
		
		$doc = new \DOMDocument();
		$doc->loadHTML( $tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NONET );
		
		$scripts = $doc->getElementsByTagName('script');
	
		if ( empty( $scripts ) ) {
			return $tag;
		}
	
		foreach( $scripts as $script ) {
			if( $script->nodeValue ){
				$script->nodeValue = self::async_inline_code( $script->nodeValue, $deps );
			} 
		}
		
		return $doc->saveHTML();
	}

	public static function async_inline_code( $code, $deps = array() ){
		
		if ( !$deps ) {
			return $code;
		}
		
		return "loadjs.ready(". json_encode( $deps ) . ",  function(){" . PHP_EOL . $code . PHP_EOL . "});";
	}

	public static function defer_inline_script( $tag ){
		$doc = new \DOMDocument();
		$doc->loadHTML( $tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NONET );
		
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
	
	public static function parse_scripts( $tag ){
		$doc = new \DOMDocument();
		$doc->loadHTML( $tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NONET );
		
		$scripts = $doc->getElementsByTagName('script');
	
		if ( empty( $scripts ) ) {
			return false;
		}
	
		return $scripts;
	}	
	
	public static function defer_inline_code( $js ){
		if ( $js ) {
			$js = 'window.addEventListener(\'DOMContentLoaded\', function() { ' . $js . ' });';
		}
		
		return $js;
	}	

	public static function common() {
		
		Script::register( 'waypoints', 'lib/jquery.waypoints.min.js', [ 'version' => '4', 'deps' => ['jquery'], 'defer' => true ] );
		Script::register( 'waypoints-sticky', 'lib/shortcuts/sticky.min.js', [ 'version' => '4', 'deps' => ['jquery', 'waypoints'], 'package' => 'waypoints', 'defer' => true ] );
		Script::register( 'jquery.collapse', 'src/jquery.collapse.js', [ 'version' => '1', 'deps' => ['jquery'], 'package' => 'jquery-collapse', 'defer' => true ] );
		
		Script::register( 'flickity', 'dist/flickity.pkgd.min.js', [ 'version' => '2', 'defer' => true] );
		
		Script::register( 'masonry-native', 'dist/masonry.pkgd.min.js', ['version' => '4', 'package' => 'masonry-layout', 'defer' => true ] );
		Script::register( 'imagesloaded', 'imagesloaded.pkgd.min.js', ['version' => '4', 'package' => 'imagesloaded', 'defer' => true ] );
		Script::register( 'jquery.localscroll', 'jquery.localScroll.min.js', ['version' => '2', 'deps' => 'jquery', 'defer' => true]);
		Script::register( 'jquery.scrollto', 'jquery.scrollTo.min.js', [ 'version' => '2.1', 'deps' => 'jquery', 'defer' => true ] );
		
		Script::register( 'history.js', 'scripts/bundled/html4+html5/jquery.history.js', [ 'version' => '1.8', 'package' => 'historyjs', 'deps' => 'jquery' ] );
	}

}

add_filter( 'script_loader_tag', array( Script::class, 'manage_script' ), 10, 3 );
//add_filter( 'script_loader_src', array( Script::class, 'manage_src' ), 100, 2 );


add_action('wp_print_scripts', function(){ ?>
<script>
loadjs=function(){var l=function(){},c={},f={},u={};function o(e,n){if(e){var t=u[e];if(f[e]=n,t)for(;t.length;)t[0](e,n),t.splice(0,1)}}function s(e,n){e.call&&(e={success:e}),n.length?(e.error||l)(n):(e.success||l)(e)}function h(t,r,i,c){var o,s,e=document,n=i.async,f=(i.numRetries||0)+1,u=i.before||l,a=t.replace(/^(css|img)!/,"");c=c||0,/(^css!|\.css$)/.test(t)?(o=!0,(s=e.createElement("link")).rel="stylesheet",s.href=a):/(^img!|\.(png|gif|jpg|svg)$)/.test(t)?(s=e.createElement("img")).src=a:((s=e.createElement("script")).src=t,s.async=void 0===n||n),!(s.onload=s.onerror=s.onbeforeload=function(e){var n=e.type[0];if(o&&"hideFocus"in s)try{s.sheet.cssText.length||(n="e")}catch(e){18!=e.code&&(n="e")}if("e"==n&&(c+=1)<f)return h(t,r,i,c);r(t,n,e.defaultPrevented)})!==u(t,s)&&e.head.appendChild(s)}function t(e,n,t){var r,i;if(n&&n.trim&&(r=n),i=(r?t:n)||{},r){if(r in c)throw"LoadJS";c[r]=!0}!function(e,r,n){var t,i,c=(e=e.push?e:[e]).length,o=c,s=[];for(t=function(e,n,t){if("e"==n&&s.push(e),"b"==n){if(!t)return;s.push(e)}--c||r(s)},i=0;i<o;i++)h(e[i],t,n)}(e,function(e){s(i,e),o(r,e)},i)}return t.ready=function(e,n){return function(e,t){e=e.push?e:[e];var n,r,i,c=[],o=e.length,s=o;for(n=function(e,n){n.length&&c.push(e),--s||t(c)};o--;)r=e[o],(i=f[r])?n(r,i):(u[r]=u[r]||[]).push(n)}(e,function(e){s(n,e)}),t},t.done=function(e){o(e,[])},t.reset=function(){c={},f={},u={}},t.isDefined=function(e){return e in c},t}();	
</script>
<?php });