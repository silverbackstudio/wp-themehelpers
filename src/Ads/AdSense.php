<?php 

namespace Svbk\WP\Helpers\Ads;

class AdSense {

    public static $script_added = false;
    
    public static function add_scripts( $options = array() ){
        
        // if ( defined('WP_ENV') && ('production' !== WP_ENV) ) {
        //     return;
        // }
        
        add_action( 'wp_head', function() use ( $options ) {
            if( ! self::$script_added ): self::$script_added = true; ?>
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <?php endif; ?>
            <script>
              (adsbygoogle = window.adsbygoogle || []).push(<?php echo json_encode($options); ?>);
            </script>         
        <?php
        } );
    }
    
}