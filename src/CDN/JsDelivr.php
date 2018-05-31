<?php

namespace Svbk\WP\Helpers\CDN;

class JsDelivr {

	public $package;
	public $version = 'latest';
	public $source = 'npm';

	public function __construct($package, $options ){
		$this->package = $package;

		foreach ( $options as $option => $value ) {
			if ( ! property_exists( $this, $option ) ) {
				continue;
			}

			$this->$option = $value;
		}

	}

	static function get( $package, $options = array() ){
		return new self($package, $options);
	}

	function url( $file ) {
		return '//cdn.jsdelivr.net/' . self::path( $file ); 
	}	
	
	function combine( $files ) {

		$combined_files = array();
			
		foreach( $files as $file ) {
			$combined_files[] = self::path( $file );
		}
			
		return '//cdn.jsdelivr.net/combine/' . implode( ',', $combined_files);
	}		

	function path( $file ) {
		
		if( ! defined('SCRIPT_DEBUG') || ! SCRIPT_DEBUG ) {
			$file = preg_replace("/\.js$/i",".min.js", $file);		
			$file = preg_replace("/\.css$/i",".min.css", $file);		
		}
		
		return $this->source . '/' . $this->package . '@' . $this->version . '/' . $file ;
	}

}
