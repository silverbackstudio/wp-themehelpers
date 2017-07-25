<?php

namespace Svbk\WP\Helpers\CDN;

class CdnJs {

	static function get_script_url( $package, $files, $version = '1.0.0' ) {
		
		if ( 'latest' === $version ) {
			return null;
		}
		
		return sprintf( '//cdnjs.cloudflare.com/ajax/libs/%1$s/%3$s/%2$s', $package, $files, $version );
	}

}
