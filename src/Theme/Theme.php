<?php

namespace Svbk\WP\Helpers\Theme;

use Svbk\WP\Helpers\Theme\CdnScripts;

class Theme {
    
    public static $config;
    public $async_scripts = ['google-maps', 'iubenda-cookie', 'google-tag-manager'];
    public $defer_scripts = ['iubenda-cookie', 'google-tag-manager'];
    protected $queued_script_methods = [];
    
    public static $gtm_noscript_printed = false;
    
    function __construct($config_file='config.php'){
        self::$config = $this->load_config($config_file);
         
        add_action('wp_enqueue_scripts', array($this, 'on_enqueue_scripts'), 8, 2 );
        add_filter('script_loader_tag', array($this, 'add_async_attributes'), 10, 2);  
        add_filter( 'bloginfo', array($this, 'extend_bloginfo'), 9, 2 );
        add_filter('acf/fields/google_map/api', array($this, 'acf_maps_api'));        
        add_shortcode('bloginfo', array($this, 'bloginfo_shortcode') );
        add_shortcode('privacy-link', array($this, 'get_privacy_link') );
        add_action( 'after_setup_theme', array($this, 'load_texdomain') );
        
        add_action('wp_head', array($this, 'add_analytics'), 1);
        add_action('after_body_tag', array($this, 'print_analytics_noscript'));
        add_action('wp_footer', array($this, 'print_analytics_noscript'));        
    }
    
    static function init($config_file='config.php'){
        return new self($config_file);
    }
    
    public function load_texdomain(){
        load_textdomain( 'svbk-helpers', dirname( dirname(__DIR__) ).'/languages/svbk-helpers' . '-' . get_locale() . '.mo'   ); 
    }    
    
    function load_config($config_file='config.php'){
        
        $newconfig = array();
        
        $file = locate_template($config_file, false);
        
        if(file_exists($file)){
            $newconfig = include_once( $file );
        }
        
        return $newconfig;
    }         
    
    
    public static function conf($group, $param=null, $default=null){
        
        if( $group && isset(self::$config[$group]) ){
            
            if($param) {
                return isset(self::$config[$group][$param])?self::$config[$group][$param]:$default;
            } 
            
            return self::$config[$group];
        }
        
        return $default;

    }
    
    function all(){
        
        if(empty(self::$config)){
            return false;
        }
        
        $this->call_on_enqueue_scripts('add_fonts');
        $this->call_on_enqueue_scripts('add_google_maps');
        $this->call_on_enqueue_scripts('add_policies');
        $this->call_on_enqueue_scripts('add_instagram');
        $this->call_on_enqueue_scripts('add_icons');
        
        $this->call_on_enqueue_scripts('register_common_scripts');
        
    }    
    
    public static function declareAjaxurl($script){
        wp_localize_script($script, 'ajaxurl', admin_url( 'admin-ajax.php' ));
    }
    
    
    protected function call_on_enqueue_scripts($method){
        $this->queued_script_methods[] = $method;
    }
    
    public function on_enqueue_scripts(){
        
        foreach($this->queued_script_methods as $method){
            call_user_func(array($this, $method));
        }
        
    }
    
    function add_async_attributes($tag, $handle) {
        
        if (in_array($handle, $this->async_scripts)){
        	$tag = str_replace( ' src', ' async src', $tag );
        } 
        
        if (in_array($handle, $this->defer_scripts)){
        	$tag = str_replace( ' src', ' defer src', $tag );
        }
            
        return $tag;
    }        

    function add_google_maps(){
        
        if( $this->conf('googlemaps') ) {
            
            $script_options = array();
            
            $script_options['key'] = $this->conf('googlemaps', 'key');
            $script_options['library'] = $this->conf('googlemaps', 'library');
            $script_options['callback'] = $this->conf('googlemaps', 'callback', 'initGMaps');
            
            $script_options = array_filter($script_options);

            $script = http_build_query($script_options);
            
    	    wp_enqueue_script('googlemaps', 'https://maps.googleapis.com/maps/api/js?'.$script, null, null, true);

        	$defaultOptions = array();
        	
        	if($this->conf('googlemaps', 'mapOptions')){
        	    wp_localize_script( 'googlemaps', 'googleMapsOptions', $this->conf('googlemaps', 'mapOptions'));
        	}
        	
        	if($this->conf('googlemaps', 'markerOptions')){
        	    wp_localize_script( 'googlemaps', 'googleMapsMarkerOptions', $this->conf('googlemaps', 'markerOptions'));
        	}
            
            if(!$this->conf('googlemaps', 'callback', false)){

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
        	
        }          
        
    }
    
    function acf_maps_api( $api ) {
        
	   	if( $this->conf('googlemaps', 'key') ) {
            $api['key'] = $this->conf('googlemaps', 'key');
        }
        
	   	if( $this->conf('googlemaps', 'client') ) {
            $api['client'] = $this->conf('googlemaps', 'client');
        }    
        
	   	if( $this->conf('googlemaps', 'libraries') ) {
            $api['libraries'] = $this->conf('googlemaps', 'libraries');
        }  
        
	   	if( $this->conf('googlemaps', 'ver') ) {
            $api['ver'] = $this->conf('googlemaps', 'ver');
        }     
        
	   	if( $this->conf('googlemaps', 'callback') ) {
            $api['callback'] = $this->conf('googlemaps', 'callback');
        }             

	   	return $api;
    }
    
    function add_instagram(){

         if(apply_filters('show_instagram_footer', is_front_page()) && $this->conf('instagram')) {
    		wp_enqueue_cdn_script('instafeed.js', 'instafeed.min.js', null, '1.4', true );
    		wp_localize_script( 'instafeed.js', 'instafeedOptions', $this->conf('instagram'));		
    	}
        
    }
    
    function add_fonts(){
    	
    	if($this->conf('google-fonts', 'fonts')){
        	wp_enqueue_style('google-font', 'https://fonts.googleapis.com/css?family='.$this->conf('google-fonts', 'fonts'));
    	}
    	
        if($this->conf('fonts_com', 'api_key')){
        	wp_enqueue_style('theme-fonts', '//fast.fonts.net/cssapi/'.$this->conf('fonts_com', 'api_key').'.css');
        }
        
    }
    
    function add_policies(){
        
        if($this->conf('iubenda')) {
    	        
    	        if($this->conf('iubenda','siteId')){
    	            wp_enqueue_script('iubenda', '//cdn.iubenda.com/iubenda.js', null, null, true);
    	        }
    	    
    	       if($this->conf('iubenda','cookiePolicyId')){
    	        
    	            wp_enqueue_script('iubenda-cookie', '//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js'); 
    	        
    	            $code = "var _iub = _iub || [];".PHP_EOL;
    	            
    	            $config = $this->conf('iubenda');
    	            
    	            $config['lang'] = substr(get_bloginfo('language'), 0, 2);
    	            
    	            $code .= "_iub.csConfiguration = ";
    	            $code .= json_encode($config);
    	            
    	            $code .= "		  
			        _iub.csConfiguration.callback =  {
			        onConsentGiven: function(){
			                dataLayer.push({'event': 'iubenda_consent_given'});
			            }
			        }
			        ";
			    
			    wp_add_inline_script('iubenda-cookie', $code,'before');	 
			    
    	    }
    		
        }        
        
    }
    
    function get_privacy_link($attr, $link_name='Privacy Policy', $shortcode_tag){
        
        $attr = shortcode_atts( array(
    		'no_style' => 1,
    		'no_brand' => 1
    	), $attr, $shortcode_tag );
        
        return '<a href="//www.iubenda.com/privacy-policy/' . $this->conf('iubenda','privacyPolicyId') . '" class="iubenda-nostyle no-brand iubenda-embed" title="'. esc_attr($link_name) .'">'. $link_name .'</a>';
    }
    
    function add_icons(){
        
        $path = $this->conf('icons', 'path');

        if($path && file_exists( trailingslashit(get_template_directory()).$path )){
            wp_enqueue_style('theme-icons', get_theme_file_uri($path) );        
        } 
    
    }
    
    function register_common_scripts(){
        
        $cdn = new CdnScripts;
        
        $cdn->register_script('waypoints', array('jquery.waypoints.min.js', 'shortcuts/sticky.min.js'), array('jquery'), '4', true);
        $cdn->register_script('jquery.collapse', 'jquery.collapse.js', array('jquery'), '1.1');
        $cdn->register_script('flickity', 'flickity.pkgd.min.js', array(), '2.0');
        $cdn->register_style('flickity', 'flickity.min.css', array(), '2.0');
        
    	$cdn->register_script('masonry', 'masonry.pkgd.min.js', array(), '4.1');
    	
    	$cdn->register_script('jquery.localscroll', 'jquery.localScroll.min.js', array('jquery'), '1.4.0');
    	$cdn->register_script('jquery.scrollto', 'jquery.scrollTo.min.js', array('jquery'), '2.1.2');
    	
    	wp_enqueue_script('object-fit-images', 'https://cdnjs.cloudflare.com/ajax/libs/object-fit-images/3.1.3/ofi.min.js', array('jquery'), '3.1.3', true );
    	wp_add_inline_script( 'object-fit-images', 'objectFitImages();' );
    	
    	wp_register_script('history.jquery.js', 'https://cdn.jsdelivr.net/history.js/1.8/history.adapter.jquery.js', array('jquery', 'history.js'), '1.8' );  
    	$cdn->register_script('history.js', 'history.js', array('jquery'), '1.8' );  
    	
    }
  
    function add_analytics(){
        
        if($this->conf('google-tag-manager', 'id')) {
            
            $dataLayer = $this->conf('google-tag-manager', 'dataLayer') ?: 'dataLayer';
            
            ?>
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','<?php echo $dataLayer; ?>','<?php echo $this->conf('google-tag-manager', 'id'); ?>');</script>
            <!-- End Google Tag Manager -->
            <?php
            
        }
        
    }
    
    function print_analytics_noscript(){ 
        
        if( !self::$gtm_noscript_printed ){
           self::$gtm_noscript_printed = true;
        } else {
           return;
        }
    
        ?>
        <!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $this->conf('google-tag-manager', 'id'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager -->
        <?php
    }
    
    function extend_bloginfo($output, $show){
    	
    	if(substr( $show, 0, 8 ) == 'contact_') {
    		
    		$show = substr($show, 8);
    		
    		$output = $this->conf('contact', $show) ?: '';
    		
    	}
    	
    	return $output;
    }
    
    function bloginfo_shortcode($attrs){
	    return get_bloginfo($attrs['value'], 'display');
    }
    
}