<?php 

namespace Svbk\WP\Helpers\Form;


class Field  {
 
    public $name = '';
 
    public $required = false;
    public $label = '';
    public $filter = FILTER_SANITIZE_SPECIAL_CHARS;
    public $requiredError = '';
    public $type = 'text';
    public $value;
    public $classes;
    

    public function validate(){
        
        return $this->required && $value;
        
    }
    
}