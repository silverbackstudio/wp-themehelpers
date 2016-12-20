<?php

namespace Svbk\WP\Helpers;

class Theme {
    
    public $config;
    public $async_scripts = ['google-maps', 'iubenda-cookie', 'google-tag-manager'];
    public $defer_scripts = ['iubenda-cookie', 'google-tag-manager'];
    protected $queued_script_methods = [];
    
    function __construct($config_file='config.php'){
        $this->config = $this->load_config($config_file);
         
        add_action('wp_enqueue_scripts', array($this, 'on_enqueue_scripts'), 11, 2 );
        add_filter('script_loader_tag', array($this, 'add_async_attributes'), 10, 2);  
        add_filter( 'bloginfo', array($this, 'extend_bloginfo'), 9, 2 );
        add_shortcode('bloginfo', array($this, 'bloginfo_shortcode') );
        add_shortcode('privacy-link', array($this, 'get_privacy_link') );
    }
    
    static function init($config_file='config.php'){
        return new self($config_file);
    }
    
    function load_config($config_file='config.php'){
        
        $newconfig = array();
        
        $file = locate_template($config_file, false);
        
        if(file_exists($file)){
            $newconfig = include_once( $file );
        }
        
        return $newconfig;
    }         
    
    
    function conf($group, $param=null){
        
        if( $group && isset($this->config[$group]) ){
            
            if($param) {
                return isset($this->config[$group][$param])?$this->config[$group][$param]:false;
            } 
            
            return $this->config[$group];
        }
        
        return false;

    }
    
    function enqueue_cdn_script($package, $files, $deps = array(), $version='latest', $in_footer=true ){
        $this->register_cdn_script($package, $files, $deps, $version, $in_footer );
        wp_enqueue_script($package);
    }
    
    function register_cdn_script($package, $files, $deps = array(), $version='latest', $in_footer=true ){
	
    	if(is_array($files)){
    		$template = '//cdn.jsdelivr.net/g/%1$s@%3$s(%2$s)';
    		$files = implode('+', $files);
    	} else {
    		$template = '//cdn.jsdelivr.net/%1$s/%3$s/%2$s';		
    	}

	    wp_register_script($package, sprintf($template, $package, $files, $version), $deps, null, $in_footer);
    }
    
    function all(){
        
        if(empty($this->config)){
            return false;
        }
        
        $this->call_on_enqueue_scripts('add_fonts');
        $this->call_on_enqueue_scripts('add_google_maps');
        $this->call_on_enqueue_scripts('add_policies');
        $this->call_on_enqueue_scripts('add_instagram');
        $this->call_on_enqueue_scripts('add_icons');
        $this->call_on_enqueue_scripts('add_analytics');
        $this->call_on_enqueue_scripts('register_common_scripts');
    
    }
    
    protected function call_on_enqueue_scripts($method){
        $this->queued_script_methods[] = $method;
    }
    
    public function on_enqueue_scripts(){
        
        foreach($this->queued_script_methods as $method){
            call_user_func(array($this, $method));
        }
        
    }
    
    function add_google_maps(){
        
        if( $this->conf('googlemaps', 'key') ) {
            
            $query = http_build_query($this->conf('googlemaps'));
            
        	wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?'.$query, null, null, true);
        	
        	$options = array();
        	
        	if($this->conf('googlemaps', 'styles')){
        	    $options['styles'] = json_decode($this->conf('googlemaps', 'styles'));
        	}
        	
        	if($this->conf('googlemaps', 'lat') && $this->conf('googlemaps', 'lng')){
        	    $options['lat'] = $this->conf('googlemaps', 'lat');
        	    $options['lng'] = $this->conf('googlemaps', 'lng');
        	}

        	if($this->conf('googlemaps', 'marker')){
        	    $options['marker'] = $this->conf('googlemaps', 'marker');
        	}
        	
        	if($this->conf('googlemaps', 'zoom')){
        	    $options['zoom'] = $this->conf('googlemaps', 'zoom');
        	}        	
        	
        	wp_localize_script( 'google-maps', 'googleMapsOptions', $options);
        	
        	wp_add_inline_script('google-maps', 
        	'function initGMaps() { jQuery(document).ready(function(){ jQuery(\'.gmap-container\').trigger(\'gmaps-ready\'); }); }',
        	'before');        	
        	
        }          
        
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
        
        if($this->conf('iubenda') && $this->conf('iubenda','siteId')) {
    	    wp_enqueue_script('iubenda', '//cdn.iubenda.com/iubenda.js', null, null, true);
    	    
    	    if($this->conf('iubenda','cookiePolicyId')) {
    	        
    	        wp_enqueue_script('iubenda-cookie', '//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js'); 
    	        
    	        $code = "var _iub = _iub || [];
    			_iub.csConfiguration = {
    			  siteId: '".$this->conf('iubenda','siteId')."',
    			  lang: '".substr(get_bloginfo('language'), 0, 2)."',
	              cookiePolicyId: '".$this->conf('iubenda','cookiePolicyId')."',
			      banner: {
				    slideDown: false,
				    applyStyles: false
				    //content: '".__('<p>Informativa sull&apos;utilizzo dei cookie</p><p>Questo sito o gli strumenti terzi da questo utilizzati si avvalgono di cookie necessari al funzionamento ed utili alle finalità illustrate nella cookie policy. Se vuoi saperne di più o negare il consenso a tutti o ad alcuni cookie, consulta la %{cookie_policy_link}. Chiudendo questo banner, scorrendo questa pagina, cliccando su un link o proseguendo la navigazione in altra maniera, acconsenti all’uso dei cookie.</p>','gazelle')."'
			      },			  
			      callback: {
			        onConsentGiven: function(){
			                dataLayer.push({'event': 'iubenda_consent_given'});
			        }
			      }
			    };";
			    
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
            wp_enqueue_style('theme-icons', trailingslashit(get_template_directory_uri()).$path);        
        } 
    
    }
    
    function register_common_scripts(){
        
        $this->register_cdn_script('waypoints', array('jquery.waypoints.min.js', 'shortcuts/sticky.min.js'), array('jquery'));
        $this->register_cdn_script('jquery.collapse', 'jquery.collapse.js', array('jquery'), '1.1');
        $this->register_cdn_script('flickity', 'flickity.pkgd.min.js', array(), '2.0');
        
    	$this->register_cdn_script('masonry', 'masonry.pkgd.min.js', array(), '4.1');
    	$this->register_cdn_script('history.js', ['history.js', 'history.adapter.jquery.js'] , array('jquery'), '1.8' );  
    	
    }
  
    function add_analytics(){
        
        if($this->conf('google-tag-manager', 'id')) {
            
            $library = '//www.googletagmanager.com/gtm.js?id='.$this->conf('google-tag-manager', 'id');
            
            $dataLayer = $this->conf('google-tag-manager', 'dataLayer') ?: 'dataLayer';
            
            if($dataLayer != 'dataLayer'){
                $library .= '&l='.$dataLayer;
            }
            
            wp_enqueue_script('google-tag-manager', $library, null, null, false);
            
            wp_add_inline_script('google-tag-manager',
            "
            window['".$dataLayer."'] = window['".$dataLayer."'] || [];
            window['".$dataLayer."'].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            ",
            'before');
            
            add_action('wp_footer', array($this, 'add_analytics_noscript'));
        
        }
        
    }
    
    function add_analytics_noscript(){ ?>
        <!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $this->conf('google-tag-manager', 'id'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>        
        <?php
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