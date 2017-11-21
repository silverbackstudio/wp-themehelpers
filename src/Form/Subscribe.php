<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\Mailing\MailChimp;
use Svbk\WP\Helpers\Mailing\Mandrill;
use Mandrill_Error;

class Subscribe extends Submission {

	public $field_prefix = 'sbs';
	public $action = 'svbk_subscribe';
	
	public $mc_apikey = '';
	public $mc_list_id = '';
	public $mc_subscribe_update = false;	
	public $subscribeAttributes = array();

	public $md_apikey = '';
	public $senderTemplateName = '';

	public $messageDefaults = array();
	
	protected function mainAction() {
		
		if ( !empty( $this->mc_apikey ) && ! empty( $this->mc_list_id ) ) {

			$mc = new MailChimp( $this->mc_apikey );

			$errors = $mc->subscribe( 
				$this->mc_list_id, 
				trim( $this->getInput( 'email' ) ), 
				$this->subscribeAttributes(), 
				$this->mc_subscribe_update 
			);
	
			array_walk( $errors, array( $this, 'addError' ) );
		}
		
		$this->mandrillSend($this->senderTemplateName, $this->senderMessageParams());

	}
	
	protected function mandrillSend($template, $params, $text_fallback = false ){
		
		if ( ! empty( $this->md_apikey ) ) {
			try {
				$mandrill = new Mandrill( $this->md_apikey );

				if ( $template ) {
					$results = $mandrill->messages->sendTemplate( $template, array(), $params );
				} elseif ( $text_fallback ) {
					$results = $mandrill->messages->send( $params );
				} else {
					return;
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
			}			
		}	
		
	}

	protected function subscribeAttributes() {
		return array_merge_recursive(
			$this->subscribeAttributes,
			array(
				'merge_fields' => [
					'FNAME' => $this->getInput( 'fname' ),
					'MARKETING' => $this->getInput( 'policy_directMarketing' ) ? 'yes' : 'no',
				],
			)
		);
	}

	protected function getGlobalMergeTags() {
		return Mandrill::castMergeTags( $this->inputData, 'INPUT_' );
	}	
	
	protected function senderMessageParams() {

		return array_merge_recursive(
			Mandrill::$messageDefaults,
			(array) $this->messageDefaults,
			array(
				'text' => __( 'Thanks! We will contact you as soon as possible.', 'svbk-helpers' ),
				'to' => array(
					array(
						'email' => trim( $this->getInput( 'email' ) ),
						'name' => ucfirst( $this->getInput( 'fname' ) ),
						'type' => 'to',
					),
				),
				'global_merge_vars' =>$this->getGlobalMergeTags(),
				'metadata' => array(
					'website' => home_url( '/' ),
					),
				'merge' => true,
				'tags' => array(
					'subscribe-autoreply'
				),
			)
		);
	}

}
