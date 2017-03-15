<?php 

namespace Svbk\WP\Helpers;

class MailChimp extends \DrewM\MailChimp\MailChimp {
    
    public static $errorTranslations;

    public static function knownErrors(){
        
        if(empty(self::$errorTranslations)){
            self::$errorTranslations = array(
            md5('You’ve already sent this email to the subscriber.') => __('You have already received this whitepaper, check your inbox, use another email or request it via email.', 'svbk-widgets'),
            md5('The subscriber has already been triggered for this email.') => __('Your content is beeing delivered, please wait', 'svbk-widgets')
            );
        }
        
        return self::$errorTranslations;
        
    }

    public static function translateError($error){
        
        $errors = self::knownErrors();

        $error_hash = md5($error);
        
        if(array_key_exists($error_hash, $errors)){
            return $errors[$error_hash];
        } else {
            return $error;
        }
        
    }    
    
    public function subscribe($list_id, $email, $args=array()){

            $errors = array();

            $subscriber_hash = $this->subscriberHash($email);
    
            $user_info = $this->get("lists/$list_id/members/$subscriber_hash");
            
            if(!$this->success()){
                
                $user_info = $this->post("lists/$list_id/members", array_merge_recursive( array(
                    'email_address' => $email,
                    'status'        => 'subscribed',
                    'ip_signup'     => $_SERVER['REMOTE_ADDR'],
                    'ip_opt'        => $_SERVER['REMOTE_ADDR'],
                    'language'      => substr(get_locale(), 0, 2),
                ), $args) );     
                
                if(!$this->success()){
                    $errors[] = $this->getLastError();
                }
            } 
            
            if( isset($user_info['status']) && ( $user_info['status'] === 'unsubscribed' ) ) {
                
                $user_info = $this->patch("lists/$list_id/members/$subscriber_hash", [
                    'status' => 'subscribed',
                ]);     
                
                if(!$this->success()){
                    $errors[] = $this->getLastError();
                }
                
            }
            
            return $errors;
    }       
    
    private function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT)
    {

        $url = $this->api_endpoint . '/' . $method;
        $this->last_error = '';
        $this->request_successful = false;
        
        $response = array(
            'headers'     => null, // array of details from curl_getinfo()
            'httpHeaders' => null, // array of HTTP headers
            'body'        => null // content of the response
        );        
        
        $this->last_response = $response;
        $this->last_request = array(
            'method'  => $http_verb,
            'path'    => $method,
            'url'     => $url,
            'body'    => '',
            'timeout' => $timeout,
        );
        
        $request = array(
            'method'=>$http_verb,
            'headers'=>array(
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
                'Authorization' => 'apikey ' . $this->api_key,            
                'User-Agent' => 'DrewM/MailChimp-API/3.0 (github.com/drewm/mailchimp-api)'
                
            ),
            'timeout' => $timeout,
        );
        
        
        if(strtolower($http_verb) === 'get'){
            $url = http_build_query($args, '', '&');
        } else {
            $request['body'] = $args;
        }
        
        $response = wp_remote_request($url, $request);        
        
        if (is_wp_error($response)) {
            $this->last_error = $response->get_error_message();
        } else {
            $response['httpHeaders'] = $response['headers'];
            $responseContent = wp_remote_retrieve_body($response);
            
            if (isset($response['headers']['request_header'])) {
                $this->last_request['headers'] = $response['headers']['request_header'];
            }
        }
        
        $formattedResponse = $this->formatResponse($response);
        $this->determineSuccess($response, $formattedResponse, 0);
        return $formattedResponse;
    }
    
    private function findHTTPStatus($response, $formattedResponse)
    {
        
        $code = wp_remote_retrieve_response_code($response);
        
        if (!empty($response['headers']) && $code) {
            return (int) $code;
        }
        if (!empty($response['body']) && isset($formattedResponse['status'])) {
            return (int) $formattedResponse['status'];
        }
        
        return 418;
    }    
    
}