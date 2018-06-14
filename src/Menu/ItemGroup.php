<?php

namespace Svbk\WP\Helpers\Menu;

class ItemGroup {

	public $id = '';
	public $name = '';
	public $menu_items = array();
	
	public function __construct( $id, $name, $menu_items ) {
		
		$this->id = $id;
		$this->name = $name;

		foreach ( $menu_items as $item ) {
			$item->object = $this->id;
			$this->menu_items[$item->type] = $item;
		}		
		
		add_action( 'admin_init', array( $this, 'add_custom_menu_items' ) );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_nav_menu_item' ) );
	}
	
	function add_custom_menu_items() {
		global $pagenow;

		if( 'nav-menus.php' == $pagenow ) {
			add_meta_box( 'add-' . $this->id . '-links', $this->name, array( $this, 'wp_nav_menu_item_links_meta_box' ), 'nav-menus', 'side', 'low' );
		}
	}	

	function wp_nav_menu_item_links_meta_box( $object ) {
		global $nav_menu_selected_id;

		$walker = new \Walker_Nav_Menu_Checklist( array() );
		?>

		<div id="<?php  echo $this->id; ?>-links" class="<?php  echo $this->id; ?>div">
			<div id="tabs-panel-<?php echo esc_attr($this->id); ?>-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">

				<ul id="<?php echo esc_attr($this->id); ?>-linkschecklist" class="list:<?php echo esc_attr($this->id); ?>-links categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $this->menu_items ), 0, (object) array( 'walker' => $walker ) ); ?>
				</ul>

			</div>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'wp-themehelper' ); ?>" name="add-<?php echo esc_attr($this->id); ?>-links-menu-item" id="submit-<?php echo esc_attr($this->id); ?>-links" />
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * setup_nav_menu_item function.
	 *
	 * Generate the urls for Sensei custom menu items.
	 *
	 * @access public
	 * @param object $item
	 * @return object $item
	 */
	public function setup_nav_menu_item( $item ) {
		global $pagenow, $wp_rewrite;
		
		if( ! isset( $this->menu_items[ $item->type ] ) ) {
			return $item;
		}

		if( 'nav-menus.php' != $pagenow && !defined('DOING_AJAX') ) {
			$item->url = $this->menu_items[ $item->type ]->frontend_url();
		} // endif nav

		$item->type_label = $this->menu_items[ $item->type ]->title;

		return $item;
	} 

}
