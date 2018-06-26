<?php

/**
 * @package Silverback Helpers
 * @version 1.1
 */

/**
Plugin Name: Silverback Helpers
Plugin URI: https://github.com/silverbackstudio/wp-themehelpers
Description: Silverback's Helper Classes
Author: Silverback Studio
Version: 1.1
Author URI: http://www.silverbackstudio.it/
Text Domain: svbk-helpers
 */


function svbk_helpers_init() {
	load_plugin_textdomain( 'svbk-helpers', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	load_plugin_textdomain( 'svbk-helpers-lists', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'svbk_helpers_init' );
