<?php

namespace Svbk\WP\Helpers\Post;

class MetaBoxField {

	public $id;
	public $type = 'text';
	public $name;
	public $label;
	public $default;

	public function __construct( $name, $args = array() ) {

		$this->name = $name;

		if ( is_array( $args ) ) {
			foreach ( $args as $property => $value ) {
				if ( property_exists( $this, $property ) ) {
					$this->{$property} = $value;
				}
			}
		} else {
			$this->label = $args;
		}

		if ( empty( $this->id ) ) {
			$this->id = $this->name;
		}

	}

	public function save( $post_id ) {

		if ( 'checkbox' !== $this->type ) {
			$value = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_SPECIAL_CHARS );
			return update_post_meta( $post_id, $this->name, $value );
		} else {
			$value = filter_input(INPUT_POST, $this->name, FILTER_VALIDATE_BOOLEAN );
			return update_post_meta( $post_id, $this->name, $value );
		}

	}

	public function render( $post_id ) {

		$values = get_post_meta( $post_id, $this->name );

		if ( empty( $values ) ) {
			$value = $this->default;
		} else {
			$value = $values[0];
		}

		echo '<div class="meta-box-field">';

		if ( 'checkbox' === $this->type ) {
			$template = '<input type="%1$s" id="%4$s" name="%2$s" value="%3$s" %6$s/><label for="%4$s" >%5$s</label>';
		} elseif ( 'textarea' === $this->type ) {
			$template = '<textarea id="%4$s" name="%2$s" %6$s/>%3$s</textarea><label for="%4$s" >%5$s</label>';
		} else {
			$template = '<label for="%4$s" ><strong>%5$s</strong></label><input type="%1$s" id="%4$s" name="%2$s" value="%3$s"  />';
		}

		printf($template,
			esc_attr( $this->type ),          // 1
			esc_attr( $this->name ),          // 2
			esc_attr( $value ),               // 3
			esc_attr( $this->id ),            // 4
			esc_html( $this->label ),         // 5
			$value?'checked="checked"':''   // 6
		);

		echo '<hr /></div>';
	}

}
