<?php

namespace Svbk\WP\Helpers\CDN;

class CdnJs {

	public $package;
	public $version;

	public function __construct($package, $options ){
		$this->package = $package;

		foreach ( $options as $option => $value ) {
			if ( ! property_exists( $this, $option ) ) {
				continue;
			}

			$this->$option = $value;
		}

	}

	public static function get( $package, $options = array() ){
		return new self($package, $options);
	}

	public function url( $file ) {
		
		if ( 'latest' === $this->version ) {
			return null;
		}		
		
		return 'https://cdnjs.cloudflare.com/' . self::path( $file ); 
	}	
	
	public function path( $file ) {
		// ajax/libs/flickity/2.0.6/flickity.css
		return 'ajax/libs/' . $this->package . '/' . $this->version . '/' . $file ;
	}	

}
