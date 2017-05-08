<?php 

namespace Svbk\WP\Helpers\Payment;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\WebhookEvent;
use WP_Error;
use WP_REST_Request;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Log\LogLevel;

class PayPal {

    protected $apiContext;
    protected $logger;

    public static $sandbox = true;

    const BUTTON_ENDPOINT = 'https://www.%s/cgi-bin/webscr';

    const IPN_ENDPOINT = 'https://ipnpb.%s/cgi-bin/webscr';
    const IPN_VALID = 'VERIFIED';
    const IPN_INVALID = 'INVALID';

    const MODE_SANDBOX = 'sandbox';

    public function __construct( $params ){
        
        if( !empty( $params['clientId'] ) && !empty( $params['clientSecret'] ) ) {
            
            $this->apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $params['clientId'],
                    $params['clientSecret']
                )
            );
            
        }

        // If don't you want to use static file based configuration via PP_CONFIG_PATH
        if( $this->apiContext && !empty( $params['config'] ) ) {
            $this->apiContext->setConfig( $params['config'] );            
        } 
        
        $this->logger = new NullLogger;
        
    }

    public function setLogger( LoggerInterface $logger ){
        $this->logger = $logger;
    }

    public static function getEndpointUrl( $map, $sandbox = true){
        
        if( self::$sandbox ){
            return sprintf( $map, 'sandbox.paypal.com' );
        } else {
            return sprintf( $map, 'paypal.com' );
        }
        
    }
    
    public function verifyWebhook( WP_REST_Request $request, $webhook_id ) {
 
        $log = " - WebhookEvent Request received";
        
        try {
            
            $signatureVerification = new VerifyWebhookSignature();
            $signatureVerification->setAuthAlgo( $request->get_header('Paypal-Auth-Algo') );
            $signatureVerification->setTransmissionId( $request->get_header('Paypal-Transmission-Id') );
            $signatureVerification->setCertUrl( $request->get_header('Paypal-Cert-Url') );
            $signatureVerification->setWebhookId( $webhook_id ); 
            $signatureVerification->setTransmissionSig( $request->get_header('Paypal-Transmission-Sig') );
            $signatureVerification->setTransmissionTime( $request->get_header('Paypal-Transmission-Time') );
            
        } catch( InvalidArgumentException $ex ){
            $log .= " -- INVALID HEADERS!";
            $this->logger->debug( $log );
            return new WP_Error( 'invalid_request', 'Not a valid WebHook request', array( 'status' => 500 ) );
        }
        
        $webhookEvent = new WebhookEvent();
        $webhookEvent->fromArray( $request->get_json_params() );
        
        $log .= ' -- ' . $webhookEvent->getSummary();
        
        $signatureVerification->setWebhookEvent($webhookEvent);
        $PPrequest = clone $signatureVerification;
        
        try {
            $output = $signatureVerification->post( $this->apiContext );
        } catch (Exception $ex) {
            $log .= " -- ERROR: " . $ex->getMessage();
            $this->logger->debug( $log );
            return new WP_Error( 'invalid_payment_info', 'Could not contact payment provider:' . $ex->getMessage(), array( 'status' => 500 ) );
        }
        
        if( 'SUCCESS' !== $output->getVerificationStatus() ){
            $log .= " -- NOT VALIDATED: " . $output->getVerificationStatus() ;
            $this->logger->debug( $log );
            return new WP_Error( 'invalid_payment_info', 'Invalid Payment Info'  , array( 'status' => 200 ) );
        }
        
        $log .= " -- VALIDATED!" ;
        $this->logger->debug( $log );
    
        return true;
    }

    public function verifyIPN( WP_REST_Request $request ){

        $log = " - Paypal IPN Request received";    
        
        $response = wp_remote_post(
            self::getEndpointUrl( self::IPN_ENDPOINT ),
            array(
                'body' => array_merge( 
                    array(
                        'cmd' => '_notify-validate',
                    ),
                    $request->get_params()
                )
            )
        );
    
        if ( is_wp_error( $response ) ){
            $log .= 'ERROR:' . $response->get_error_message();
            $this->logger->debug( $log );
            return $response;
        }
    
        $result = wp_remote_retrieve_body( $response );
    
        $log .= ' -- STATUS: ' . $result;
        $log .= print_r( $request->get_params(), true );
        
        $this->logger->debug( $log );
        
        if( strcmp ($result, self::IPN_VALID ) == 0 ) {
            return true;
        } else {
            return new WP_Error( 'invalid_payment_info', 'Invalid Payment Info'  , array( 'status' => 200 ) );
        }
            
    }    
    
    public static function buttonUrl( $button_id, $args = array() ){
        
        $endpoint = self::getEndpointUrl( self::BUTTON_ENDPOINT );
        
        return add_query_arg( 
            array_merge( 
                array(
                    'cmd' => '_s-xclick',
                    'hosted_button_id' => $button_id,
                ),
                $args
            ),  
            self::getEndpointUrl( self::BUTTON_ENDPOINT ) 
        );
        
    }
        
}
