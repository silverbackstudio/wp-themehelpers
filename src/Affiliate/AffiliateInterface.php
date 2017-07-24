<?php

namespace Svbk\WP\Helpers\Affiliate;

interface AffiliateInterface {

		/**
		 * Tracks a sale.
		 *
		 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
		 * and to provide some background information or textual references.
		 *
		 * @param float  $amount Sale amount in the reference currency.
		 * @param string $product The product name.
		 * @param array  $user_data {
		 *    An array containing required user data.
		 *
		 *   @type string $first_name
		 *   @type string $last_name
		 *   @type string $email
		 * }
		 *
		 * @return bool
		 */
	public function sale( $amount, $user_data = array() );

		/**
		 * Tracks a lead.
		 *
		 * @access public
		 *
		 * @param array $user_data {
		 *   An array containing required user data.
		 *
		 *   @type string $first_name
		 *   @type string $last_name
		 *   @type string $email
		 * }
		 *
		 * @return WP_Error|array The response or WP_Error on failure.
		 */
	public function lead( $user_data = array() );


		/**
		 * Create an affiliate account.
		 *
		 * @access public
		 * @since 3.1.15
		 *
		 * @param array $user_data {
		 *   An array containing the user data.
		 *
		 *   @type string $username Username Must be a minimum 4 characters in length.	(ex. username=bailey08).
		 *   @type string $password Password. Must be a minimum 4 characters in length (ex. password=makemoney).
		 *   @type string $email Email. Must Be An Emaill Address (ex. email=ferrari@porsche.com).
		 *
		 * }
		 *
		 * @return WP_Error|array The response or WP_Error on failure.
		 */
	public function create_user( $user_data );
}
