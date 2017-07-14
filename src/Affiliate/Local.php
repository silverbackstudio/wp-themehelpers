<?php 

namespace Svbk\WP\Helpers\Affiliate;

use \Svbk\WP\Helpers;

/**
* Local Affiliate Management class
* 
* @package wp-themehelper
* @subpackage Affiliate
* @author Brando Meniconi <b.meniconi@silverbackstudio.it>
* @since 3.1.15* 
*
*/  

class Local implements AffiliateInterface {
    
    
    const DB_VERSION = '1.0';
    const TABLE_NAME  = 'svbk_affiliates';
    
    /**
    * Class constructor
    *
    * @access public
    * @since 3.1.15* 
    * 
    *
    * @return void
    */  
    public function __construct( ){

        if ( get_site_option( 'svbk_affiliates_db_version' ) != self::DB_VERSION ) {
            self::database_setup();
        }    

    }


    /**
    * Install or update the database
    *
    * @access public
    *  
    * @return void
    */  
    public static function database_setup() {
    	global $wpdb;
    
    	$table_name = $wpdb->prefix . self::TABLE_NAME;
    	
    	$charset_collate = $wpdb->get_charset_collate();
    
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    		email tinytext NOT NULL,
    		affiliate_id int(11) NOT NULL,
    		url varchar(55) DEFAULT '' NOT NULL,
    		PRIMARY KEY  (id)
    		UNIQUE KEY  (email)
    	) $charset_collate;";
    
    	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    	dbDelta( $sql );
    
    	add_option( 'svbk_affiliates_db_version', self::DB_VERSION );
    }

    /**
    * Tracks a sale.
    *
    * @access public
    * 
    * @param float $amount Sale amount in the reference currency
    * @param string $order_num Reference order number
    * @param array $user_data {
    *   An array containing required user data
    *  
    *   @type string $first_name
    *   @type string $last_name
    *   @type string $email 
    *   @type string $ip_address
    *   @type int $id_affiliate
    * }
    * @param string $product_name The product name
    *  
    * @return WP_Error|array The response or WP_Error on failure.
    */       
    public function sale( $amount, $user_data = array(), $order_num = null, $product_name = null ){
        
    }
    
    
    /**
    * Tracks a lead.
    *
    * @access public
    * 
    * @param float $amount Lead amount in the reference currency
    * @param string $order_num Reference order number
    * @param array $user_data {
    *   An array containing required user data
    *  
    * @param array $user_data {
    *   An array containing required user data
    *  
    *   @type string $email The email address that has made the lead
    *   @type int $id_affiliate The affiliate user ID to refer the lead
    * }
    *  
    * 
    * @return (int|false) The number of rows inserted, or false on error.
    */       
    public function lead( $amount = null, $user_data = array() ){
        global $wpdb;
        
        $insert_data = filter_var_array( 
            $user_data,
            array(
                'email' => FILTER_SANITIZE_EMAIL,
                'affiliate_id' => FILTER_SANITIZE_NUMBER_INT,
                'url' => FILTER_SANITIZE_URL
            )
        );
        
        return $wpdb->insert( 
        	$wpdb->prefix . self::TABLE_NAME, 
            $insert_data
        	array( 
        		'%s', 
        		'%d' 
        		'%s' 
        	) 
        );        
    }      

}