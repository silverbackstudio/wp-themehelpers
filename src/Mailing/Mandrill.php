<?php

namespace Svbk\WP\Helpers\Mailing;

use Mandrill as NativeMandrill;
use Mandrill_Error;

class Mandrill extends NativeMandrill {

    public static $messageDefaults = array(
        'html' => 'default HTML content',
        //'text' => 'default TEXT content',        
        'track_opens' => null,
        'track_clicks' => null,
        'auto_text' => null,
        'auto_html' => true,
        'inline_css' => null,
        'url_strip_qs' => null,
        'preserve_recipients' => null,
        'view_content_link' => null,
        'tracking_domain' => null,
        'signing_domain' => null,
        'return_path_domain' => null,
        'merge' => true,
        'merge_language' => 'mailchimp',
        'tags' => array('download-request'),
        'subaccount' => null,
    ); 

    public static function castMergeTags($inputData, $prefix=''){
        
        foreach($inputData as $key => &$value){
            $value = array(
                'name'=> $prefix . strtoupper($key),
                'content' => $value
            );
        }
        
        return $inputData;
    }

    public function getResponseErrors($results){
        
        $errors = array();
        
        foreach($results as $result){
        
            if($result['status'] === 'rejected'){
                
                switch($result['reject_reason']){
                   case 'rule':
                   case 'unsub':
                   case 'custom':
                        $errors[] = __('This address has beed rejected, this sometimes is due to multiple tries to send to a full or disabled inbox. Please try again later', 'svbk-helpers');
                        break;
                    case 'hard-bounce': 
                        $errors[] = __('The mailbox is non existent or disabled. Please check your email address is correct or your contact your mailbox provider.', 'svbk-helpers');
                        break;
                    case 'soft-bounce': 
                        $errors[] = __('Your mailbox isn\'t accepting our message. Please check your if mailbox your mailbox is full.', 'svbk-helpers');
                        break; 
                    case 'invalid': 
                        $errors[] = __('Your email address is invalid. Please check the address or use another.', 'svbk-helpers');
                        break;  
                    case 'spam': 
                        $errors[] = __('Your mailbox is reporting our message as spam. Please add our domain to trusted senders or contact the website tech support', 'svbk-helpers');
                        break; 
                    case 'test': 
                        $errors[] = __('This email address is a test account.', 'svbk-helpers');
                        break;     
                    case 'test-mode-limit': 
                        $errors[] = __('The test mode sending limit is beeing reached.', 'svbk-helpers');
                        break;                         
                    default:
                        $errors[] = __('This email address has beeing rejected for an unknown reason. Please use another email address.', 'svbk-helpers');
    
                }
                return false;
            }
            
            if($result['status'] === 'invalid'){
                $errors[] =  __('Your email address is invalid. Please check the address or use another.', 'svbk-helpers');
                return false;
            }       
        
        }
        
        return $errors;   
    }       
    
}