<?php

use Illuminate\Support\Facades\URL;

if (!function_exists('curl_init')) {
    throw new Exception('OpenIDConnect needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('OpenIDConnect needs the JSON PHP extension.');
}

class Client extends Eloquent {

	    /**
     * @var string arbitrary id value
     */
    private $clientID;

    private $clientURL;

    /*
     * @var string arbitrary name value
     */
    private $clientName;

    /**
     * @var string arbitrary secret value
     */
    private $clientSecret;

    /**
     * @var array holds the provider configuration
     */
    private $providerConfig = array();

    /**
     * @var string http proxy if necessary
     */
    private $httpProxy;

    /**
     * @var string full system path to the SSL certificate
     */
    private $certPath;

    /**
     * @var string if we aquire an access token it will be stored here
     */
    private $accessToken;

    /**
     * @var array holds scopes
     */
    private $scopes = array();

    /**
     * @var array holds a cache of info returned from the user info endpoint
     */
    private $userInfo = array();

    /**
     * @var array holds authentication parameters
     */
    private $authParams = array();

    /**
     * @param $provider_url string optional
     *
     * @param $client_id string optional
     * @param $client_secret string optional
     *
     */

    public function __construct($provider_url = null, $client_id = null, $client_secret = null, $client_url = null) {
        $this->setProviderURL($provider_url);
        $this->clientID = $client_id;
        $this->clientSecret = $client_secret;
        $this->clientURL = $client_url;
    }
	
	    /**
     *
     * @param $attribute
     *
     * Attribute        Type    Description
     * user_id            string    REQUIRED Identifier for the End-User at the Issuer.
     * name            string    End-User's full name in displayable form including all name parts, ordered according to End-User's locale and preferences.
     * given_name        string    Given name or first name of the End-User.
     * family_name        string    Surname or last name of the End-User.
     * middle_name        string    Middle name of the End-User.
     * nickname        string    Casual name of the End-User that may or may not be the same as the given_name. For instance, a nickname value of Mike might be returned alongside a given_name value of Michael.
     * profile            string    URL of End-User's profile page.
     * picture            string    URL of the End-User's profile picture.
     * website            string    URL of End-User's web page or blog.
     * email            string    The End-User's preferred e-mail address.
     * verified        boolean    True if the End-User's e-mail address has been verified; otherwise false.
     * gender            string    The End-User's gender: Values defined by this specification are female and male. Other values MAY be used when neither of the defined values are applicable.
     * birthday        string    The End-User's birthday, represented as a date string in MM/DD/YYYY format. The year MAY be 0000, indicating that it is omitted.
     * zoneinfo        string    String from zoneinfo [zoneinfo] time zone database. For example, Europe/Paris or America/Los_Angeles.
     * locale            string    The End-User's locale, represented as a BCP47 [RFC5646] language tag. This is typically an ISO 639-1 Alpha-2 [ISO639‑1] language code in lowercase and an ISO 3166-1 Alpha-2 [ISO3166‑1] country code in uppercase, separated by a dash. For example, en-US or fr-CA. As a compatibility note, some implementations have used an underscore as the separator rather than a dash, for example, en_US; Implementations MAY choose to accept this locale syntax as well.
     * phone_number    string    The End-User's preferred telephone number. E.164 [E.164] is RECOMMENDED as the format of this Claim. For example, +1 (425) 555-1212 or +56 (2) 687 2400.
     * address            JSON object    The End-User's preferred address. The value of the address member is a JSON [RFC4627] structure containing some or all of the members defined in Section 2.4.2.1.
     * updated_time    string    Time the End-User's information was last updated, represented as a RFC 3339 [RFC3339] datetime. For example, 2011-01-03T23:58:42+0000.
     *
     * @return mixed
     *
     */
    public function requestUserInfo($attribute = null) {

        // Check to see if the attribute is already in memory
        if (!empty($attribute) and array_key_exists($attribute, $this->userInfo)) {
            return $this->userInfo[$attribute];
        }


        $user_info_endpoint = $this->getProviderConfigValue("userinfo_endpoint");

        $schema = 'openid';

        $user_info_endpoint .= "?schema=" . $schema;


        $user_json = json_decode($this->fetchURL($user_info_endpoint, null, array("Authorization: Bearer ".$this->accessToken)),true);

        if(array_key_exists('sub', $user_json)){

            $this->userInfo = $user_json;

        if (empty($attribute)) {
            return $this->userInfo;
        }

        if (array_key_exists($attribute, $this->userInfo)) {
            return $this->userInfo[$attribute];
        }

        }

        return null;

    }



    /**
     * @param $provider_url
     */
    public function setProviderURL($provider_url) {
        $this->providerConfig['issuer'] = $provider_url;
    }


    public function setAccessToken($token){
        $this->accessToken = $token;
    }

    public function getAccessToken(){
        return $this->accessToken;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function authenticate() {

        // Do a preemptive check to see if the provider has thrown an error from a previous redirect
        if (Session::has('oauth_error')) {
            throw new Exception("Error: " . Session::get('oauth_error') . " Description: " . Session::get('oauth_error_description'));
        }
        
        // If we have an authorization code then proceed to request a token
        if (Session::has("oauth_code") and Session::has("oauth_state")) {
        
            $code = Session::get("oauth_code");
            $state = Session::get("oauth_state");
            $token_json = $this->requestTokens($code);
            
            // Throw an error if the server returns one
            if (isset($token_json->error)) {
                throw new Exception($token_json->error_description);
            }

            // Do an OpenID Connect session check
            //dd(Session::get('local_state') .' and '. Session::get('oauth_state') );
            if (!Cache::has($state)) {
               throw new Exception("Unable to determine state");
            }

            $claims = $this->decodeJWT($token_json->id_token, 1);

            
            // If this is a valid claim
            if ($this->verifyJWTclaims($claims)) {
                
                Cache::forget($claims->nonce);
                Cache::forget($state);
                Session::forget('oauth_nonce');
                Session::forget('oauth_state');
                

                // Save the access token
                $this->accessToken = $token_json->access_token;

                // Success!
                return true;

            } else {
                throw new Exception ("Unable to verify JWT claims");
            }

        } else {
            $this->requestAuthorization();
            return false;
        }

    }

    /**
     * @param $scope - example: openid, given_name, etc...
     */
    public function addScope($scope) {
        $this->scopes = array_merge($this->scopes, (array)$scope);
    }

    /**
     * @param $param - example: prompt=login
     */
    public function addAuthParam($param) {
        $this->authParams = array_merge($this->authParams, (array)$param);
    }

    /**
     * Get's anything that we need configuration wise including endpoints, and other values
     *
     * @param $param
     * @throws Exception
     * @return string
     *
     */
    private function getProviderConfigValue($param) {

        // If the configuration value is not available, attempt to fetch it from a well known config endpoint
        // This is also known as auto "discovery"
        if (!isset($this->providerConfig[$param])) {
            $well_known_config_url = rtrim($this->getProviderURL(),"/") . "/.well-known/openid-configuration";
            $value = json_decode($this->fetchURL($well_known_config_url))->{$param};

            if ($value) {
                $this->providerConfig[$param] = $value;
            } else {
                throw new Exception("The provider {$param} has not been set. Make sure your provider has a well known configuration available.");
            }

        }

        return $this->providerConfig[$param];
    }

    /**
     * Gets the URL of the current page we are on, encodes, and returns it
     *
     * @return string
     */
    protected function getRedirectURL() {

        /**
         * Thank you
         * http://stackoverflow.com/questions/189113/how-do-i-get-current-page-full-url-in-php-on-a-windows-iis-server
         */

        $base_page_url = (array_key_exists('HTTPS', $_SERVER) and $_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $base_page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $base_page_url .= $_SERVER["SERVER_NAME"];
        }

        $request_uri = explode("?", $_SERVER['REQUEST_URI']);
        $base_page_url .= reset($request_uri);

        return $base_page_url;
    }

    /**
     * Used for arbitrary value generation for nonces and state
     *
     * @return string
     */
    protected function generateRandString() {
        return md5(uniqid(rand(), TRUE));
    }

    /**
     * Start Here
     * @return void
     */
    private function requestAuthorization() {

        $auth_endpoint = $this->getProviderConfigValue("authorization_endpoint");
        $response_type = "code";

        // Generate and store a nonce in the session
        // The nonce is an arbitrary value
        $nonce = $this->generateRandString();
        Cache::put($nonce, 'oauth_nonce',5);

        // State essentially acts as a session key for OIDC
        $state = $this->generateRandString();
        Cache::put($state, 'oauth_state',5);
        

        $auth_params = array_merge($this->authParams, array(
            'response_type' => $response_type,
            'redirect_uri' => $this->getRedirectURL(),
            'client_id' => $this->clientID,
            'nonce' => $nonce,
            'state' => $state
        ));
        
        // If the client has been registered with additional scopes
        if (sizeof($this->scopes) > 0) {
            $auth_params = array_merge($auth_params, array('scope' => implode(' ', $this->scopes)));
        }

        $auth_endpoint .= '?' . http_build_query($auth_params, null, '&');


        $this->redirect($auth_endpoint);

    }


    /**
     * Requests ID and Access tokens
     *
     * @param $code
     * @return mixed
     */
    private function requestTokens($code) {


        $token_endpoint = $this->getProviderConfigValue("token_endpoint");

        $grant_type = "authorization_code";
        
        $token_params = array(
            'grant_type' => $grant_type,
            'code' => $code,
            'redirect_uri' => $this->getRedirectURL(),
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret
        );

        // Convert token params to string format
        $token_params = http_build_query($token_params, null, '&');

        return json_decode($this->fetchURL($token_endpoint, $token_params));

    }

    /**
     * @param object $claims
     * @return bool
     */
    private function verifyJWTclaims($claims) {
        
        return (($claims->iss == $this->getProviderURL())
            && (($claims->aud == $this->clientURL) || (is_array($claims->aud) and in_array($this->clientURL, $claims->aud)))
            && (Cache::get($claims->nonce) == 'oauth_nonce'));

    }

    /**
     * @param $jwt string encoded JWT
     * @param int $section the section we would like to decode
     * @return object
     */
    private function decodeJWT($jwt, $section = 0) {

        $parts = explode(".", $jwt);
        return json_decode(base64_decode($parts[$section]));
    }



    /**
     * @param $url
     * @param null $post_body string If this is set the post type will be POST
     * @throws Exception
     * @return mixed
     */
    protected function fetchURL($url, $post_body = null, $headers = null) {

        // OK cool - then let's create a new cURL resource handle
        $ch = curl_init();

        // Determine whether this is a GET or POST
        if ($post_body != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

            // Default content type is form encoded
            $content_type = 'application/x-www-form-urlencoded';
            // Determine if this is a JSON payload and add the appropriate content type
            if (is_object(json_decode($post_body))) {
                $content_type = 'application/json';
            }

            $http_headers = array(
                "Content-Type: {$content_type}",
                'Content-Length: ' . strlen($post_body)
            );

            if(!empty($headers)){
                $http_headers = array_merge ($http_headers, $headers);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);

        }

        if(!empty($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }


        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $url);

        if (isset($this->httpProxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->httpProxy);
        }

        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

        /**
         * Set cert
         * Otherwise ignore SSL peer verification
         */
        if (isset($this->certPath)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->certPath);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }

        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        
        // Download the given URL, and return output
        $output = curl_exec($ch);


        if (curl_exec($ch) === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        // Close the cURL resource, and free system resources
        curl_close($ch);

        return $output;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getProviderURL() {

        if (!isset($this->providerConfig['issuer'])) {
            throw new Exception("The provider URL has not been set");
        } else {
            return $this->providerConfig['issuer'];
        }
    }

    /**
     * @param $url
     */
    public function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    /**
     * @param $httpProxy
     */
    public function setHttpProxy($httpProxy) {
        $this->httpProxy = $httpProxy;
    }

    /**
     * @param $certPath
     */
    public function setCertPath($certPath) {
        $this->certPath = $certPath;
    }

    /**
     *
     * Use this to alter a provider's endpoints and other attributes
     *
     * @param $array
     *        simple key => value
     */
    public function providerConfigParam($array) {
        $this->providerConfig = array_merge($this->providerConfig, $array);
    }

    /**
     * @param $clientSecret
     */
    public function setClientSecret($clientSecret) {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param $clientID
     */
    public function setClientID($clientID) {
        $this->clientID = $clientID;
    }


    /**
     * Dynamic registration
     *
     * @throws Exception
     */
    public function register() {

        $registration_endpoint = $this->getProviderConfigValue('registration_endpoint');

        $send_object = (object)array(
            'redirect_uris' => array($this->getRedirectURL()),
            'client_name' => $this->getClientName()
        );

        $response = $this->fetchURL($registration_endpoint, json_encode($send_object));

        $json_response = json_decode($response);

        // Throw some errors if we encounter them
        if ($json_response === false) {
            throw new Exception("Error registering: JSON response received from the server was invalid.");
        } elseif (isset($json_response->{'error_description'})) {
            throw new Exception($json_response->{'error_description'});
        }

        $this->setClientID($json_response->{'client_id'});

        // The OpenID Connect Dynamic registration protocol makes the client secret optional
        // and provides a registration access token and URI endpoint if it is not present
        if (isset($json_response->{'client_secret'})) {
            $this->setClientSecret($json_response->{'client_secret'});
        } else {
            throw new Exception("Error registering:
                                                    Please contact the OpenID Connect provider and obtain a Client ID and Secret directly from them");
        }

    }

    /**
     * @return mixed
     */
    public function getClientName() {
        return $this->clientName;
    }

    /**
     * @param $clientName
     */
    public function setClientName($clientName) {
        $this->clientName = $clientName;
    }

    /**
     * @return string
     */
    public function getClientID() {
        return $this->clientID;
    }

    /**
     * @return string
     */
    public function getClientSecret() {
        return $this->clientSecret;
    }


    public static function getExceptionHttpHeaders($error)
    {
        $headers = array();
        switch ($error) {
            case 401:
                $headers[] = 'HTTP/1.1 401 Unauthorized';
                break;
            case 500:
                $headers[] = 'HTTP/1.1 500 Internal Server Error';
                break;
            case 501:
                $headers[] = 'HTTP/1.1 501 Not Implemented';
                break;
            case 400:
            default:
                $headers[] = 'HTTP/1.1 400 Bad Request';
        }
    }

}
