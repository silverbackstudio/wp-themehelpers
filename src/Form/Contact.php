<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\Mailing\Mandrill;
use Mandrill_Error;

class Contact extends Subscribe {

	public $field_prefix = 'cnt';
	public $action = 'svbk_contact';

	public $templateName = '';
	
	public $recipientEmail = 'webmaster@silverbackstudio.it';
	public $recipientName = 'Webmaster';

	public function setInputFields( $fields = array() ) {

		return parent::setInputFields(
			array_merge(
				array(
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

	protected function mainAction() {
		$this->mandrillSend($this->templateName, $this->messageParams(), true);
		
		if ( empty( $this->errors ) && $this->checkPolicy( 'policy_newsletter' ) )	{
			parent::mainAction();
		} else {
			$this->mandrillSend( $this->senderTemplateName, $this->senderMessageParams() );
		}
		
	}

	protected function getRecipients() {
		return array(
			array(
				'email' => trim( $this->recipientEmail ),
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
				'headers' => array(
					'Reply-To' => trim( $this->getInput( 'email' ) ),
					),
				'to' => $this->getRecipients(),
				'global_merge_vars' => $this->getGlobalMergeTags(),
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

}
