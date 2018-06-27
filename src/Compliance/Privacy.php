<?php

namespace Svbk\WP\Helpers\Compliance;

/**
 * Policy links helper class
 * 
 * @since: 4.1.10 
 */
class Privacy {

		public static function register_shortcodes(){
			add_shortcode( 'privacy-policy-link', array( __CLASS__, 'shortcode' ) );
			add_shortcode( 'privacy-controller-name', array( __CLASS__, 'shortcode' ) );
			add_shortcode( 'cookie-policy-link', array( __CLASS__, 'shortcode' ) );
		}

		public static function shortcode( $atts, $content, $shortcode_id = '' ){
			
		   $attr = shortcode_atts( array(
		        'label' => '',
		        'before' => '',
		        'after' => '',
		    ), $atts );
		   
			switch (  $shortcode_id ) { 
		    	case 'policy-link':
		    	case 'privacy-policy-link':
		    		return self::privacy_policy_link( $attr['before'], $attr['after'], $attr );
		    		break;
		    	case 'cookie-policy-link': 
		    		return self::cookie_policy_link( $attr['before'], $attr['after'], $attr );
		    		break;
		    	case 'privacy-controller-name': 
		    		return self::controller_name( $attr['before'], $attr['after'], $attr );
		    		break;		    		
		    }		    
		    
		    return '';
		    
		}

		/**
		 * Returns the privacy controller name with formatting, when applicable.
		 *
		 * @since 4.1.10
		 *
		 * @param string $before Optional. Display before privacy policy link. Default empty.
		 * @param string $after  Optional. Display after privacy policy link. Default empty.
		 *
		 * @return string Markup for the link and surrounding elements. Empty string if it
		 *                doesn't exist.
		 */
		public static function controller_name( $before = '', $after = '', $attr = array() ) {
			$controller_name =  apply_filters( 'privacy_policy_controller_name', get_bloginfo('name') );
			
			if ( $controller_name ) {
				return $before . $controller_name . $after;
			}
			
			return '';
		}

		/**
		 * Returns the privacy policy link with formatting, when applicable.
		 *
		 * @since 4.1.10
		 *
		 * @param string $before Optional. Display before privacy policy link. Default empty.
		 * @param string $after  Optional. Display after privacy policy link. Default empty.
		 *
		 * @return string Markup for the link and surrounding elements. Empty string if it
		 *                doesn't exist.
		 */
		public static function privacy_policy_link( $before = '', $after = '', $attr = array() ) {
			
			if ( function_exists('get_the_privacy_policy_link') ) {
				return get_the_privacy_policy_link( $before, $after);
			}
			
			$link               = '';
			$privacy_policy_url = self::privacy_policy_url();
			
			if ( $privacy_policy_url ) {
				$link = sprintf(
					'<a class="privacy-policy-link" href="%s">%s</a>',
					esc_url( $privacy_policy_url ),
					__( 'Privacy Policy', 'svbk-helpers' )
				);
			}

			/**
			 * Filters the cookie policy link.
			 *
			 * @since 4.1.10
			 *
			 * @param string $link               The cookie policy link. Empty string if it
			 *                                   doesn't exist.
			 * @param string $privacy_policy_url The URL of the cookie policy. Empty string
			 *                                   if it doesn't exist.
			 */
			$link = apply_filters( 'the_cookie_policy_link', $link, $privacy_policy_url );
		
			if ( $link ) {
				return $before . $link . $after;
			}
		
			return '';
		}	

		/**
		 * Returns the cookie policy link with formatting, when applicable.
		 *
		 * @since 4.1.10
		 *
		 * @param string $before Optional. Display before cookie policy link. Default empty.
		 * @param string $after  Optional. Display after cookie policy link. Default empty.
		 *
		 * @return string Markup for the link and surrounding elements. Empty string if it
		 *                doesn't exist.
		 */
		public static function cookie_policy_link( $before = '', $after = '', $attr = array() ) {
			$link               = '';
			$cookie_policy_url = self::cookie_policy_url();
		
			if ( $cookie_policy_url ) {
				$link = sprintf(
					'<a class="cookie-policy-link" href="%s">%s</a>',
					esc_url( $privacy_policy_url ),
					__( 'Cookie Policy', 'svbk-helpers' )
				);
			}
		
			/**
			 * Filters the cookie policy link.
			 *
			 * @since 4.1.10
			 *
			 * @param string $link               The cookie policy link. Empty string if it
			 *                                   doesn't exist.
			 * @param string $cookie_policy_url The URL of the cookie policy. Empty string
			 *                                   if it doesn't exist.
			 */
			$link = apply_filters( 'the_cookie_policy_link', $link, $cookie_policy_url );
		
			if ( $link ) {
				return $before . $link . $after;
			}
		
			return '';
		}	

		/**
		 * Retrieves the URL to the privacy policy page.
		 *
		 * @since 4.1.10
		 *
		 * @return string The URL to the cookie policy page. Empty string if it doesn't exist.
		 */
		public static function privacy_policy_url( $before = '', $after = '', $attr = array() ) {
			if( function_exists( 'get_privacy_policy_url' ) ) {
				return get_privacy_policy_url( $before, $after );
			} else {
				return apply_filters( 'privacy_policy_url', '', 0 );
			}
		}
		
		/**
		 * Retrieves the URL to the cookie policy page.
		 *
		 * @since 4.1.10
		 *
		 * @return string The URL to the cookie policy page. Empty string if it doesn't exist.
		 */
		public static function cookie_policy_url() {
			$url            = '';
			$policy_page_id = (int) get_option( 'wp_page_for_cookie_policy' );
		
			if ( ! empty( $policy_page_id ) && get_post_status( $policy_page_id ) === 'publish' ) {
				$url = (string) get_permalink( $policy_page_id );
			}
		
			/**
			 * Filters the URL of the cookie policy page.
			 *
			 * @since 4.1.10
			 *
			 * @param string $url            The URL to the cookie policy page. Empty string
			 *                               if it doesn't exist.
			 * @param int    $policy_page_id The ID of cookie policy page.
			 */
			return apply_filters( 'cookie_policy_url', $url, $policy_page_id );
		}
		
}