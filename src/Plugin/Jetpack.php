<?php

namespace Svbk\WP\Helpers\Plugin;

use Jetpack_RelatedPosts;
use WP_Query;

class Jetpack {

	public static function disableSharingCss() {
		add_filter( 'pre_option_sharedaddy_disable_resources', '__return_true', 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'addSharingJS' ) );
	}

	public static function addSharingJS() {
		wp_enqueue_script( 'sharing-js', WP_SHARING_PLUGIN_URL . 'sharing.js', array(), 4 );

		if ( function_exists( 'get_base_recaptcha_lang_code' ) ) {
			$sharing_js_options = array(
			'lang'   => get_base_recaptcha_lang_code(),
			'counts' => apply_filters( 'jetpack_sharing_counts', true ),
			);
		}
		wp_localize_script( 'sharing-js', 'sharing_js_options', $sharing_js_options );
	}

	public static function contentShareRemove() {
		add_action( 'loop_start', function() {
			remove_filter( 'the_content', 'sharing_display', 19 );
			if ( class_exists( 'Jetpack_Likes' ) ) {
				remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
			}
		});
	}

	public static function excerptShareRemove() {
		add_action( 'loop_start', function() {
			remove_filter( 'the_excerpt', 'sharing_display', 19 );
		});
	}

	public static function relatedPostsRemove() {
		add_filter( 'wp', function() {
			if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
				$jprp = Jetpack_RelatedPosts::init();
				$callback = array( $jprp, 'filter_add_target_to_dom' );
				remove_filter( 'the_content', $callback, 40 );
			}
		}, 20 );
	}


	public static function relatedPostsHtml() {
		if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
			return do_shortcode( '[jetpack-related-posts]' );
		}
	}

	public static function relatedPosts( $options = array() ) {

		$options = shortcode_atts(
			array(
				'size' => 3,
			),
		$options );

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

	public static function relatedPostsPrint( $args = array(), $_deprecated_slug = null, $_deprecated_name = '' ) {

		$defaults = array(
			'template_slug' => 'template-parts/thumb',
			'template_name' => '',
			'before' => '',
			'after' => '',
			'size' => 3,
		);

		// Backward compatibility.
		if ( ! is_array( $args ) ) {

			_deprecated_argument( __FUNCTION__, '3.0.0', 'Using Helpers/Jetpack with deprecated arguments' );

			$defaults['template_slug'] = $args;
			$defaults['template_name'] = $_deprecated_slug;
			$args = $_deprecated_name;
		}

		$params = wp_parse_args( $args, $defaults );

		$related = self::relatedPosts( $params );

		if ( empty( $related ) ) {
			return;
		}

		$related_ids = wp_list_pluck( $related, 'id' );
		$related_query = new WP_Query( array(
			'post__in' => $related_ids,
			'posts_per_page' => -1,
		) );

		echo $params['before'];

		while ( $related_query->have_posts() ) : $related_query->the_post();
			get_template_part( $params['template_slug'], $params['template_name'] ?: get_post_type() );
		endwhile;

		echo $params['after'];

		wp_reset_query();
		wp_reset_postdata();

	}

}
