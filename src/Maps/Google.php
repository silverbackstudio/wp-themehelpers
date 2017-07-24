<?php

namespace Svbk\WP\Helpers\Maps;

class Google {

	public static function url( $query = array() ) {

		$parameters = wp_parse_args(
			$query,
			array(
				'query' => '',
				'api' => 1,
				)
		);

		return 'https://www.google.com/maps/search/?' . http_build_query( $parameters );

	}

}
