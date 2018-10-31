<?php 

namespace Svbk\WP\Helpers\Html;

class Element {
    
    public static function attributes( $attributes ){
        
        $html = '';
        
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= static::htmlClass( $value );
                } else {
                    $html .= " $name='" . json_encode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" . esc_attr($value) . '"';
            }
        }
        
        return $html;
    }
    
    public static function htmlClass( $classes ){
        return ' class="' . esc_attr( implode(' ', $classes) ) . '"';
    }
    
}