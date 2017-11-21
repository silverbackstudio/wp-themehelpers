<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\Mailing\Mandrill;
use Mandrill_Error;

class Download extends Subscribe {

	public $field_prefix = 'dl';
	public $action = 'svbk_download';

	public function processInput( $input_filters = array() ) {

		$input_filters['fid'] = FILTER_VALIDATE_INT;

		return parent::processInput( $input_filters );
	}
	
	protected function getGlobalMergeTags() {

		$mergeTags = parent::getGlobalMergeTags();

		$mergeTags[] = array(
			'name' => 'DOWNLOAD_URL',
			'content' => esc_url( $this->getDownloadLink() ),
		);

		return $mergeTags;
	}

	protected function mainAction() {

		if ( empty( $this->errors ) && $this->checkPolicy( 'policy_newsletter' ) )	{
			parent::mainAction();
		} else {
			$this->mandrillSend( $this->senderTemplateName, $this->senderMessageParams() );
		}
		
	}

	protected function getDownloadLink() {
		return wp_get_attachment_url( $this->getInput( 'fid' ) );
	}

	protected function senderMessageParams() {

		$messageParams = parent::senderMessageParams();
		
		$messageParams['html'] = sprintf( __(' Thanks for your request, please download your file <a href="%s">here</a>', 'svbk-helpers' ) , $this->getDownloadLink() );
		$messageParams['tags'] = array( 'download-request' );
		
		return $messageParams;
	}

	public function renderParts( $action, $attr = array() ) {

		$output = parent::renderParts( $action, $attr );
		$output['input']['file'] = '<input type="hidden" name="' . $this->fieldName( 'fid' ) . '" value="' . $attr['file'] . '" >';

		return $output;
	}

	protected function validateInput() {

		parent::validateInput();

		$post = get_post( (int) $this->getInput( 'fid' ) );

		if ( ! $post || ('attachment' != $post->post_type) ) {
			$this->addError( __( 'The specified download doesn\'t exists anymore. Please contact site owner', 'svbk-helpers' ) );
		}

	}

}
