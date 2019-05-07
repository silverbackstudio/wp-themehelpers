<?php

namespace Svbk\WP\Helpers\Plugin;

use Svbk\WP\Helpers;

class Facebook {

	public static $config = array(
      "appId"            => '',
      "autoLogAppEvents" => true,
      "xfbml"            => false,
      "version"          => 'v3.2',
      "chat"			 => false
	);
	
	public static function enableComments() {
		self::enableSDK( array( 'xfbml' => true ) );
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

	public static function enableSDK ( $config = null ) {

		if ( null !== $config ) {
			self::$config = array_merge(self::$config, $config);
		}
		
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueSDK' ), 9 );
	}

	public static function enqueueSDK() {
		$lang = str_replace( '-', '_', get_bloginfo('language') );
		$url = 'https://connect.facebook.net/' . esc_attr( $lang ) . '/sdk.js';
		
		if ( !empty( self::$config['chat'] ) ) {
			$url = 'https://connect.facebook.net/' . esc_attr( $lang ) . '/sdk/xfbml.customerchat.js';
		}
		
		Helpers\Assets\Script::enqueue('facebook-jssdk', apply_filters('svbk_facebook_sdk_url', $url, $lang, self::$config ), [ 'version' => self::$config['version'], 'async' => true, 'defer' => true, 'source' => false ] );		
		wp_localize_script('facebook-jssdk', 'facebook_jssdk', self::$config );
		wp_add_inline_script('facebook-jssdk', 'window.fbAsyncInit = function() { FB.init(facebook_jssdk); };', 'before');		
	}

}
