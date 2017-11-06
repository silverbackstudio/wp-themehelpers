<?php

namespace Svbk\WP\Helpers\Theme;

use Svbk\WP\Helpers\Theme\Script;
use Svbk\WP\Helpers\Theme\Style;

class Setup {

	public static $gtm_noscript_printed = false;

	public function __construct() {
		$this->register_hooks();
		$this->register_shortcodes();
	}

	public static function run() {
		return new self();
	}

	public function register_hooks() {

		add_action( 'after_setup_theme', array( $this, 'load_texdomain' ) );

		add_action( 'wp_head', array( $this, 'add_analytics' ), 1 );
		add_action( 'after_body_tag', array( $this, 'print_analytics_noscript' ) );
		add_action( 'wp_footer', array( $this, 'print_analytics_noscript' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 8 );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_fonts' ), 8 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_google_maps' ), 8 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_policies' ), 8 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_instagram' ), 8 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_icons' ), 8 );

		add_filter( 'bloginfo', array( $this, 'extend_bloginfo' ), 9, 2 );
		add_filter( 'acf/fields/google_map/api', array( $this, 'acf_maps_api' ) );
	}

	public function register_shortcodes() {
		add_shortcode( 'bloginfo', array( $this, 'bloginfo_shortcode' ) );
		add_shortcode( 'privacy-link', array( $this, 'get_privacy_link' ) );
		add_shortcode( 'cookie-policy-link', array( $this, 'get_cookie_policy_link' ) );
	}

	public function register_scripts() {
		
		Script::register( 'waypoints', 'lib/jquery.waypoints.js', [ 'version' => '4', 'deps' => 'jquery' ] );
		Script::register( 'waypoints-sticky', 'lib/shortcuts/sticky.js', [ 'version' => '4', 'deps' => ['jquery', 'waypoints'], 'package' => 'waypoints' ] );
		Script::register( 'jquery.collapse', 'src/jquery.collapse.js', [ 'version' => '1', 'deps' => 'jquery', 'package' => 'jquery-collapse' ] );
		
		Script::register( 'flickity', 'dist/flickity.pkgd.js', [ 'version' => '2'] );
		Style::register( 'flickity',  'dist/flickity.css' , [ 'version' => '2' ] );
		
		Script::register( 'masonry', 'masonry.pkgd.js', ['version' => '4', 'package' => 'masonry-layout' ] );
		Script::register( 'jquery.localscroll', 'jquery.localScrolljs', ['version' => '2', 'deps' => 'jquery']);
		Script::register( 'jquery.scrollto', 'jquery.scrollTo.js', [ 'version' => '2.1', 'deps' => 'jquery' ] );

		Script::enqueue( 'object-fit-images', 'dist/ofi.js' , [ 'version' => '3' ] );
		wp_add_inline_script( 'object-fit-images', 'objectFitImages();' );

		Script::register( 'history.js', 'scripts/bundled/html4+html5/jquery.history.js', [ 'version' => '1.8', 'package' => 'historyjs', 'deps' => 'jquery' ] );
	
	}

	public function load_texdomain() {
		load_textdomain( 'svbk-helpers', dirname( dirname( __DIR__ ) ) . '/languages/svbk-helpers-' . get_locale() . '.mo' );
	}

	protected function conf( $group, $param = null, $default = null ) {
		return Config::get( $group, $param, $default );
	}

	public static function declareAjaxurl( $script ) {
		wp_localize_script( $script, 'ajaxurl', admin_url( 'admin-ajax.php' ) );
	}

	public function add_google_maps() {

		if ( $this->conf( 'googlemaps' ) ) {

			$script_options = array();

			$script_options['key'] = $this->conf( 'googlemaps', 'key' );
			$script_options['library'] = $this->conf( 'googlemaps', 'library' );
			$script_options['callback'] = $this->conf( 'googlemaps', 'callback', 'initGMaps' );

			$script_options = array_filter( $script_options );

			$script = http_build_query( $script_options );

			wp_enqueue_script( 'googlemaps', 'https://maps.googleapis.com/maps/api/js?' . $script, null, null, true );
			Script::set_async( 'googlemaps' );

			$defaultOptions = array();

			if ( $this->conf( 'googlemaps', 'mapOptions' ) ) {
				wp_localize_script( 'googlemaps', 'googleMapsOptions', $this->conf( 'googlemaps', 'mapOptions' ) );
			}

			if ( $this->conf( 'googlemaps', 'markerOptions' ) ) {
				wp_localize_script( 'googlemaps', 'googleMapsMarkerOptions', $this->conf( 'googlemaps', 'markerOptions' ) );
			}

			if ( ! $this->conf( 'googlemaps', 'callback', false ) ) {

				wp_add_inline_script('googlemaps',
					'function initGMaps() { 
        	        var triggerGmaps = function(){
        	        
                        var event, eventName = \'gmaps-ready\';
                        
                        if (window.CustomEvent) {
                            event = new CustomEvent(eventName);
                        } else {
                            event = document.createEvent(\'CustomEvent\');
                            event.initCustomEvent(eventName, true, true);
                        }
                        
                        var containers = document.getElementsByClassName(\'gmap-container\');
                        for (var i = 0, len = containers.length; i < len; i++) {
                            containers[i].dispatchEvent(event);
                        }
                        
                        document.body.dispatchEvent(event);
        	        };
        	
                    document.addEventListener(\'DOMContentLoaded\', triggerGmaps);
                    triggerGmaps();
            	}',
				'before');
			}
		}// End if().

	}

	public function acf_maps_api( $api ) {

		if ( $this->conf( 'googlemaps', 'key' ) ) {
			$api['key'] = $this->conf( 'googlemaps', 'key' );
		}

		if ( $this->conf( 'googlemaps', 'client' ) ) {
			 $api['client'] = $this->conf( 'googlemaps', 'client' );
		}

		if ( $this->conf( 'googlemaps', 'libraries' ) ) {
			 $api['libraries'] = $this->conf( 'googlemaps', 'libraries' );
		}

		if ( $this->conf( 'googlemaps', 'ver' ) ) {
			 $api['ver'] = $this->conf( 'googlemaps', 'ver' );
		}

		if ( $this->conf( 'googlemaps', 'callback' ) ) {
			 $api['callback'] = $this->conf( 'googlemaps', 'callback' );
		}

		return $api;
	}

	public function add_instagram() {

		if ( apply_filters( 'show_instagram_footer', is_front_page() ) && $this->conf( 'instagram' ) ) {
			wp_enqueue_cdn_script( 'instafeed.js', 'instafeed.min.js', null, '1.4', true );
			wp_localize_script( 'instafeed.js', 'instafeedOptions', $this->conf( 'instagram' ) );
		}

	}

	public function add_fonts() {

		if ( $this->conf( 'google-fonts', 'fonts' ) ) {
			wp_enqueue_style( 'google-font', 'https://fonts.googleapis.com/css?family=' . $this->conf( 'google-fonts', 'fonts' ) );
		}

		if ( $this->conf( 'fonts_com', 'api_key' ) ) {
			wp_enqueue_style( 'theme-fonts', '//fast.fonts.net/cssapi/' . $this->conf( 'fonts_com', 'api_key' ) . '.css' );
		}

	}

	public function add_policies() {

		if ( $this->conf( 'iubenda' ) ) {

			if ( $this->conf( 'iubenda','siteId' ) ) {
				wp_enqueue_script( 'iubenda', '//cdn.iubenda.com/iubenda.js', null, null, true );
			}

			if ( $this->conf( 'iubenda','cookiePolicyId' ) ) {

				wp_enqueue_script( 'iubenda-cookie', '//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js' );
				Script::set_async( 'iubenda-cookie' );
				Script::set_defer( 'iubenda-cookie' );

				$code = 'var _iub = _iub || [];' . PHP_EOL;
				$config = $this->conf( 'iubenda' );
				$config['lang'] = substr( get_bloginfo( 'language' ), 0, 2 );

				$code .= '_iub.csConfiguration = ';
				$code .= json_encode( $config );
				$code .= "		  
			        _iub.csConfiguration.callback =  {
			        onConsentGiven: function(){
			                dataLayer.push({'event': 'iubenda_consent_given'});
			            }
			        }
			        ";

				wp_add_inline_script( 'iubenda-cookie', $code,'before' );

			}
		}

	}

	public function add_icons() {

		$path = $this->conf( 'icons', 'path' );

		if ( $path && file_exists( trailingslashit( get_template_directory() ) . $path ) ) {
			wp_enqueue_style( 'theme-icons', get_theme_file_uri( $path ) );
		}

	}

	public function add_analytics() {

		if ( $this->conf( 'google-tag-manager', 'id' ) ) {
			$dataLayer = $this->conf( 'google-tag-manager', 'dataLayer' ) ?: 'dataLayer';
		?>
			<!-- Google Tag Manager -->
			<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','<?php echo $dataLayer; ?>','<?php echo $this->conf( 'google-tag-manager', 'id' ); ?>');</script>
			<!-- End Google Tag Manager -->
			<?php

		}

	}

	public function print_analytics_noscript() {

		if ( ! self::$gtm_noscript_printed ) {
			self::$gtm_noscript_printed = true;
		} else {
			return;
		}

		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $this->conf( 'google-tag-manager', 'id' ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}

	public function extend_bloginfo( $output, $show ) {

		if ( substr( $show, 0, 8 ) == 'contact_' ) {
			$show = substr( $show, 8 );
			$output = $this->conf( 'contact', $show ) ?: '';
		}

		return $output;
	}

	public function bloginfo_shortcode( $attrs ) {
		return get_bloginfo( $attrs['value'], 'display' );
	}

	public function get_privacy_link( $attr, $link_name = 'Privacy Policy', $shortcode_tag ) {

		$attr = shortcode_atts(
			array(
				'style' => 'nostyle',
				'no_brand' => '1',
			),
		$attr, $shortcode_tag );

		return '<a href="//www.iubenda.com/privacy-policy/' . esc_attr( $this->conf( 'iubenda','privacyPolicyId' ) ) . '" class="iubenda-'. esc_attr( $attr['style'] ) ." ". ( $attr['no_brand'] ? "no-brand" : "" ) ." ".'iubenda-embed" title="' . esc_attr( $link_name ) . '">' . $link_name . '</a>';
	}
	
    public function get_cookie_policy_link( $attr, $link_name = 'Cookie Policy', $shortcode_tag ) {    

		$attr = shortcode_atts(
			array(
				'style' => 'nostyle',
				'no_brand' => '1',
			),
		$attr, $shortcode_tag );

		return '<a href="//www.iubenda.com/privacy-policy/' . esc_attr ( $this->conf( 'iubenda','cookiePolicyId' ) )."/cookie-policy?ifr=true&amp;height=800" .'"class="iubenda-'. esc_attr( $attr['style'] ) ." ". ( $attr['no_brand'] ? "no-brand" : "" ) ." ".'iubenda-embed" title="' . esc_attr( $link_name ) . '">' . $link_name . '</a>';
	}
	
}
