<?php 

namespace Svbk\WP\Helpers\Utils;

class ObjectUtils {
    
	public static function configure( &$target, $properties ) {
		
		foreach ( $properties as $property => $value ) {
			if ( ! property_exists( $target, $property ) ) {
				continue;
			}

			if ( is_array( $target->$property ) ) {
				$target->$property = array_merge( $target->$property, (array)$value );
			} else {
				$target->$property = $value;
			}
		}
		
	}
    
}