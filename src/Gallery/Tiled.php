<?php

namespace Svbk\WP\Helpers\Gallery;

// $('.gallery-size-tiled-gallery .gallery-item').each(function(){
// var $img = $(this).find('img');
// var ratio = parseInt($img.attr('width')) / (parseInt($img.attr('height'))*0.7);
// $(this).addClass('picture-ratio-'+Math.round(ratio));
// });
class Tiled {

	static function gallery( $html ) {

		if ( strpos( $html, 'gallery-size-tiled-gallery' ) === false ) {
			return $html;
		}

		// $html = str_replace( 'gallery ', 'gallery', $html );
		return $html;
	}

	public static function register() {

		add_image_size( 'tiled-gallery', 9999, 1040 );
		add_filter( 'image_size_names_choose', array( __CLASS__, 'custom_image_sizes' ) );

	}

	public static function custom_image_sizes( $sizes ) {

		$sizes['tiled-gallery'] = __( 'Tiled Gallery', 'svbk-helpers' );

		return $sizes;
	}

}
