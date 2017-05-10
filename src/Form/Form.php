<?php 

namespace Svbk\WP\Helpers\Form;

class Form {

    public $index = 0;
    
    public static $salt = 's1v2b3k4';
    public $field_prefix = 'frm';
    public $antispam_timeout = 0;
    
    public $errors = array();
    
    const PREFIX_SEPARATOR = '-';
    
    protected function addError($error, $field=null){
        
        if($field){
            $this->errors[$field][] = $error;
        } else {
            $this->errors[] = $error;
        }
        
    }
    
    public function getErrors(){
        return $this->errors;
    }

    public function processInput($fields){
        
        $hashed_fields = array();
        $inputs = array();
        
        foreach($fields as $field => $filter){
            $hashed_field_name = $this->fieldName($field);
            $hashed_filters[ $hashed_field_name ] = $filter;
            $input[ $field ] = $hashed_field_name;
        }
        
        $hashed_inputs = filter_input_array( INPUT_POST, $hashed_filters );
        
        foreach($input as $field => $hashed_field_name){
            $input[ $field ] = $hashed_inputs[ $hashed_field_name ];
        }        
        
        return $input;
    }
    
    public function fieldName($fieldName, $hash = true){
        
        $clearText =   $this->index . '_' . $fieldName;
        
        if( !$hash ){
            return $this->field_prefix . '_' . $clearText;
        }
        
        $clearText .= self::$salt;
        
        if($this->antispam_timeout > 0){
            $clearText .= round( time() / ( $this->antispam_timeout * MINUTE_IN_SECONDS * 2 ) );
        }
        
        return $this->field_prefix . md5( $clearText );
    }
    
    protected static function fieldRequired($fieldAttr){
        return (bool) ( isset($fieldAttr['required']) ? $fieldAttr['required'] : false );
    }
    
    protected static function fieldError($fieldAttr, $name=''){
        return ( isset($fieldAttr['error']) ? $fieldAttr['error'] : sprintf( __('Empty or invalid field [%s]', 'svbk-helpers'), $name )  );
    }        
    
    public function renderField($fieldName, $fieldAttr, $errors = array()){
        
            if( ! is_array( $fieldAttr ) ){
                return $fieldAttr;
            }
        
            $type = isset( $fieldAttr['type'] ) ? $fieldAttr['type'] : 'text';
            $fieldLabel = isset( $fieldAttr['label'] ) ? $fieldAttr['label'] : 'text';
            $value = isset( $fieldAttr['default'] ) ? $fieldAttr['default'] : '';
        
            $fieldClass = preg_replace('/\[\d+\]/i', '', $fieldName);
            $fieldClass = preg_replace('/\[(\w+)\]/i', '-$1', $fieldClass);

            $classes = array_merge(
                array( 
                    $this->field_prefix . self::PREFIX_SEPARATOR . $fieldClass . '-group',
                    'field-group',
                ),
                isset( $fieldAttr['class'] ) ? (array)$fieldAttr['class'] : array()
            );
            
            if( $this->fieldRequired($fieldAttr) ){
                $classes[] = 'required';
            }
        
            $fieldNameHash = esc_attr( $this->fieldName($fieldName) );
            $fieldId =  esc_attr( $this->fieldName($fieldName, false) );
            $fieldId = preg_replace('/\[(\d+)\]/i', '-$1', $fieldId);
            $fieldId = preg_replace('/\[(\w+)\]/i', '-$1', $fieldId);        
        
            $output = '<div class="' . esc_attr( join(' ', $classes) ) . '">';
            
            if('checkbox' === $type){
                    $output .= '<input type="' . esc_attr($type)  . '" name="' . $fieldNameHash . '" id="' . $fieldId . '" value="1" />'           
                    .       '<label for="' . $fieldId . '">' . $fieldLabel .'</label>';                  
            }
            elseif('textarea' === $type){
                    $output .=  '<label for="' . $fieldId . '">' . $fieldLabel .'</label>'
                    .           '<textarea type="' . esc_attr($type)  . '" name="' . $fieldNameHash . '" id="' . $fieldId . '">'. esc_html($value) . '</textarea>';
            } elseif ( ('select' === $type) && !empty($fieldAttr['choices']) ){
                    $output .=  '<label for="' . $fieldId . '">' . $fieldLabel .'</label>';
                    $output .=  '<select name="' . $fieldNameHash . '" id="' . $fieldId . '" >';
                    foreach( $fieldAttr['choices']  as $cValue => $cLabel) {
                        $output .=  '  <option value="' . esc_attr($cValue) . '" ' . selected( $value, $cValue, false ) . '>'. esc_html($cLabel) . '</option>';
                    }
                    $output .=  '</select>';
            } elseif ( ('checkboxes' === $type) && !empty($fieldAttr['choices']) ){
                    $output .=  '<label >' . $fieldLabel .'</label>';
                    $output .=  '<div name="' . $fieldNameHash . '" id="' . $fieldId . '" >';
                    foreach( $fieldAttr['choices']  as $cValue => $cLabel) {
                        $output .=  '  <div class="select field-pair">';
                        $output .=  '  <input id="' . $fieldId . '_' . esc_attr($cValue) . '"  name="' . $fieldNameHash . '[' . $cValue . ']" type="checkbox" value="1" ' . checked( $value, $cValue, false ) . '  />';
                        $output .=  '  <label for="' . $fieldId . '_' . esc_attr($cValue) . '">' . esc_html($cLabel) . '</label>';
                        $output .=  '  </div>';
                    }
                    $output .=  '</div>';
            } elseif('image' === $type){
                    $output .= '<label for="' . $fieldId . '">' . $fieldLabel .'</label>';
                    $output .= '<input type="file" name="' . $fieldNameHash . '" id="' . $fieldId . '" />';
                    $output .= wp_get_attachment_image( $value, 'thumb' );
            } else {
                    $output .= '<label for="' . $fieldId . '">' . $fieldLabel .'</label>'
                    .       '<input type="' . esc_attr($type)  . '" name="' . $fieldNameHash . '" id="' . $fieldId . '" value="' . esc_attr($value) . '" />';
            }
                    
            if($errors !== false){
                $output .=  '<span class="field-errors"></span>';
            }
            
            $output .=  '</div>';
            
            return $output;
        
    }        
    
    
}