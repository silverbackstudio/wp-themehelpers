<?php
namespace Svbk\WP\Helpers\Form;

use Svbk\WP\Helpers\Mailing\MailChimp;

class Subscribe extends Submission {

	public $field_prefix = 'sbs';
	public $action = 'svbk_subscribe';
	public $subscribeAttributes = array();

	public $mc_apikey = '';
	public $mc_list_id = '';
	public $mc_subscribe_update = false;

	protected function mainAction() {

		if ( ! empty( $this->mc_apikey ) && ! empty( $this->mc_list_id ) ) {
			$mc = new MailChimp( $this->mc_apikey );

			$errors = $mc->subscribe( $this->mc_list_id, trim( $this->getInput( 'email' ) ), $this->subscribeAttributes(), $this->mc_subscribe_update );

			array_walk( $errors, array( $this, 'addError' ) );
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


}
