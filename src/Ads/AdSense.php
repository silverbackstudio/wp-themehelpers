<?php 

namespace Svbk\WP\Helpers\Ads;

class AdSense {

    public static $script_added = false;
    public $google_ad_client = '';
    
    public function __construct( $google_ad_client ){
        $this->google_ad_client = $google_ad_client;
    }
    
    public static function adunit_sizes(){
        return array(
		    'auto' => __('Adaptive', 'svbk-helpers'),
		    '728x90' => __('Leaderboard', 'svbk-helpers'),
		    '336x280' => __('Large Rectangle', 'svbk-helpers'),
		    '300x600' => __('Large SkyScraper', 'svbk-helpers'),        
        );
    }
    
    public function adunit_code( $ad_slot, $ad_size = 'auto' ){
        
        if( ! $this->google_ad_client || ! $ad_slot ) {
            return;
        }           
        
        if( 'auto' == $ad_size  ){
            $style = 'display:block;';
            $format = 'data-ad-format="auto"';
        } else {
            $dims = explode('x', $ad_size);
            $style = 'display:inline-block;width:' . $dims[0] . 'px;height:' . $dims[1] . 'px;';
            $format = '';
        }
            
        $output = '<ins class="adsbygoogle" style="' . $style . '" data-ad-client="' . $this->google_ad_client . '" data-ad-slot="' . $ad_slot . '" ' . $format . '></ins>';
        $output .= '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';        
        
        return $output;
        
    }
    
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