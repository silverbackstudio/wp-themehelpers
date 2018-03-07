<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Email;
use Exception;

class Contact extends Subscribe {

	public $field_prefix = 'cnt';
	public $action = 'svbk_contact';

	public $admin_subject = '';
	public $admin_template = '';
	
	public $recipient;

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

	protected function mainAction( $flags = array() ) {
		
		$this->sendAdminEmail( array('contact-form') );
		$this->sendUserEmail( array('contact-form') );			

		if ( empty( $this->errors ) ){
			parent::mainAction( array( 'disable_user_email' => true ) );
		} 
		
	}

	protected function sendAdminEmail( $tags = array() ){
		
		if( !$this->transactional ) {
			$this->addError( __( 'Unable to send email, please contact the website owner', 'svbk-helpers' ) );
			return;
		}
		
		if( !$this->recipient ) {
			$this->recipient = new Email\Contact( 
				[
					'email' => get_bloginfo('admin_email'),
					'first_name' => 'Website Admin',
				]
			);
		}		
		
		$email = $this->getEmail();
		$email->tags = array_merge( $email->tags, $tags, array('admin-email') );
		$email->to = $this->recipient;
		$email->reply_to = $this->getUser();
		
		if( $this->admin_template ) {
	
			try { 
				$this->transactional->sendTemplate( $email, $this->admin_template );
			} catch( Exception $e ) {
				$this->addError( $e->getMessage() );
			}		
			
		} else {
			
			$email->subject = $this->admin_subject ?: __('Contact Request (no-template)', 'svbk-helpers');
			$email->text_body = $this->getInput('request');
			$email->html_body = '<p>' . $this->getInput('request') .  '</p>';
			
			if( $email->from ) {
				$email->from = new Email\Contact(
					[
						'email' => $_SERVER['SERVER_ADMIN'] ?: 'webmaster@silverbackstudio.it',
						'first_name' => 'Website Admin',
					]				
				);
			}

			try { 
				$this->transactional->send( $email );
			} catch( Exception $e ) {
				$this->addError( $e->getMessage() );
			}			
			
		}		
		
	}

}
