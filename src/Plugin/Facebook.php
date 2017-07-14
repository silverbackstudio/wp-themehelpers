<?php 

namespace Svbk\WP\Helpers\Plugin;

use Svbk\WP\Helpers;

class Facebook {

    public static function enableComments(){
        add_filter('comments_template', array(__CLASS__, 'templateFile'), 11);
        add_action('after_body_tag', array(__CLASS__, 'printFbRoot'));
    }    
    
    public static function templateFile( $theme_template ){
        if ( file_exists( STYLESHEETPATH  . '/comments-facebook.php' ) ) {
            return STYLESHEETPATH . '/comments-facebook.php';
        }
        
        return $theme_template;
    }
    
    public static function printComments(){ 
        echo '<div class="fb-comments" data-href="' . esc_attr( home_url( add_query_arg( NULL, NULL ) ) ) .'" data-numposts="5" data-width="100%"></div>';
    }
    
    public static function printFbRoot(){ ?>
        
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v2.9&appId=<?php echo Helpers\Theme\Theme::conf('facebook', 'app_id') ?>"
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
    <?php
    }

}