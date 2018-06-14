<?php

namespace Svbk\WP\Helpers\Menu;

use Svbk\WP\Helpers\Utils\ObjectUtils as Utils;

class Item {
	
	public $object_id = '';
	public $title = '';
	public $url	= '';
	public $description = 'description';
	public $db_id = 0;
	public $object	= 'custom';
	public $menu_item_parent = 0;
	public $type = 'custom';
	public $target	= '';
	public $attr_title	= '';
	public $classes = array();
	
	public $xfn = '';
	
	public $frontend_url = '';
	
	public function __construct( $object_id, $title, $attr = array() ) {
		
		$this->object_id = esc_attr( $object_id );
		$this->title = esc_attr( $title );
		$this->url  = esc_attr( $object_id ) ;
		$this->type  = esc_attr( $object_id ) ;
		
		Utils::configure( $this, $attr );
	}
	
	public function frontend_url() { 
		return $this->frontend_url;
	}
	
}
