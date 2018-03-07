<?php
namespace Svbk\WP\Helpers\Form;

use Exception;
use Svbk\WP\Email;

class Subscribe extends Submission {

	public $field_prefix = 'sbs';
	public $action = 'svbk_subscribe';
	
	public $transactional;
	public $marketing;
	public $sender;
	
	public $marketing_lists = array();
	public $user_template = '';
	
	protected function mainAction( $flags = array() ) {
		
		
		
		if ( $this->checkPolicy( 'policy_newsletter' ) && !empty( $this->marketing ) && !empty( $this->marketing_lists ) ) {
		
			$user = $this->getUser();
			$user->lists = $this->marketing_lists;
		
			try { 
				$this->marketing->create( $user, true );
			} catch( Exception $e ) {
				$this->addError( $e->getMessage() );
			}
		}

		if( empty( $flags['disable_user_email'] ) ) {
			$this->sendUserEmail( array('subscribe-form') );
		}

	}

	protected function sendUserEmail( $tags = array() ){
		
		if( $this->transactional && $this->user_template ) {
	
			$email = $this->getEmail();
			$email->to = $this->getUser();
			
			$email->tags = array_merge( $email->tags, $tags, array('user-email') );

			try { 
				$this->transactional->sendTemplate( $email, $this->user_template );
			} catch( Exception $e ) {
				$this->addError( $e->getMessage() );
			}		
			
		}		
		
	}
	
	protected function getUser(){
		
		$user = new Email\Contact([
			'email' => trim( $this->getInput( 'email' ) ),
			'first_name' => ucfirst( $this->getInput( 'fname' ) ),
		]);
		
		if( $this->getInput( 'lname' ) ) {
			$user->last_name = ucfirst( $this->getInput( 'lname' ) );
		}		
		
		return $user;
	}
	
	protected function getEmail(){
		
		$email = new Email\Message();
		$email->attributes = $this->inputData;
		
		if( $this->sender ) {
			$email->from = $this->sender;
		}
		
		return $email;
	}	
	

}
