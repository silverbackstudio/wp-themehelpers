<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\MailChimp;

class Submission extends Form {

    public static $defaultPolicyFilter = array( 
        'filter'=>FILTER_VALIDATE_BOOLEAN, 
        'flags'=>FILTER_NULL_ON_FAILURE 
    );
    
    public $field_prefix = 'sub';
    public $action = 'svbk_submission';
    
    public $inputFields = array();
    public $policyParts = array();
    
    protected $inputData = array();
    protected $inputErrors = array();
    
    public static $next_index = 1;
    public $index = 0;

    public function __construct(){
        
        $this->index = self::$next_index++;
        
        $this->setInputFields($this->inputFields);
        $this->setPolicyParts($this->policyParts);        
    }
    
    public function setInputFields($fields=array()){
        
        $this->inputFields = array_merge( 
            array(
                'fname' => array( 
                    'required' => true,
                    'label' => __('First Name', 'svbk-shortcakes'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter first name', 'svbk-shortcakes')
                ),
                'lname' => array( 
                    'required' => true,
                    'label' => __('Last Name', 'svbk-shortcakes'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter last name', 'svbk-shortcakes')
                ),                
                'email' => array( 
                    'required' => true,
                    'label' => __('Email Address', 'svbk-shortcakes'), 
                    'filter' => FILTER_VALIDATE_EMAIL,
                    'error' => __('Invalid email address', 'svbk-shortcakes')
                ),
            ), 
            $fields
        );
        
        return $this->inputFields;
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
                'policy_newsletter' => array(
                    'label' => __('Accetto il trattamento dei dati di cui all\' articolo 4.1 dell\'informativa sulla privacy.', 'svbk-shortcakes'),
                    'required' => false,
                    'type' => 'checkbox',
                    'filter' => self::$defaultPolicyFilter,
                ),                    
                'policy_directMarketing' => array(
                    'label' => __('Accetto il trattamento dei dati di cui all\' articolo 4.2 dell\'informativa sulla privacy.', 'svbk-shortcakes'), 
                    'type' => 'checkbox',
                    'required' => false,
                    'filter' => self::$defaultPolicyFilter,
                ),                    
            ), 
            $policyParts
        );
            
        return $this->policyParts;
    }         
    
    public function insertInputField($fieldName, $fieldParams, $after=null){
        
        if($after){
            $this->inputFields = $this->arrayInsert($this->inputFields, array($fieldName => $fieldParams), $after);
        } else {
            $this->inputFields[$fieldName] = $fieldParams;
        }
        
    }
    
    public function getInput($field){
        return isset($this->inputData[$field]) ? $this->inputData[$field] : null;
    }

    /**
    * Insert an array into another array before/after a certain key
    *
    * @param array $array The initial array
    * @param array $pairs The array to insert
    * @param string $key The certain key
    * @param string $position Wether to insert the array before or after the key
    * @return array
    */
    protected static function arrayInsert( $array, $pairs, $key, $position = 'after' ) {
        $key_pos = array_search( $key, array_keys( $array ) );
        if ( 'after' == $position )
        	$key_pos++;
        if ( false !== $key_pos ) {
        	$result = array_slice( $array, 0, $key_pos );
        	$result = array_merge( $result, $pairs );
        	$result = array_merge( $result, array_slice( $array, $key_pos ) );
        }
        else {
        	$result = array_merge( $array, $pairs );
        }
        return $result;
    }    
    
    public function processInput($input_filters=array()){
    
        $index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);
        
        if ( $index === false ) {
            $this->addError(__('Input data error', 'svbk-helper'));  
            return;
        }  else {
            $this->index = $index;
        }
        
        $input_filters['policy_all'] = self::$defaultPolicyFilter;
        
        $input_filters = array_merge( 
                $input_filters,
                wp_list_pluck( $this->inputFields, 'filter' )
        );
        
        if ( $this->policyParts ) {
            $input_filters = array_merge( 
                $input_filters,
                wp_list_pluck( $this->policyParts, 'filter' )
            );
        } 
        
    
        $this->inputData = parent::processInput($input_filters);
        
        $this->validateInput();
        
    }
    
    protected function getField($fieldName){
        
        if( isset($this->inputFields[$fieldName]) ){
           return $this->inputFields[$fieldName];
        } elseif( isset($this->policyParts[$fieldName]) ){
            return $this->policyParts[$fieldName];
        } else {
            return false;
        }        
        
    }
    
    protected static function fieldRequired($field){
        return (bool) ( isset($field['required']) ? $field['required'] : false );
    }
    
    protected static function fieldError($field, $name=''){
        return ( isset($field['error']) ? $field['error'] : sprintf( __('Empty or invalid field [%s]', 'svbk-shortcakes'), $name )  );
    }    
    
    protected function validateInput(){
        
        $policyFields = array_keys($this->policyParts);
        
        foreach($this->inputData as $name => $value){
            
            $field = $this->getField($name);
            
            if( !$value && $this->fieldRequired($field)){
                $this->addError( $this->fieldError($field, $name), $name );
                
                if(in_array($name, $policyFields)){
                    $this->addError( $this->fieldError($field, $name), 'policy_all');    
                }
                
            }
            
        }
        
    }    

    public function checkPolicy($policyPart='policy_service'){
        
        if( $this->getInput('policy_all') ){
            return true;
        }
        
        if($this->getInput($policyPart)){
            return true;
        }
        
        return false;
    }

    public function processSubmission(){
        
        $this->processInput();
        
        if(empty($this->errors) && $this->checkPolicy()){
            $this->mainAction();
        }
    }    
    
    protected function mainAction(){ }       
 
    protected function privacyNotice($attr){
        
        $label = __('Privacy policy', 'svbk-shortcakes');
        
        if( shortcode_exists('privacy-link') ) {
            $privacy = do_shortcode( sprintf( '[privacy-link]%s[/privacy-link]', $label ));
        } elseif( isset($attr['privacy_link']) && $attr['privacy_link'] ) {
            $privacy = sprintf( __('<a href="%s" target="_blank">%s</a>', 'svbk-shortcakes'), $attr['privacy_link'], $label);
        } else {
            $privacy = $label;
        }
        
        $text = sprintf( __('I declare I have read and accept the %s notification and I consent to process my personal data.', 'svbk-shortcakes'), $privacy);
        
        if(count($this->policyParts) > 1){
            $flagsButton = '<a class="policy-flags-open" href="#policy-flags-' . $this->index . '">' . __('click here','svbk-shortcakes') . '</a>';
            $text .= sprintf( __('To select the consents partially %s.', 'svbk-shortcakes'), $flagsButton);
        }
        
        return $text;
    }
 
    public function renderParts($action, $attr=array()){

        $admin_url = admin_url('admin-post.php');

        $output = array();

        $output['formBegin'] = '<form class="svbk-form" action="'. esc_url( $admin_url ) .'" id="'.$this->field_prefix. self::PREFIX_SEPARATOR . $this->index . '" method="POST">';
        
        foreach($this->inputFields as $fieldName => $fieldAttr) {
            $output['input'][$fieldName] = $this->renderField($fieldName, $fieldAttr);
        }
        
        $output['policy']['begin'] = '<div class="policy-agreements">';
        
        if( count($this->policyParts) > 1) {
            
            $output['policy']['global'] = $this->renderField('policy_all', array( 'label' => $this->privacyNotice($attr), 'type'=>'checkbox', 'class'=>'policy-flags-all' )  );
            
            $output['policy']['flags']['begin'] = '<div class="policy-flags" id="policy-flags-' . $this->index . '" style="display:none;" >';
            
            foreach($this->policyParts as $policy_part => $policyAttr ) {
                $output['policy']['flags'][$policy_part] = $this->renderField($policy_part, $policyAttr);        
            }
            
            $output['policy']['flags']['end'] = '</div>';
            
        } else {
            $output['policy']['global'] = $this->renderField('policy_service', array( 'label' => $this->privacyNotice($attr), 'type'=>'checkbox' )  );
        }
        
        $output['policy']['end'] = '</div>';
        
        $output['input']['action'] = '<input type="hidden" name="action" value="' . $this->action . '" >';
        $output['input']['index']  = '<input type="hidden" name="index" value="' . $this->index . '" >';
        
        $output['submitButton'] = '<button type="submit" name="' . $this->fieldName('subscribe') . '" class="button">' . urldecode($attr['submit_button_label']) . '</button>';
        
        $output['messages'] ='<ul class="messages"></ul>';+
        $output['formEnd'] = '</form>';        
        
        return $output;
        
    }
    
}