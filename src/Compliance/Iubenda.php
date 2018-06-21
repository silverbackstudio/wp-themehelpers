<?php

namespace Svbk\WP\Helpers\Compliance;

use Svbk\WP\Helpers\Theme\Script;

class Iubenda {

	public $config = array();
	public $linkDefaults = array();
	
	public static $instance = null;
	
	public function __construct( $config = array() ) {
		
		$this->config = $config;
		
		add_action( 'init', array( $this, 'init') );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) ); 
		
		add_filter( 'the_privacy_policy_link', array( $this, 'maybe_replace_privacy_policy_link' ), 10, 2 );
		add_filter( 'the_cookie_policy_link', array( $this, 'maybe_replace_cookie_policy_link' ), 10, 2 );
		add_filter( 'privacy_policy_url', array( $this, 'maybe_replace_privacy_policy_url' ), 10 );
		add_filter( 'cookie_policy_url', array( $this, 'maybe_replace_cookie_policy_url' ), 10 );
	}
	
	public function init() {
		
		$defaults = array(
	        "siteId" => '',
	        "cookiePolicyId" => '',
	        'privacyPolicyId' => '',
	        "consentOnButton" => false,
	        "consentOnScroll" => false,		        
			'lang' => substr( get_bloginfo( 'language' ), 0, 2 ),
	        "banner" => [
	            'slideDown'=> false,
	            'applyStyles' => false,            
	            "prependOnBody" => false,
	            "textColor" => "#dadada",
	            "backgroundColor" => "#5A5A5A",
	            "cookiePolicyLinkCaption" => __( "Cookie Policy", 'svbk-helpers' ),
	            "content" => __( "<p>Information</p><p>This site uses third party cookies necessary for the operation of the site and profiling cookies that keep track of the pages visits to understand your interests and provide customized informations. If you want to learn more or deny consent to all or some cookies, see %{cookie_policy_link}.</p><p> By accepting or closing this banner, you consent to use cookies including profiling. </p>", 'svbk-helpers' ) . '<a class="iubenda-cs-close-btn" href="javascript:void(0)">' . __( "Accept", 'svbk-helpers' ) . '</a>'	        
           ],
		);

		$this->config = array_replace_recursive ( $defaults, $this->config );	

		$this->linkDefaults = array(
			'style' => 'nostyle',
			'remove_branding' => true,
			'class' => 'iubenda-embed',
			'type' => 'privacy-policy',
		);		
		
	}
	
	public static function setConfig( $config ){
		
        if (null === self::$instance) {
            self::$instance = new static( $config );
        }

        return self::$instance;
		
	}	

	public static function getInstance( $config = array() ){
		
        if (null === self::$instance) {
            self::$instance = new static( $config );
        }

        return self::$instance;
	}
	
	public static function add_cs_script_block( $tag ){
		return str_replace( 'src', 'class="_iub_cs_activate" type="text/plain" data-suppressedsrc', $tag );
	}
	
	public function add_scripts( $config ) {

		if ( !empty( $this->config['siteId'] ) ) {
			Script::enqueue( 'iubenda', '//cdn.iubenda.com/iubenda.js', array( 'cdn_class' => false ) );
		}

		if ( !empty( $this->config['cookiePolicyId'] ) ) {

			Script::enqueue( 'iubenda-cookie', '//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js', array( 'async' => true, 'defer' => true, 'cdn_class' => false ) );

			$code = 'var _iub = _iub || [];' . PHP_EOL;

			$code .= '_iub.csConfiguration = ';
			$code .= json_encode( $this->config );
			$code .= "		  
		        _iub.csConfiguration.callback =  {
		        onConsentGiven: function(){
		                dataLayer.push({'event': 'iubenda_consent_given'});
		            }
		        }
		        ";

			wp_add_inline_script( 'iubenda-cookie', $code, 'before' );

		}

	}
	
	public function getPolicy( $params = array() ){
	   
		$defaults = array(
			'policy_id'	=> $this->config['cookiePolicyId'],
			'policy_type' => 'privacy-policy',
			'remove_styles' => false,
		);
	   
		$params = wp_parse_args($params, $defaults);
		
	    $cache_key = 'iubenda_policy';
	    
	    $url = 'https://www.iubenda.com/api/privacy-policy/';
	    
        $url .= $params['policy_id'];
        $cache_key .= '_' . $params['policy_id'];
        
        if( $params['policy_type'] && ( 'privacy-policy' !== $params['policy_type'] ) ) {
            $url .= '/' . $params['policy_type'];
            $cache_key .= '_' . str_replace('-', '_', $params['policy_type'] );
        }
        
        if( $params['remove_styles'] ) {
            $url .= '/no-markup';
            $cache_key .= '_no_markup';
        }

	    $policy_html = get_transient( $cache_key );

        if ( ! $policy_html ) {
            // It wasn't there, so regenerate the data and save the transient
    	    $request = wp_remote_get( $url );
    	    
            if( !is_wp_error( $request ) ) {
                $response =  wp_remote_retrieve_body( $request );
                $response = json_decode( $response, true );
            }
            
            if( !empty($response['content']) ) {
                $policy_html = $response['content'];
                set_transient( $cache_key, $policy_html, 12 * HOUR_IN_SECONDS );
            }
        }	    

        return $policy_html;
	}
	
	public function maybe_replace_privacy_policy_url( $policy_url ) {
		
		if ( ! $policy_url ) {
			$policy_url = $this->getPolicyUrl();
		}
		
		return $policy_url;
	}
	
	public function maybe_replace_cookie_policy_url( $policy_url ) {
		
		if ( ! $policy_url ) {
			$policy_url = $this->getPolicyUrl( array( 'type' => 'cookie-policy' ) );
		}
		
		return $policy_url;
	}	

	public function maybe_replace_privacy_policy_link( $policy_link, $policy_url ) {
		
		// if we are using iubenda policy, apply the styles
		if ( $policy_url === $this->getPolicyUrl() ) {
			$policy_link = $this->getPolicyLink( __( 'Privacy Policy', 'svbk-helpers') );
		}

		return $policy_link;
	}
	
	public function maybe_replace_cookie_policy_link( $policy_link, $policy_url ) {
		
		// if we are using iubenda policy, apply the styles
		if ( $policy_url === $this->getPolicyUrl( array( 'type' => 'cookie-policy' ) ) ) {
			$policy_link = $this->getPolicyLink( __( 'Cookie Policy', 'svbk-helpers'), array( 'type' => 'cookie-policy' ) );
		}

		return $policy_link;
	}
		
	
	public function getPolicyLink( $link_name, $params = array() ){
	   
		$params = wp_parse_args($params, $this->linkDefaults);
        $params['class'] .= ' ' . $params['type'] . '-link iubenda-' . $params['style'];
        
        if( $params['remove_branding'] ) {
            $params['class'] .= ' no-brand';
        }

  		return '<a href="' . $this->getPolicyUrl( $params ) . '" class="' . esc_attr( $params['class'] ) . '" title="' . esc_attr( $link_name ) . '">' . $link_name . '</a>';

	}	
	
	public function getPolicyUrl( $params = array() ){
	   
		$defaults = array(
			'policy_id'	=> $this->config['cookiePolicyId'],
			'type' => 'privacy-policy',
		);
	   
		$params = wp_parse_args($params, $defaults);
		
	    $url = 'https://www.iubenda.com/privacy-policy/';
	    
        $url .= $params['policy_id'];

        if( $params['type'] && ( 'privacy-policy' !== $params['type'] ) ) {
            $url .= '/' . $params['type'];
        }

		return $url;
	}

}

add_filter( 'svbk_script_setup_tracking', array( Iubenda::class, 'add_cs_script_block' ) );