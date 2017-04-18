<?php 

namespace Svbk\WP\Helpers;

use Jetpack_RelatedPosts;
use WP_Query;

class Jetpack {

    public static function contentShareRemove() {
        add_action( 'loop_start', function(){
            remove_filter( 'the_content', 'sharing_display', 19 );
            if ( class_exists( 'Jetpack_Likes' ) ) {
                remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
            }
        });
    }
    
    public static function excerptShareRemove() {
        add_action( 'loop_start', function(){
            remove_filter( 'the_excerpt', 'sharing_display', 19 );
        });
    }    

    public static function relatedPostsRemove(){
        add_filter( 'wp', function() {
            if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
                $jprp = Jetpack_RelatedPosts::init();
                $callback = array( $jprp, 'filter_add_target_to_dom' );
                remove_filter( 'the_content', $callback, 40 );
            }
        }, 20 );    
    }
    
    
    public static function relatedPostsHtml(){
        if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
            return do_shortcode( '[jetpack-related-posts]' );
        }    
    }
    
    public static function relatedPosts($options = array()){
        
        $options = wp_parse_args($options, array( 'size' => 3 ));
        
        if ( class_exists( 'Jetpack_RelatedPosts' ) && method_exists( 'Jetpack_RelatedPosts', 'init_raw' ) ) {
            return Jetpack_RelatedPosts::init_raw()
                ->set_query_name( 'svbk-jetpack-helper' )
                ->get_for_post_id(
                    get_the_ID(),
                    $options
                );
        }
        
        return array();
    }  
    
    public static function relatedPostsPrint($template_slug, $template_name='',  $options = array()){

        $related =  self::relatedPosts($options);
    
        if(empty($related)){
            return;
        }
        
        $related_ids = wp_list_pluck($related, 'id');
        $related_query = new WP_Query( array('post__in' => $related_ids, 'posts_per_page' => -1 ) );
        
        while ( $related_query->have_posts() ) : $related_query->the_post();
            get_template_part($template_slug, $template_name);
        endwhile;
        
        wp_reset_query();
        wp_reset_postdata();        
    
    }      
    
    
}