<?php

namespace Svbk\WP\Helpers;

class MetaBox {

        public $name;

        public $args = array(
            'title'=>'',
            'post_type'=>'post',
            'position'=>'side',
            'priority'=>'default'
        );
        
        public $fields = array();
            
        public function __construct($name, $fields, $args='') {
            $this->name = $name;
            
            foreach ($fields as $name=>$options){
                $this->fields[] = new MetaBoxField($name, $options);
            }
            
            $this->args = wp_parse_args($args, $this->args);
            
            add_action( 'add_meta_boxes', array( $this, 'add' ) );
            add_action( 'save_post', array( $this, 'save' ) );
        }
        
	/**
	 * Adds the meta box container.
	 */
	public function add() {
		add_meta_box(
			$this->name
			,$this->args['title']
			,array( $this, 'render' )
			,$this->args['post_type']
			,$this->args['position']
			,$this->args['priority']
		);              
	}
        


        public static function has_permission($post_id){
		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return false;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return false;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return false;
		}            
                
                return true;
        }
        
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
                foreach($this->fields as $field){
                    if(!$field->verify_nonce() || !self::has_permission($post_id)){
                        continue;
                    }
                    
                    $field->save($post_id);
                    
                }
            
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public  function render( $post ) {
	
             foreach($this->fields as $field){

                 $field->render($post->ID);
                
             }
                
	}
}


