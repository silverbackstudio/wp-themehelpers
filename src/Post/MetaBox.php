<?php

namespace Svbk\WP\Helpers\Post;

class MetaBox {

	public $name;

	public $args = array(
		'title' => '',
		'post_type' => 'post',
		'position' => 'side',
		'priority' => 'default',
	);

	public $fields = array();

    /**
     * Constructor.
     */
	public function __construct( $name, $fields, $args = '' ) {
		$this->name = $name;

		foreach ( $fields as $name => $options ) {
			$this->fields[] = new MetaBoxField( $name, $options );
		}

		$this->args = wp_parse_args( $args, $this->args );

        if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			add_action( 'edit_form_after_title', array( $this, 'register_top_position' ) );            
        }
	}

    /**
     * Meta box initialization.
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add'  )        );
        add_action( 'save_post',      array( $this, 'save' ), 10, 2 );
    }

    /**
     * Print MetaBox in Top position
     */
	public function register_top_position() {
		global $post, $wp_meta_boxes;

		do_meta_boxes( get_current_screen(), 'top', $post );

		unset( $wp_meta_boxes[ get_post_type( $post ) ]['top'] );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add() {
		add_meta_box(
			$this->name,
			$this->args['title'],
			array( $this, 'render' ),
			$this->args['post_type'],
			$this->args['position'],
			$this->args['priority']
		);
	}

	public static function has_permission( $post_id, $post_type = 'post' ) {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	
		// Check the user's permissions.
		if ( 'page' === $post_type ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return false;
			}
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {

		if( empty( $_POST ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if( !self::has_permission( $post_id ) ) {
			return;
		}
		
		if( ! in_array( get_post_type( $post_id ), (array) $this->args['post_type'] ) ) {
			return;
		}
		
		if( ! check_admin_referer( 'metabox_save', 'metabox_' . $this->name ) ) {
			return;
		}		

		foreach ( $this->fields as $field ) {
			$field->save( $post_id );
		}

	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public  function render( $post ) {

		wp_nonce_field( 'metabox_save' , 'metabox_' . $this->name  );

		foreach ( $this->fields as $field ) {
			$field->render( $post->ID );
		}

	}
}


