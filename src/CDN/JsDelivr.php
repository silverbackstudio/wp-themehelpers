<?php

namespace Svbk\WP\Helpers\CDN;

class JsDelivr {

	static function get_script_url( $package, $files, $version = 'latest' ) {

		if ( is_array( $files ) ) {
			$template = '//cdn.jsdelivr.net/g/%1$s@%3$s(%2$s)';
			$files = implode( '+', $files );
		} else {
			$template = '//cdn.jsdelivr.net/%1$s/%3$s/%2$s';
		}

		return sprintf( $template, $package, $files, $version );
	}

}
