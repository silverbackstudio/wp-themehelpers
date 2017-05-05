<?php 

namespace Svbk\WP\Helpers\Post;

use WP_Term;

class CrossFilter {
    
    public $post_type = 'post';
    
    protected $primary_tax;
    protected $secondary_tax;
    protected $_primary_tax;
    protected $_secondary_tax;
    
    public function __construct($post_type, $primary_tax, $secondary_tax){
        $this->post_type = $post_type;
        
        $this->setPrimaryTaxonomy($primary_tax);
        $this->setSecondaryTaxonomy($secondary_tax);
    }
    
    public static function instance($post_type, $primary_tax, $secondary_tax){
        
        $class = get_called_class();
        
        return new $class($post_type, $primary_tax, $secondary_tax);
    }
    
    public function setPrimaryTaxonomy($primary_tax){
        $this->primary_tax = $primary_tax;
        $this->_primary_tax = get_taxonomy($this->primary_tax);
    }
    
    public function setSecondaryTaxonomy($secondary_tax){
        $this->secondary_tax = $secondary_tax;
        $this->_secondary_tax = get_taxonomy($this->secondary_tax);
    }    
    
    public function getSecondaryTerms($primary_terms, $posts_query = array(), $tax_query = array()) {
        
        $posts_query = wp_parse_args( $posts_query, array(
            'post_type' => $this->post_type,
			'posts_per_page'=>'-1',
			'fields'=>'ids',
			'tax_query' => [ 
				[
				    'taxonomy' => $this->primary_tax, 
				    'field' => 'slug', 
				    'terms' => $primary_terms 
				]
			]             
        ));
        
        $tax_query = wp_parse_args( $tax_query,	array(
			'orderby' => 'term_order', 
			'order' => 'ASC', 
			'fields' => 'all'
			)
        );
        
	    $found_posts = get_posts( $posts_query );
	    
	    if(!$found_posts){
	        return array();
	    }
	
		return wp_get_object_terms( $found_posts, $this->secondary_tax, $tax_query );
    }
						    
    public function getLink($primary_term, $secondary_term=null){
        
        $base_link = get_term_link($primary_term, $this->primary_tax);
        
        if(!$secondary_term instanceof WP_Term){
            $secondary_term = get_term($secondary_term, $this->secondary_tax, 'OBJECT');
        }
        
        if(is_wp_error($secondary_term) || !$secondary_term){
            return $base_link;
        }
        
        if ( !get_option('permalink_structure') ) {
            return add_query_arg($this->_primary_tax->query_var, $secondary_term->slug, $base_link);
        } 
        
        return trailingslashit($base_link).$this->_secondary_tax->rewrite['slug'].'/'.$secondary_term->slug;
    }
    
    public function getCurrentPrimary(){
        return get_query_var($this->_primary_tax->query_var);
    }
    
    public function getCurrentSecondary(){
        return get_query_var($this->_secondary_tax->query_var);
    }
    
    public function isCurrent(WP_Term $term){
        
        if($term->taxonomy === $this->primary_tax){
            return $this->getCurrentPrimary() === $term->slug;
        }
        
        if($term->taxonomy === $this->secondary_tax){
            return $this->getCurrentSecondary() === $term->slug;
        }        
        
        return false;
    }
    
    public function render($all_label=null){
        
        $output = '';
        
		$primary = $this->getCurrentPrimary();
		$secondary_items = $this->getSecondaryTerms($primary);
		
		if(empty($secondary_items)){
		    return $output;
		}
		
		if($all_label && !$this->getCurrentSecondary()){
		    $output .=  '<li class="all current">'.$all_label.'</li>';
		} elseif( $all_label ) {
		    $output .=  '<li class="all"><a href="'.$this->getLink($primary).'">'.$all_label.'</a></li>';
		}
		
		foreach($secondary_items as $secondary){
			if($this->isCurrent($secondary)){
				$output .= '<li class="current">'.$secondary->name.'</li>';
			} else {
				$output .= '<li><a href="'.$this->getLink($primary, $secondary).'">'.$secondary->name.'</a></li>';
			}
		}        
		
		return $output;
    }
    
}