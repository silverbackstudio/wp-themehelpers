<?php

namespace Svbk\WP\Helpers\Mailing;

use Svbk\WP\Helpers\Networking;
use GetResponse as GetResponseClient;

class GetResponse {

	/**
	 * Prints the HTML fields in subscrioption's admin panel
	 *
	 * @var GetResponse $client The GetResponse API client instance.
	 */
	public $client;

	/**
	 * The cache group to cache results
	 *
	 * @var string $cache_key
	 */
	public $cache_key = '';

	/**
	 * Constructor, instantiate the GetResponse API
	 *
	 * @param object $apikey The GetResponse API key.
	 *
	 * @return void
	 */
	public function __construct( $apikey ) {

		$this->client = new GetResponseClient( $apikey );
		$this->cache_key = md5( $apikey );
	}

	/**
	 * Get subscriber Id by email from a specific campaign
	 *
	 * @param string $email The subscriber email to search for.
	 * @param string $campaignId The campaign id.
	 *
	 * @return string|false The email address if exists, false if not.
	 */
	public function subscriberId( $email, $campaignId ) {

		$user_info = (array) $this->client->getContacts(array(
			'query' => array(
			'email' => $email,
			'campaignId' => $campaignId,
			),
			'fields' => 'contactId',
		));

		if ( 200 !== $this->client->http_status ) {
			return false;
		}

		if ( ! empty( $user_info ) && isset( $user_info[0] ) ) {
			return $user_info[0]->contactId;
		}

		return false;
	}

	/**
	 * Subscribe a user to a campaign
	 *
	 * @param string $campaignId The campaign ID.
	 * @param string $email The subscriber email.
	 * @param array  $args The subscription meta parameters.
	 * @param bool   $update If the user is already present, subscribe to the campaign.
	 *
	 * @return array The errors.
	 */
	public function subscribe( $campaignId, $email, $args = array(), $update = false ) {

		$errors = array();

		$subscriberId = $this->subscriberId( $email, $campaignId );

		if ( $campaignId ) {
			$args['campaign']['campaignId'] = $campaignId;
		}

		if ( $subscriberId && $update ) {

			$updateResult = $this->client->updateContact( $subscriberId, $args );

			if ( 200 !== $this->client->http_status ) {
				$errors[] = __( 'Unable to update the contact', 'svbk-helpers' );
			}
		} elseif ( ! $subscriberId ) {

			$args['email'] = $email;
			$args['dayOfCycle'] = 0;

			$ip_address = Networking\IpAddress::getClientAddress();

			if ( $ip_address && ! is_wp_error( $ip_address ) ) {
				$args['ipAddress'] = $ip_address;
			}

			$addResult = $this->client->addContact( $args );

			if ( 202 !== $this->client->http_status ) {
				$errors[] = __( 'Unable to subscribe the contact', 'svbk-helpers' );
			}
		}

		return $errors;
	}

	/**
	 * Get available campaigns via GetResponse API
	 *
	 * @return array
	 */
	public function getCampaigns() {

		$this->client->getCampaigns();

		$cache_key = 'svbk_helpers_campaigns_' . $this->cache_key;

		$campaigns = get_transient( $cache_key );

		if ( false === $campaigns ) {
			$campaigns = $this->client->getCampaigns();
			set_transient( $cache_key, $campaigns, 10 * MINUTE_IN_SECONDS );
		}

		if ( (200 !== $this->client->http_status) || empty( $campaigns ) ) {
			return array();
		}

		return $campaigns;

	}

}
