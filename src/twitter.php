<?php
/**
 * close and destroy twitter connection
 *
 * @param resource $twitter is the connection
 */
function twitter_close(&$twitter) {
    destroy($twitter);
}

/**
 * perform a GET query to twitter api
 *
 * @param resource $twitter is the twitter connection
 * @param string $service is the endpoint
 * @param array $request is the request params
 * @return array the response object
 */
function twitter_get($twitter, $service, $request) {
    return twitter_query($twitter, $service, 'GET', $request);
}

/**
 * open a twitter connection
 *
 * @param string $consumer_key
 * @param string $consumer_secret
 * @param string $access_token
 * @param string $access_secret
 * @return resource the twitter connection
 */
function twitter_open($consumer_key, $consumer_secret, $access_token, $access_secret) {
    return [
        'oauth_access_token'        => $access_token,
        'oauth_access_token_secret' => $access_secret,
        'consumer_key'              => $consumer_key,
        'consumer_secret'           => $consumer_secret
    ];
}

/**
 * perform POST query to twitter api
 *
 * @param resource $twitter is the twitter connection
 * @param string $service is the endpoint
 * @param array $request is hte request params
 * @return array the response object
 */
function twitter_post($twitter, $service, $request) {
    return twitter_query($twitter, $service, 'POST', $request);
}

/**
 * get a user's twitter profile
 *
 * @param resource $twitter the twitter connection
 * @param string $username the user's twitter username
 * @return array the response object
 */
function twitter_profile($twitter, $username) {
    return twitter_get($twitter, 'users/show', ['screen_name' => $username]);
}

/**
 * query the twitter api
 *
 * @param resource $twitter the twitter connection
 * @param string $service the endpoint
 * @param string $method the request method
 * @param array $request the request params
 * @return array the response object
 */
function twitter_query($twitter, $service, $method, $request = []) {
    $service .= '.json';

    twitter_load();

    $twitter = new TwitterAPIExchange($twitter);

    switch (strtoupper($method)) {
        case 'GET':
            if ($request) {
                $twitter = $twitter->setGetfield('?' . http_build_query($request));
            }

            $twitter = $twitter->buildOauth('https://api.twitter.com/1.1/' . $service, $method);

            break;
        case 'POST':
            $twitter = $twitter->buildOauth('https://api.twitter.com/1.1/' . $service, $method);

            if ($request) {
                $twitter = $twitter->setPostfields($request);
            }

            break;
        default:
            pfunc_error('Invalid Twitter request method ' . spy($method) . '.');
    }

    $response = $twitter->performRequest(false);

    if (isset($response['error'])) {
        $response['errors'] = [['message' => $response['error']]];
    }

    if (isset($response['errors'])) {
        pfunc_error('Twitter says "' . implode('" and "', array_column($response['errors'], 'message')) . '".');
    }

    return $response;
}

/**
 * generates a twitter share/tweet url
 *
 * @param string $url the url to share/tweet
 * @param string $text the share/tweet text
 * @return string the share/tweet url
 */
function twitter_sharer($url, $text) {
    return 'https://twitter.com/intent/tweet?' . http_build_query([
        'original_referer' => server_root(),
        'text'             => $text,
        'tw_p'             => 'tweetbutton',
        'url'              => $url
    ]);
}

/**
 * generates twitter links to all @'s users
 *
 * @deprecated
 * @param string $text is the string to taggify
 * @return string the taggified text
 */
function twitter_taggify($text) {
    $text = preg_replace('#@([\\d\\w]+)#', '<a href="http://twitter.com/$1">$0</a>', $text);

    return preg_replace('/\s#([\\d\\w]+)/', '<a href="http://twitter.com/search?q=%23$1&src=hash">' . trim('$0') . '</a>', $text);
}

/**
 * post a tweet to twitter
 *
 * @param resource $twitter the twitter connection
 * @param string $tweet the tweet
 * @param array $request any additional request params
 * @return array the response object
 */
function twitter_tweet($twitter, $tweet, $request = []) {
    return twitter_post($twitter, 'statuses/update', array_merge($request, ['status' => $tweet]));
}

/**
 * fetch tweets
 *
 * @param resource $twitter the twitter connection
 * @param array $request the request params
 * @return array the response object
 */
function twitter_tweets($twitter, $request = []) {
    return twitter_get($twitter, 'statuses/user_timeline', $request);
}

/**
 * loads the TwitterAPIExchange object if not already loaded
 */
function twitter_load() {
    if (class_exists('TwitterAPIExchange')) {
        return;
    }

    /**
     * Twitter-API-PHP : Simple PHP wrapper for the v1.1 API
     *
     * PHP version 5.3.10
     *
     * @category Awesomeness
     * @package  Twitter-API-PHP
     * @author   James Mallison <me@j7mbo.co.uk>
     * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
     * @link     http://github.com/j7mbo/twitter-api-php
     */
    class TwitterAPIExchange
    {
        private $oauth_access_token;
        private $oauth_access_token_secret;
        private $consumer_key;
        private $consumer_secret;
        private $postfields;
        private $getfield;
        protected $oauth;
        public $url;

        /**
         * Create the API access object. Requires an array of settings::
         * oauth access token, oauth access token secret, consumer key, consumer secret
         * These are all available by creating your own application on dev.twitter.com
         * Requires the cURL library
         *
         * @param array $settings
         */
        public function __construct(array $settings)
        {
            require_curl();

            if (!isset($settings['oauth_access_token'])
                || !isset($settings['oauth_access_token_secret'])
                || !isset($settings['consumer_key'])
                || !isset($settings['consumer_secret']))
            {
                throw new Exception('Make sure you are passing in the correct parameters');
            }

            $this->oauth_access_token = $settings['oauth_access_token'];
            $this->oauth_access_token_secret = $settings['oauth_access_token_secret'];
            $this->consumer_key = $settings['consumer_key'];
            $this->consumer_secret = $settings['consumer_secret'];
        }

        /**
         * Set postfields array, example: array('screen_name' => 'J7mbo')
         *
         * @param array $array Array of parameters to send to API
         *
         * @return TwitterAPIExchange Instance of self for method chaining
         */
        public function setPostfields(array $array)
        {
            if (!is_null($this->getGetfield()))
            {
                throw new Exception('You can only choose get OR post fields.');
            }

            if (isset($array['status']) && substr($array['status'], 0, 1) === '@')
            {
                $array['status'] = sprintf("\0%s", $array['status']);
            }

            $this->postfields = $array;

            return $this;
        }

        /**
         * Set getfield string, example: '?screen_name=J7mbo'
         *
         * @param string $string Get key and value pairs as string
         *
         * @return \TwitterAPIExchange Instance of self for method chaining
         */
        public function setGetfield($string)
        {
            if (!is_null($this->getPostfields()))
            {
                throw new Exception('You can only choose get OR post fields.');
            }

            $search = array('#', ',', '+', ':');
            $replace = array('%23', '%2C', '%2B', '%3A');
            $string = str_replace($search, $replace, $string);

            $this->getfield = $string;

            return $this;
        }

        /**
         * Get getfield string (simple getter)
         *
         * @return string $this->getfields
         */
        public function getGetfield()
        {
            return $this->getfield;
        }

        /**
         * Get postfields array (simple getter)
         *
         * @return array $this->postfields
         */
        public function getPostfields()
        {
            return $this->postfields;
        }

        /**
         * Build the Oauth object using params set in construct and additionals
         * passed to this method. For v1.1, see: https://dev.twitter.com/docs/api/1.1
         *
         * @param string $url The API url to use. Example: https://api.twitter.com/1.1/search/tweets.json
         * @param string $requestMethod Either POST or GET
         * @return \TwitterAPIExchange Instance of self for method chaining
         */
        public function buildOauth($url, $requestMethod)
        {
            if (!in_array(strtolower($requestMethod), array('post', 'get')))
            {
                throw new Exception('Request method must be either POST or GET');
            }

            $consumer_key = $this->consumer_key;
            $consumer_secret = $this->consumer_secret;
            $oauth_access_token = $this->oauth_access_token;
            $oauth_access_token_secret = $this->oauth_access_token_secret;

            $oauth = array(
                'oauth_consumer_key' => $consumer_key,
                'oauth_nonce' => time(),
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_token' => $oauth_access_token,
                'oauth_timestamp' => time(),
                'oauth_version' => '1.0'
            );

            $getfield = $this->getGetfield();

            if (!is_null($getfield))
            {
                $getfields = str_replace('?', '', explode('&', $getfield));
                foreach ($getfields as $g)
                {
                    $split = explode('=', $g);
                    $oauth[$split[0]] = $split[1];
                }
            }

            $base_info = $this->buildBaseString($url, $requestMethod, $oauth);
            $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
            $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
            $oauth['oauth_signature'] = $oauth_signature;

            $this->url = $url;
            $this->oauth = $oauth;

            return $this;
        }

        /**
         * Perform the actual data retrieval from the API
         *
         * @param boolean $return If true, returns data.
         *
         * @return string json If $return param is true, returns json data.
         */
        public function performRequest($return = true)
        {
            if (!is_bool($return))
            {
                throw new Exception('performRequest parameter must be true or false');
            }

            $header = array($this->buildAuthorizationHeader($this->oauth), 'Expect:');

            $getfield = $this->getGetfield();
            $postfields = $this->getPostfields();

            $options = array(
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_HEADER => false,
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true
            );

            if (!is_null($postfields))
            {
                $options[CURLOPT_POSTFIELDS] = $postfields;
            }
            else
            {
                if ($getfield !== '')
                {
                    $options[CURLOPT_URL] .= $getfield;
                }
            }

            $feed = curl_open();
            curl_options($feed, $options);
            $json = curl_query($feed);
            curl_close($feed);

            return $return ? $json : json_to_array($json);
        }

        /**
         * Private method to generate the base string used by cURL
         *
         * @param string $baseURI
         * @param string $method
         * @param array $params
         *
         * @return string Built base string
         */
        private function buildBaseString($baseURI, $method, $params)
        {
            $return = array();
            ksort($params);

            foreach($params as $key=>$value)
            {
                $return[] = "$key=" . $value;
            }

            return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return));
        }

        /**
         * Private method to generate authorization header used by cURL
         *
         * @param array $oauth Array of oauth data generated by buildOauth()
         *
         * @return string $return Header used by cURL for request
         */
        private function buildAuthorizationHeader($oauth)
        {
            $return = 'Authorization: OAuth ';
            $values = array();

            foreach($oauth as $key => $value)
            {
                $values[] = "$key=\"" . rawurlencode($value) . "\"";
            }

            $return .= implode(', ', $values);
            return $return;
        }
    }
}