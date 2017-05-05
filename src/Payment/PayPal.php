<?php 

namespace Svbk\WP\Helpers\Payment;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\WebhookEvent;
use WP_Error;
use WP_REST_Request;

class PayPal {
    
    public $use_sandbox = true;
    protected $apiContext;

    const IPN_ENDPOINT = 'https://ipnpb.%s/cgi-bin/webscr';
    const IPN_VALID = 'VERIFIED';
    const IPN_INVALID = 'INVALID';

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
    }

    public static function getEndpointUrl( $map, $sandbox = true){
        
        if( $sandbox ){
            return sprintf( $map, 'sandbox.paypal.com' );
        } else {
            return sprintf( $map, 'paypal.com' );
        }
        
    }
    
    public function verifyWebhook( WP_REST_Request $request, $webhook_id ) {
 
        $fp = fopen('request.log', 'a');
        fwrite($fp, "\n" . date('r') . " - WebhookEvent Request received" );

        try {
            
            $signatureVerification = new VerifyWebhookSignature();
            $signatureVerification->setAuthAlgo( $request->get_header('Paypal-Auth-Algo') );
            $signatureVerification->setTransmissionId( $request->get_header('Paypal-Transmission-Id') );
            $signatureVerification->setCertUrl( $request->get_header('Paypal-Cert-Url') );
            $signatureVerification->setWebhookId( $webhook_id ); 
            $signatureVerification->setTransmissionSig( $request->get_header('Paypal-Transmission-Sig') );
            $signatureVerification->setTransmissionTime( $request->get_header('Paypal-Transmission-Time') );
            
        } catch( InvalidArgumentException $ex ){
            fwrite($fp, " -- INVALID HEADERS!");
            return new WP_Error( 'invalid_request', 'Not a valid WebHook request', array( 'status' => 500 ) );
        }
        
        $webhookEvent = new WebhookEvent();
        $webhookEvent->fromArray( $request->get_json_params() );
        
        fwrite($fp, ' -- ' . $webhookEvent->getSummary() );
        
        $signatureVerification->setWebhookEvent($webhookEvent);
        $PPrequest = clone $signatureVerification;
        
        try {
            $output = $signatureVerification->post( $this->apiContext );
        } catch (Exception $ex) {
            fwrite($fp, " -- ERROR: " . $ex->getMessage() );
            return new WP_Error( 'invalid_payment_info', 'Could not contact payment provider:' . $ex->getMessage(), array( 'status' => 500 ) );
        }
        
        if( 'SUCCESS' !== $output->getVerificationStatus() ){
            fwrite($fp, " -- NOT VALIDATED: " . $output->getVerificationStatus() );
            return new WP_Error( 'invalid_payment_info', 'Invalid Payment Info'  , array( 'status' => 200 ) );
        }
        
        fwrite($fp, " -- VALIDATED!");
        fclose($fp);  

        return true;
    }

    public function verifyIPN( WP_REST_Request $request, $sandbox = true){

        $fp = fopen('request.log', 'a');
        fwrite($fp, "\n" . date('r') . " - Paypal IPN Request received" );    
        
        $response = wp_remote_post(
            self::getEndpointUrl( self::IPN_ENDPOINT, $sandbox ),
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
            return $response;
        }
    
        $result = wp_remote_retrieve_body( $response );
    
        fwrite($fp, ' -- STATUS: ' . $result );
        
        if( strcmp ($result, self::IPN_VALID ) == 0 ) {
            return true;
        } else {
            return new WP_Error( 'invalid_payment_info', 'Invalid Payment Info'  , array( 'status' => 200 ) );
        }
            
        fclose($fp);  
    }    
        
}
