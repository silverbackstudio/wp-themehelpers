<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\Mailing\Mandrill;
use Mandrill_Error;

class Contact extends Submission {

	public static $defaultPolicyFilter = array(
		'filter' => FILTER_VALIDATE_BOOLEAN,
		'flags' => FILTER_NULL_ON_FAILURE,
	);

	public $field_prefix = 'cnt';
	public $action = 'svbk_contact';

	public $md_apikey = '';
	public $templateName = '';
	public $senderTemplateName = '';
	public $recipientEmail = 'webmaster@silverbackstudio.it';
	public $recipientName = 'Webmaster';

	public $messageDefaults = array();

	public function setInputFields( $fields = array() ) {

		return parent::setInputFields(
			array_merge(
				array(
					'subject' => array(
						'required' => true,
						'label' => __( 'Subject', 'svbk-helpers' ),
						'type' => 'text',
						'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
						'error' => __( 'Please enter a subject', 'svbk-helpers' ),
					),
					'request' => array(
						'required' => true,
						'label' => __( 'Message', 'svbk-helpers' ),
						'type' => 'textarea',
						'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
						'error' => __( 'Please write a brief description of your request', 'svbk-helpers' ),
					),
				),
				$fields
			)
		);

	}

	public function setPolicyParts( $policyParts = array() ) {

		$this->policyParts = array_merge_recursive(
			array(
				'policy_service' => array(
					'label' => __( 'Ho letto e accetto le condizioni generali e l\'informativa della privacy.', 'svbk-helpers' ),
					'required' => true,
					'type' => 'checkbox',
					'error' => __( 'Policy terms must be accepted', 'svbk-helpers' ),
					'filter' => self::$defaultPolicyFilter,
					),
				),
			$policyParts
		);

		return $this->policyParts;
	}


	protected function mainAction() {

		if ( ! empty( $this->md_apikey ) ) {

			try {
				$mandrill = new Mandrill( $this->md_apikey );

				if ( $this->templateName ) {
					$results = $mandrill->messages->sendTemplate( $this->templateName, array(), $this->messageParams() );
				} else {
					$results = $mandrill->messages->send( $this->messageParams() );
				}

				if ( ! is_array( $results ) || ! isset( $results[0]['status'] ) ) {
					throw new Mandrill_Error( __( 'The requesto to our mail server failed, please try again later or contact the site owner.', 'svbk-helpers' ) );
				}

				$errors = $mandrill->getResponseErrors( $results );

				foreach ( $errors as $error ) {
					$this->addError( $error, 'email' );
				}

				if ( $this->senderTemplateName ) {
					$results = $mandrill->messages->sendTemplate( $this->senderTemplateName, array(), $this->senderMessageParams() );
				} else {
					$results = $mandrill->messages->send( $this->senderMessageParams() );
				}

				if ( ! is_array( $results ) || ! isset( $results[0]['status'] ) ) {
					throw new Mandrill_Error( __( 'The requesto to our mail server failed, please try again later or contact the site owner.', 'svbk-helpers' ) );
				}

				$errors = $mandrill->getResponseErrors( $results );

				foreach ( $errors as $error ) {
					$this->addError( $error, 'email' );
				}
			} catch ( Mandrill_Error $e ) {
				$this->addError( $e->getMessage() );
			}// End try().
		}// End if().

	}

	protected function getRecipients() {
		return array(
			array(
				'email' => $this->recipientEmail,
				'name' => $this->recipientName,
				'type' => 'to',
			),
		);
	}

	protected function messageParams() {

		return array_merge_recursive(
			Mandrill::$messageDefaults,
			(array) $this->messageDefaults,
			array(
				'text' => $this->getInput( 'request' ),
				'subject' => $this->getInput( 'subject' ),
				'headers' => array(
					'Reply-To' => $this->getInput( 'email' ),
					),
				'to' => $this->getRecipients(),
				'global_merge_vars' => Mandrill::castMergeTags( $this->inputData, 'INPUT_' ),
				'metadata' => array(
					'website' => home_url( '/' ),
					),
				'merge' => true,
				'tags' => array(
					'contact-request'
					),
				)
		);
	}

	protected function senderMessageParams() {

		return array_merge_recursive(
			Mandrill::$messageDefaults,
			(array) $this->messageDefaults,
			array(
				'text' => __( 'Thanks! We will contact you as soon as possible.', 'svbk-helpers' ),
				'to' => array(
					array(
						'email' => $this->getInput( 'email' ),
						'name' => trim( $this->getInput( 'fname' ) . ' ' . $this->getInput( 'lname' ) ),
						'type' => 'to',
					),
				),
				'global_merge_vars' => Mandrill::castMergeTags( $this->inputData, 'INPUT_' ),
				'metadata' => array(
					'website' => home_url( '/' ),
					),
				'merge' => true,
				'tags' => array(
					'contact-autoreply'
					),
				)
		);
	}

}
