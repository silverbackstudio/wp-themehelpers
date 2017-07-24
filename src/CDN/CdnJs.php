<?php 

namespace Svbk\WP\Helpers\CDN;

class CdnJs {

	static function get_script_url( $package, $files, $version = 'latest' ){
		return sprintf('//cdnjs.cloudflare.com/ajax/libs/%1$s/%3$s/%2$s', $package, $files, $version);
	}
    
}