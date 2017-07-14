<?php 

namespace Svbk\WP\Helpers\Affiliate;

use \Svbk\WP\Helpers;

/**
* iDevAffiliate connector class
* 
* @package wp-themehelper
* @subpackage Affiliate
* @author Brando Meniconi <b.meniconi@silverbackstudio.it>
* @since 3.1.15* 
*
*/  

class IDevAffiliate implements AffiliateInterface {
    
    public $base_url = '';
    public $secret = '';

    const SALE_PROFILE = 72198;
    const LEAD_PROFILE = 44;
   
    /**
    * Class constructor
    *
    * @access public
    * @since 3.1.15* 
    * 
    * @param string $base_url The iDevAffiliate folder url
    *
    * @return void
    */  
    public function __construct( $base_url ){
        $this->base_url = $base_url;
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
        
        $args = array();
        
        $args['profile'] = self::SALE_PROFILE;
        $args['idev_saleamt'] = $this->formatFloat( $amount ); 
        
        if( ! empty( $order_num ) ) {
            $args['idev_ordernum'] = $order_num; 
        }
        
        $name = '';
        $name .= empty( $user_data['first_name'] ) ? '' : $user_data['first_name'];
        $name .= empty( $user_data['last_name'] ) ? '' : $user_data['last_name'];
 
        $args['ip_address'] = self::getIpAddress( $user_data );
 
        if ( !empty( $name ) ) {
            $args['idev_option_1'] = $name; 
        }
        
        if( !empty( $user_data['email'] ) ) {
            $args['idev_option_2'] = $user_data['email']; 
        }
        
        if( !empty( $product_name ) ) {
            $args['idev_option_3'] = $product_name; 
        }
        
        if( !empty( $user_data['id_affiliate'] ) ) {
            $args['id_affiliate'] = $user_data['id_affiliate']; 
        }        
         
        return wp_remote_get( $this->base_url . '/sale.php', array( 'body' => $args ) );
        
    }
    
    
    /**
    * Returns the affiliate id specified in the URL after the redirect
    *
    * @access public
    *
    * @return int|null The Affiliate ID
    */      
    public static function getAffiliateID(){
        return filter_input( INPUT_GET, 'idev_id', FILTER_VALIDATE_INT, array( 
            'options' => array( 
                'min_range' => 1 
                )
            );
    }
    
    /**
    * Tracks a lead.
    *
    * @access public
    * 
    * @param float $amount Lead amount in the reference currency
    * @param array $user_data {
    *   An array containing required user data
    *  
    * @param array $user_data {
    *   An array containing required user data
    *  
    *   @type string $first_name
    *   @type string $last_name
    *   @type string $email 
    *   @type string $ip_address
    *   @type int $id_affiliate
    * }
    * 
    * @param string $order_num Reference order number* 
    * @param string $product_name The product name
    *  
    * @return WP_Error|array The response or WP_Error on failure.
    */       
    public function lead( $amount = '0.10', $user_data = array(), $order_num = null, $product_name = null ){
        
        $args = array();
        
        $args['profile'] = self::LEAD_PROFILE;
        $args['idev_leadamt'] = $this->formatFloat( $amount ); 
        
        if( ! empty( $order_num ) ) {
            $args['idev_ordernum'] = $order_num; 
        }
        
        $args['ip_address'] = self::getIpAddress( $user_data );   
        
        if( ! empty( $order_num ) ) {
            $args['idev_ordernum'] = $order_num; 
        }
        
        $name = '';
        $name .= empty( $user_data['first_name'] ) ? '' : $user_data['first_name'];
        $name .= empty( $user_data['last_name'] ) ? '' : $user_data['last_name'];
 
        $args['ip_address'] = self::getIpAddress( $user_data );
 
        if ( !empty( $name ) ) {
            $args['idev_option_1'] = $name; 
        }
        
        if( !empty( $user_data['email'] ) ) {
            $args['idev_option_2'] = $user_data['email']; 
        }
        
        if( !empty( $product_name ) ) {
            $args['idev_option_3'] = $product_name; 
        }
        
        if( !empty( $user_data['id_affiliate'] ) ) {
            $args['id_affiliate'] = $user_data['id_affiliate']; 
        }         
        
        return wp_remote_get( untrailingslashit($this->base_url) . '/sale.php', array( 'body' => $args ) );
    }      
    
    /**
    * Get IP address from user data or request
    *
    * @access protected
    * 
    * @param array $user_data {
    *   An array containing a 'ip_address' key
    * 
    *   @type string $ip_address
    * }
    * 
    * @return string|null The IP address or null on failure
    */  
    protected static function getIpAddress( $user_data ) {
        
        if ( ! empty( $user_data['ip_address'] ) ) {
            return filter_var($user_data['ip_address'], FILTER_VALIDATE_IP);
        }
        
        $client_ip = Helpers\Networking\IpAddress::getClientAddress();
        
        if( $client_ip && !is_wp_error( $client_ip ) ) {
            return $client_ip;
        }
        
        return null;
    }
    
    /**
    * Format Float to a two decimal strings
    *
    * @access protected
    * 
    * @param float $float_value 
    * 
    * @return string Formatted value
    */  
    protected static function formatFloat( $float_value ) {
        return sprintf( '%.2f', $float_value );
    }    
    
    
    /**
    * Create an affiliate account.
    *
    * @access public
    * @since 3.1.15
    * 
    * @param array $user_data {
    *   An array containing the user data
    *   
    *   @type string $username      Username - Must be a minimum 4 characters in length.	(ex. username=bailey08)
    *   @type string $password      Password - Must be a minimum 4 characters in length (ex. password=makemoney)
    *   @type string $email         Email - Must Be An Emaill Address (ex. email=ferrari@porsche.com)
    *   @type int    $approved      (Optional) 1 = approved, 0 = not approved	(default: approved=1)
    *   @type int    $payout_type   (Optional) Payout Type  1 = Percentage, 2 = Flat Rate , 3 = PPC	payout_type=1 (default: approved=1)
    *   @type int    $payout_level	(Optional) Payout Level 
    *   @type int    $use_paypal	(Optional) Use Paypal - 1 = yes, 0 = no	(default: yes)
    *   @type string $first_name	(Optional) First name - Letters Only
    *   @type string $last_name	    (Optional) Last Name - Letters Only
    *   @type string $company	    (Optional) Company Name - Normal Characters
    *   @type string $payable	    (Optional) Make checks payable to. - Letters Only
    *   @type string $tax_id	    (Optional) Tax ID, SSN or VAT - Letters, Numbers & Dashes	
    *   @type string $website	    (Optional) Website Address -  A Valid URL
    *   @type string $address_1	    (Optional) Address Line One - Letters & Numbers
    *   @type string $address_2	    (Optional) Address Line Two - Letters & Numbers
    *   @type string $city	        (Optional) City - Letters & Numbers
    *   @type string $state	        (Optional) State Abbr. or Full - Letters (state=FL)
    *   @type string $zip	        (Optional) Postal Code - Numbers
    *   @type string $country	    (Optional) Country - 2 Letters - Abbr: US, CA, JP, etc. 
    *   @type string $phone	        (Optional) Phone- Letters & Dashes (phone=444-444-4444)
    *   @type string $fax	        (Optional) Fax - Letters & Dashes
    *   
    * }
    * 
    * @return WP_Error|array The response or WP_Error on failure.
    */        
    public function create_user( $user_data ){
        
        $definition = array(
            'secret' => FILTER_SANITIZE_STRING,	
            'username' => FILTER_SANITIZE_STRING,	
            'password' => FILTER_SANITIZE_STRING,
            'email' => FILTER_VALIDATE_EMAIL,       
            'approved' => array( 
                'filter'    => FILTER_VALIDATE_INT,
                'options'   => array( 
                    'min_range' => 0, 
                    'max_range' => 1, 
                    'default' => 1 
                )
               ), 
            'payout_type' => array( 
                'filter'    => FILTER_VALIDATE_INT,
                'options'   => array( 
                    'min_range' => 0, 
                    'max_range' => 3, 
                    'default' => 1 
                )
               ), 
            'payout_level' => array( 
                'filter'    => FILTER_VALIDATE_INT,
                'options'   => array( 
                    'min_range' => 1, 
                    'default' => 1 
                )
               ), 
            'use_paypal' => array( 
                'filter'    => FILTER_VALIDATE_INT,
                'options'   => array( 
                    'min_range' => 0, 
                    'max_range' => 1, 
                    'default' => 1 
                )
               ), 
            'paypal_account' =>  FILTER_VALIDATE_EMAIL,
            'first_name' => FILTER_SANITIZE_STRING, 
            'last_name' => FILTER_SANITIZE_STRING,  
            'company' => FILTER_SANITIZE_STRING,
            'payable' => FILTER_SANITIZE_STRING, 
            'tax_id' => FILTER_SANITIZE_STRING, 
            'website' => FILTER_SANITIZE_STRING,
            'address_1' => FILTER_SANITIZE_STRING,
            'address_2'	=> FILTER_SANITIZE_STRING,
            'city' => FILTER_SANITIZE_STRING,
            'state' => FILTER_SANITIZE_STRING,
            'zip' => FILTER_SANITIZE_STRING,
            'country' => FILTER_SANITIZE_STRING,
            'phone'	 => FILTER_SANITIZE_STRING,
            'fax'  => FILTER_SANITIZE_STRING,
        );
        
        $args = filter_var_array( $user_data, $definition, false );
        
        return wp_remote_post( untrailingslashit($this->base_url) . '/API/scripts/new_affiliate.php', array( 'body' => $args ) );
        
    }
    
}