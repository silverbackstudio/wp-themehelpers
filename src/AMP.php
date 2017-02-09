<?php 

namespace Svbk\WP\Helpers;

class AMP {
 
 
    public static function init(){
        
        add_filter( 'amp_post_template_data', array( __CLASS__, 'set_site_icon_url') );   
        add_filter( 'amp_post_article_footer_meta', array( __CLASS__, 'disable_comments') );   
        
        add_filter( 'amp_post_article_header_meta',  array( __CLASS__, 'remove_author') ); 
        
    }
    
    public static function  set_site_icon_url( $data ) {
        $data[ 'site_icon_url' ] = get_stylesheet_directory_uri() . '/images/amp-site-icon.png';
        $data[ 'featured_image']['caption'] = '';
        return $data;
    } 
    
    
    public static function disable_comments($parts){
        return array_diff($parts, array('meta-comments-link'));
    }
    
    public static function remove_author($parts){
        return array_diff($parts, array('meta-author'));
    }
    


}