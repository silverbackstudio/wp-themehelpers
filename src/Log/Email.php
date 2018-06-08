<?php
namespace Svbk\WP\Helpers\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Email extends AbstractLogger {

	public $defaultSubject = 'WP Logger Message';

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 * @return null
	 */
	public function log( $level, $message, array $context = array() ) {

		$subject = "[{$level}] ";
		$subject .= empty( $context['subject'] ) ? $this->defaultSubject : $context['subject'];

		wp_mail( 'meniconi.brando@gmail.com', $subject, $message );
	}

}
