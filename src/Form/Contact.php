<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\Mandrill;
use Mandrill_Error;

class Contact extends Submission {

    public static $defaultPolicyFilter = array( 
        'filter'=>FILTER_VALIDATE_BOOLEAN, 
        'flags'=>FILTER_NULL_ON_FAILURE 
    );
    
    public $field_prefix = 'cnt';
    public $action = 'svbk_contact';
    
    public $md_apikey = '';
    public $templateName = '';    
    public $recipientEmail = 'webmaster@silverbackstudio.it';    
    public $recipientName = 'Webmaster';    

    public function setInputFields($fields=array()){
        
        return parent::setInputFields(
            array_merge(
                array(
                    'subject' => array( 
                        'required' => true,
                        'label' => __('Subject', 'svbk-shortcakes'), 
                        'type' => 'text',
                        'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                        'error' => __('Please enter a subject', 'svbk-shortcakes')
                    ),                
                    'request' => array( 
                        'required' => true,
                        'label' => __('Message', 'svbk-shortcakes'), 
                        'type' => 'textarea',
                        'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                        'error' => __('Please write a brief description of your request', 'svbk-shortcakes')
                    ),
                ), 
                $fields
            )
        );
        
    }       

    public function setPolicyParts($policyParts=array()){
        
        $this->policyParts = array_merge_recursive( 
             array(
                'policy_service' => array(
                    'label' => __('Ho letto e accetto le condizioni generali e l\'informativa della privacy.', 'svbk-shortcakes'),
                    'required' => true,
                    'type' => 'checkbox',
                    'error' => __('Policy terms must be accepted', 'svbk-shortcakes'),
                    'filter' => self::$defaultPolicyFilter,
                ),
            ), 
            $policyParts
        );
            
        return $this->policyParts;
    }   


    protected function mainAction(){
        
        
        if( !empty( $this->md_apikey ) ){

            try {
                $mandrill = new Mandrill( $this->md_apikey );

                if($this->templateName){
                    $results = $mandrill->messages->sendTemplate($this->templateName, array(), $this->messageParams());
                } else {
                    $results = $mandrill->messages->send($this->messageParams());
                }
                
                if( !is_array($results) || !isset($results[0]['status']) ){
                    throw new Mandrill_Error( __('The requesto to our mail server failed, please try again later or contact the site owner.', 'svbk-helpers') );
                } 
                
                $errors = $mandrill->getResponseErrors($results);
                
                foreach($errors as $error){
                    $this->addError($error, 'email');
                }

            } catch(Mandrill_Error $e) {
                $this->addError( $e->getMessage() );
            }            
            
        }
        
    }   
    
    protected function getRecipients(){
        return array(
            array(
                'email' => $this->recipientEmail,
                'name' => $this->recipientName,
                'type' => 'to'
            )
        );
    }
    
    protected function messageParams(){
        
        return array_merge_recursive(
            Mandrill::$messageDefaults,
            array(
                'text' => $this->getInput('request'),
                'subject' => $this->getInput('subject'),
                'to' => $this->getRecipients(),
                'global_merge_vars' => Mandrill::castMergeTags($this->inputData, 'INPUT_'),
                'metadata' => array(
                    'website' => home_url( '/' )
                ),
                'merge' =>true,
                'tags' => array('download-request'),
            )
        );        
    }    
    
}