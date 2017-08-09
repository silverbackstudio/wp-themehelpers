<?php
namespace Svbk\WP\Helpers\Networking;

use \WP_Error;

/**
 * IP Address Helper Class
 *
 * @package wp-themehelper
 * @subpackage Networking
 * @author Brando Meniconi <b.meniconi@silverbackstudio.it>
 * @since 3.1.15
 *
 * @property string $private_ranges[] List of all RFC 1918 private ranges.
 *
 * @uses \WP_Error;
 */
class IpAddress {

	public static $trusted_proxies = array();
	public static $private_ranges = array( '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16' );

	/**
	 * Get IP address
	 *
	 * @access public
	 *
	 * @param string $trusted_proxies[] List of all the proxies that are known to handle 'proxy_header' in known, safe manner.
	 * @param string $proxy_header Header that is used by the trusted proxy to refer to the original IP.
	 *
	 * @return string|WP_Error The IP address or WP_Error on failure.
	 */
	public static function getClientAddress( $proxy_header = 'HTTP_X_FORWARDED_FOR' ) {

		// Nothing to do without any reliable information
		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) || ! filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ) ) {
			return new WP_Error( 'no_ip_available', 'Request doesnt contain a valid IP address' );
		}

		$in_private_range = false;
		$is_trusted_proxy = false;

		foreach ( self::$private_ranges as $range ) {
			if ( self::inRange( $_SERVER['REMOTE_ADDR'], $range ) ) {
				$in_private_range = true;
				break;
			}
		}

		if ( in_array( $_SERVER['REMOTE_ADDR'], self::$trusted_proxies ) ) {
			$is_trusted_proxy = true;
		}

		if ( $in_private_range || $is_trusted_proxy ) {

			// Get the IP address of the client behind trusted proxy.
			if ( array_key_exists( $proxy_header, $_SERVER ) ) {

				// Header can contain multiple IP-s of proxies that are passed through.
				// Only the IP added by the last proxy (last IP in the list) can be trusted.
				$ips_from_proxy = explode( ',', $_SERVER[ $proxy_header ] );
				$client_ip = trim( end( $ips_from_proxy ) );

				// Validate just in case.
				if ( filter_var( $client_ip, FILTER_VALIDATE_IP ) ) {
					return $client_ip;
				} else {
					// Validation failed - beat the guy who configured the proxy.
					return new WP_Error( 'invalid_ip_config', 'Proxy has returned an invalid ip' );
				}
			}
		}

		// In all other cases, REMOTE_ADDR is the ONLY IP we can trust.
		return $_SERVER['REMOTE_ADDR'];

	}

	/**
	 * Check if an IP is in a specific network range
	 *
	 * @access public
	 *
	 * @param string $ip The IP to check.
	 * @param string $range The range to be checked against in standard or CIDR format.
	 *
	 * @return bool If the IP address is in the range or not.
	 */
	public static function inRange( $ip, $range ) {
		if ( strpos( $range, '/' ) == false ) {
			$range .= '/32';
		}
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal = ip2long( $range );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal = ~ $wildcard_decimal;

		return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
	}

}
