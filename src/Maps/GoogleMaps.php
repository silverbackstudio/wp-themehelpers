<?php

namespace Svbk\WP\Helpers\Maps;

use Svbk\WP\Helpers\Utils\ObjectUtils;
use Svbk\WP\Helpers\Assets\Script;

class GoogleMaps {

	public $key;
	public $libraries = array();
	public $callback;
	public $ver;
	public $options = array();
	
	public $mapOptions = array();
	public $markerOptions = array();

	public function __construct( $properties = array() ) {
		ObjectUtils::configure( $this, $properties );
		
		$this->enqueue_script();
		add_filter( 'acf/fields/google_map/api', array( $this, 'acf_maps_api' ) );
	}

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

	public function enqueue_script() {

		$script_options = $this->options;

		$script_options['key'] = $this->key;
		$script_options['libraries'] = $this->libraries;   // modified

		if ( $this->callback !== false ) {
			$script_options['callback'] = $this->callback ?: 'initGMaps';		
		}

		$script_options = array_filter( $script_options );

		$script = http_build_query( $script_options );

		Script::enqueue( 'googlemaps', 'https://maps.googleapis.com/maps/api/js?' . $script, array( 'source' => false, 'async' => true, 'defer' => true ) );


		if ( $this->mapOptions ) {
			wp_localize_script( 'googlemaps', 'googleMapsOptions', $this->mapOptions );
		}

		if ( $this->markerOptions ) {
			wp_localize_script( 'googlemaps', 'googleMapsMarkerOptions', $this->markerOptions );
		}

		if ( ! $this->callback ) {

			wp_add_inline_script('googlemaps',
			'function initGMaps() { ' .
		    '	var triggerGmaps = function(){' .
		    
		    '        var event, eventName = \'gmaps-ready\';' .
		    
		    '        if (window.CustomEvent) { '.
		    '            event = new CustomEvent(eventName); '.
		    '        } else { ' .
		    '            event = document.createEvent(\'CustomEvent\'); ' .
		    '            event.initCustomEvent(eventName, true, true); ' .
		    '        } '.
		    
		    '        var containers = document.getElementsByClassName(\'gmap-container\'); ' .
		    '        for (var i = 0, len = containers.length; i < len; i++) { ' .
		    '            containers[i].dispatchEvent(event); ' .
		    '        } '.
		
		    '        document.body.dispatchEvent(event); ' .
		    '    }; ' .
		    
		    '    document.addEventListener(\'DOMContentLoaded\', triggerGmaps); ' . 
		    '    triggerGmaps(); ' . 
			'}', 'before'); 
		}


	}

	public function acf_maps_api( $api ) {

		if ( $this->key ) {
			$api['key'] = $this->key;
		}

		return $api;
	}
}
