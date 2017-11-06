<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers;

class Submission extends Form {

	public static $defaultPolicyFilter = array(
		'filter' => FILTER_VALIDATE_BOOLEAN,
		'flags' => FILTER_NULL_ON_FAILURE,
	);

	public $field_prefix = 'sub';
	public $action = 'svbk_submission';
	public $submitUrl = '';

	public $inputFields = array();
	public $policyParts = array();

	protected $inputData = array();
	protected $inputErrors = array();

	public static $next_index = 1;
	public $index = 0;

	public function __construct() {

		$this->index = self::$next_index++;

		$this->setInputFields( $this->inputFields );
		$this->setPolicyParts( $this->policyParts );
	}

	public function setInputFields( $fields = array() ) {

		$this->inputFields = array_merge(
			array(
				'fname' => array(
					'required' => true,
					'label' => __( 'First Name', 'svbk-helpers' ),
					'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
					'error' => __( 'Please enter first name', 'svbk-helpers' ),
				),
				'email' => array(
					'required' => true,
					'label' => __( 'Email Address', 'svbk-helpers' ),
					'filter' => FILTER_SANITIZE_EMAIL,
					'error' => __( 'Invalid email address', 'svbk-helpers' ),
				),
			),
			$fields
		);

		return $this->inputFields;
	}

	public function addInputFields( $fields, $key = '', $position = 'after' ) {
		$this->inputFields = Helpers\Form\Renderer::arraykeyInsert( $this->inputFields, $fields, $key, $position );
	}

	public function removeInputFields() {
		$this->inputFields = array();
	}
	
	public function removeInputField( $field ) {
		if ( array_key_exists( $field, $this->inputFields ) ) {
			unset( $this->inputFields[$field] );
		}
	}	

	public function setPolicyParts( $policyParts = array() ) {

		$this->policyParts = array_merge_recursive(
			array(
				'policy_service' => array(
					'label' => __( 'I have read and agree to the "Terms and conditions" and the "Privacy Policy"', 'svbk-helpers' ),
					'required' => true,
					'type' => 'checkbox',
					'error' => __( 'Privacy Policy terms must be accepted', 'svbk-helpers' ),
					'filter' => self::$defaultPolicyFilter,
				),
				'policy_newsletter' => array(
					'label' => __( 'I accept the processing of the data referred to in Article 1 of the "Privacy policy"', 'svbk-helpers' ),
					'required' => false,
					'type' => 'checkbox',
					'filter' => self::$defaultPolicyFilter,
				),
				'policy_directMarketing' => array(
					'label' => __( 'I accept the processing of the data referred to in Article 2 of the "Privacy policy"', 'svbk-helpers' ),
					'type' => 'checkbox',
					'required' => false,
					'filter' => self::$defaultPolicyFilter,
				),
			),
			$policyParts
		);

		return $this->policyParts;
	}

	public function insertInputField( $fieldName, $fieldParams, $after = null ) {

		if ( $after ) {
			$this->inputFields = Helpers\Form\Renderer::arrayKeyInsert( $this->inputFields, array(
				$fieldName => $fieldParams,
			), $after );
		} else {
			$this->inputFields[ $fieldName ] = $fieldParams;
		}

	}

	public function getInput( $field ) {
		return isset( $this->inputData[ $field ] ) ? $this->inputData[ $field ] : null;
	}

	public function processInput( $input_filters = array() ) {

		$index = filter_input( INPUT_POST, 'index', FILTER_VALIDATE_INT );

		if ( $index === false ) {
			$this->addError( __( 'Input data error', 'svbk-helpers' ) );
			return;
		} else {
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

		$this->inputData = parent::processInput( $input_filters );

		$this->validateInput();

	}

	protected function getField( $fieldName ) {

		if ( isset( $this->inputFields[ $fieldName ] ) ) {
			return $this->inputFields[ $fieldName ];
		} elseif ( isset( $this->policyParts[ $fieldName ] ) ) {
			return $this->policyParts[ $fieldName ];
		} else {
			return false;
		}

	}

	protected function validateInput() {

		$policyFields = array_keys( $this->policyParts );

		foreach ( $this->inputData as $name => $value ) {

			$field = $this->getField( $name );

			if ( ! $value && $this->fieldRequired( $field ) ) {
				$this->addError( $this->fieldError( $field, $name ), $name );

				if ( in_array( $name, $policyFields ) ) {
					$this->addError( $this->fieldError( $field, $name ), 'policy_all' );
				}
			}
		}

	}

	public function checkPolicy( $policyPart = 'policy_service' ) {

		if ( $this->getInput( 'policy_all' ) ) {
			return true;
		}

		if ( $this->getInput( $policyPart ) ) {
			return true;
		}

		return false;
	}

	public function processSubmission() {

		$this->processInput();

		if ( empty( $this->errors ) && $this->checkPolicy() ) {
			$this->mainAction();
		}
	}

	protected function mainAction(){ }

	protected function privacyNotice( $attr ) {

		$label = __( 'Privacy policy', 'svbk-helpers' );

		if ( shortcode_exists( 'privacy-link' ) ) {
			$privacy = do_shortcode( sprintf( '[privacy-link]%s[/privacy-link]', $label ) );
		} elseif ( isset( $attr['privacy_link'] ) && $attr['privacy_link'] ) {
			$privacy = sprintf( __( '<a href="%1$s" target="_blank">%2$s</a>', 'svbk-helpers' ), $attr['privacy_link'], $label );
		} else {
			$privacy = $label;
		}

		$text = sprintf( __( 'I declare I have read and accept the %s notification and I consent to process my personal data.', 'svbk-helpers' ), $privacy );

		if ( count( $this->policyParts ) > 1 ) {
			$flagsButton = '<a class="policy-flags-open" href="#policy-flags-' . $this->index . '">' . __( 'click here','svbk-helpers' ) . '</a>';
			$text .= '</label><label class="show-policy-parts">' . sprintf( __( 'To select the consents partially %s.', 'svbk-helpers' ), $flagsButton );
		}

		return $text;
	}

	public function renderParts( $action, $attr = array() ) {

		$output = array();

		$form_id = $this->field_prefix . self::PREFIX_SEPARATOR . $this->index;

		$output['formBegin'] = '<form class="svbk-form" action="' . esc_url( $this->submitUrl . '#' . $form_id ) . '" id="' . esc_attr( $form_id ) . '" method="POST">';

		foreach ( $this->inputFields as $fieldName => $fieldAttr ) {
			$output['input'][ $fieldName ] = $this->renderField( $fieldName, $fieldAttr );
		}

		$output['requiredNotice'] = '<div class="required-notice">' . __( 'Required fields', 'svbk-helpers' ) . '</div>';

		$output['policy']['begin'] = '<div class="policy-agreements">';

		if ( count( $this->policyParts ) > 1 ) {

			$output['policy']['global'] = $this->renderField( 'policy_all', array(
					'label' => $this->privacyNotice( $attr ),
					'type' => 'checkbox',
					'class' => 'policy-flags-all',
				)
			);
			$output['policy']['flags']['begin'] = '<div class="policy-flags" id="policy-flags-' . $this->index . '" style="display:none;" >';

			foreach ( $this->policyParts as $policy_part => $policyAttr ) {
				$output['policy']['flags'][ $policy_part ] = $this->renderField( $policy_part, $policyAttr );
			}

			$output['policy']['flags']['end'] = '</div>';

		} else {
			$output['policy']['global'] = $this->renderField( 'policy_service', array(
				'label' => $this->privacyNotice( $attr ),
				'type' => 'checkbox',
				)
			);
		}

		$output['policy']['end'] = '</div>';
		$output['input']['index']  = '<input type="hidden" name="index" value="' . $this->index . '" >';
		$output['submitButton'] = '<button type="submit" name="' . $this->fieldName( 'subscribe' ) . '" class="button">' . urldecode( $attr['submit_button_label'] ) . '</button>';
		$output['messages'] = '<div class="messages"><ul></ul><div class="close"><span>' . __( 'Close', 'svbk-helpers' ) . '</span></div></div>';
		$output['formEnd'] = '</form>';

		return $output;
	}

}
