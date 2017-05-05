<?php

namespace Svbk\WP\Helpers\Lists;

class Array {

    /**
    * Insert an array into another array before/after a certain key
    *
    * @param array $array The initial array
    * @param array $pairs The array to insert
    * @param string $key The certain key
    * @param string $position Wether to insert the array before or after the key
    * @return array
    */
    public static function keyInsert( $array, $pairs, $key, $position = 'after' ) {
        $key_pos = array_search( $key, array_keys( $array ) );
        
        if ( 'after' == $position ){
        	$key_pos++;
        }
        if ( false !== $key_pos ) {
        	$result = array_slice( $array, 0, $key_pos );
        	$result = array_merge( $result, $pairs );
        	$result = array_merge( $result, array_slice( $array, $key_pos ) );
        } else {
        	$result = array_merge( $array, $pairs );
        }
        return $result;
    } 

    /**
    * Insert an array into another array before/after a certain value
    *
    * @param array $array The initial array
    * @param array $values The array to insert
    * @param string $reference The reference value
    * @param string $position Wether to insert the array before or after the value
    * @return array
    */
    public static function insert( $array, $values, $reference, $position = 'after' ) {
        $key_pos = array_search( $reference, $array );
        
        if ( 'after' == $position ){
        	$key_pos++;
        }
        
        if ( false !== $key_pos ) {
            $result = $array;
            array_splice($result, $key_pos, 0, $values);
        } else {
        	$result = array_merge( $array, $values );
        }
        return $result;
    } 

    public static function mergeParts($output, $order=null){
        
        if( empty($order) ) {
            $order = array_keys( $output );
        }
        
        $output_html = '';
        
        foreach($order as $part){
            
            if(!array_key_exists($part, $output)){
                continue;
            }
            
            if(is_array($output[$part])){
                $output_html .= self::mergeParts( $output[$part] );
            } else {
                $output_html .= $output[$part];
            }
            
        }        
        
        return $output_html;
        
    }

}