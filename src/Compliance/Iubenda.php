<?php

namespace Svbk\WP\Helpers\Compliance;

use Svbk\WP\Helpers\Theme\Script;

class Iubenda {

	public $config = array();	
	
	public static $instance = null;
	
	public function __construct( $config = array() ) {
		
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
	            "innerHtmlCloseBtn" => __( "Accept", 'svbk-helpers' ),
	            "cookiePolicyLinkCaption" => __( "Cookie Policy", 'svbk-helpers' ),
	            "content" => __( "<p>Information</p><p>This site uses third party cookies necessary for the operation of the site and profiling cookies that keep track of the pages visits to understand your interests and provide customized informations. If you want to learn more or deny consent to all or some cookies, see %{cookie_policy_link}.</p><p> By accepting or closing this banner, you consent to use cookies including profiling. </p>", 'svbk-helpers' )
	        ],
		);

		$this->config = wp_parse_args( $config, $defaults );	
		
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) ); 
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

}

add_filter( 'svbk_script_setup_tracking', array( Iubenda::class, 'add_cs_script_block' ) );