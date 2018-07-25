<?php

namespace Svbk\WP\Helpers\Plugin;

use Svbk\WP\Helpers;

class Facebook {

	public static $appId = "";
	public static $sdkVersion = '2.11';
	
	public static function enableComments() {
		self::enableSDK();
		add_filter( 'comments_template', array( __CLASS__, 'templateFile' ), 11 );
	}

	public static function templateFile( $theme_template ) {
		if ( apply_filters( 'show_facebook_comments', true ) && file_exists( STYLESHEETPATH . '/comments-facebook.php' ) ) {
			return STYLESHEETPATH . '/comments-facebook.php';
		}

		return $theme_template;
	}

	public static function printComments( $count = 5 ) {
		echo '<div class="fb-comments" data-href="' . esc_attr( home_url( add_query_arg( null, null ) ) ) . '" data-numposts="' . esc_attr($count) . '" data-width="100%"></div>';
	}

	public static function enableSDK ($appId = null, $version = null) {
		
		if( $appId ) {
			self::$appId = $appId;
		}
		
		if( $version ) {
			self::$sdkVersion = $version;
		}		
		
		add_action( 'after_body_tag', array( __CLASS__, 'printSDK' ) );
	}

	public static function printSDK() {
	?>
		<script>
		  window.fbAsyncInit = function() {
		    FB.init({
		      appId            : '<?php echo esc_attr(self::$appId); ?>',
		      autoLogAppEvents : true,
		      xfbml            : true,
		      version          : 'v<?php echo self::$sdkVersion; ?>'
		    });
		  };
		
		  (function(d, s, id){
		     var js, fjs = d.getElementsByTagName(s)[0];
		     if (d.getElementById(id)) {return;}
		     js = d.createElement(s); js.id = id;
		     js.src = "https://connect.facebook.net/<?php echo esc_attr( str_replace( '-', '_', get_bloginfo('language') ) )?>/sdk.js";
		     fjs.parentNode.insertBefore(js, fjs);
		   }(document, 'script', 'facebook-jssdk'));
		</script>
	<?php
	}

}
