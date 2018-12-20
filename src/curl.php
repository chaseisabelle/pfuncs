<?php
/**
 * just checks if the curl lib is enabled
 *
 * @return bool true if curl is enabled - false if not
 */
function curl_enabled() {
    return function_exists('curl_version');
}

/**
 * gets the response code from a curl resource after a curl query
 *
 * @param resource $curl is the curl resource
 * @return int the response code
 */
function curl_code($curl) {
    return curl_info($curl, CURLINFO_HTTP_CODE);
}

/**
 * gets the content type for a curl resource after a curl query
 *
 * @param resource $curl is the curl resource
 * @return string the content type
 */
function curl_content_type($curl) {
    $content_type = curl_info($curl, CURLINFO_CONTENT_TYPE);

    if (is_null($content_type)) {
        trigger_error('Failed to determine document content type for ' . spy($curl) . '.');
    }

    return $content_type;
}

/**
 * basically a wrapper for curl_getinfo
 */
function curl_info($curl, $option = 0) {
    $info = curl_getinfo($curl, $option);

    if (is_bool($info)) {
        trigger_error('Failed to get ' . spy($option) . ' for ' . spy($curl) . '.');
    }

    return $info;
}

/**
 * gets/sets the url from/for a curl resource
 *
 * @param resource $curl is the curl resource
 * @param string $url to set
 * @return string the url
 */
function curl_url($curl, $url = null) {
    if (func_num_args() >= 2) {
        curl_option($curl, CURLOPT_URL, $url);
    }

    return curl_info($curl, CURLINFO_EFFECTIVE_URL);
}

/**
 * opens a curl connection resource
 *
 * @param string $url is the url to conenct to (defaults to null if you wanna set it later)
 * @param mixed[] $request is the query params - will be appnded to url as GET request params
 * @return resource the curl connection resource
 */
function curl_open($url = null, $request = []) {
    if ($request) {
        $url .= '?' . http_build_query($request);
    }

    $curl = curl_init($url);

    if (!$curl) {
        trigger_error('Failed to open cURL resource for ' . spy($url) . '.');
    }

    return curl_options($curl, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 60]);
}

/**
 * performs a curl query
 *
 * @param resource $curl is the conneciton resource
 * @return string the response
 */
function curl_query($curl) {
    $response = curl_exec($curl);

    if (!is_string($response)) {
        trigger_error('Failed to execute cURL query for ' . spy($curl) . '.');
    }

    return $response;
}

/**
 * checks a curl resource for a response code and triggers an error if the code is not what was expected
 *
 * @param resource $curl is the curl resource
 * @param int $code is the expected code
 */
function curl_require_code($curl, $code = 200) {
    if (curl_code($curl) !== intval($code)) {
        trigger_error(curl_url($curl) . ' responded with code ' . curl_code($curl) . ' instead of code ' . spy($code) . '.');
    }
}

/**
 * sets a single curl header
 *
 * @param resource $curl is the conneciton resource
 * @param string $header is the header
 * @return resource the modified curl resource
 */
function curl_header(&$curl, $header) {
    return curl_headers($curl, [$header]);
}

/**
 * sets the curl headers
 *
 * @param resource $curl
 * @param string[] $headers
 * @return resource the modified curl resource
 */
function curl_headers(&$curl, $headers = []) {
    return $headers ? curl_option($curl, CURLOPT_HTTPHEADER, $headers) : $curl;
}

/**
 * set the curl request method
 *
 * @param resource $curl
 * @param string $method is the request method - defaults to'GET'
 * @return resource the modified curl conneciton resource
 */
function curl_method(&$curl, $method = 'GET') {
    return curl_option($curl, CURLOPT_CUSTOMREQUEST, $method);
}

/**
 * a wrapper for curl_setopt basically
 */
function curl_option(&$curl, $option, $value) {
    return curl_options($curl, [$option => $value]);
}

/**
 * a wrapper for curl_setopts basically
 */
function curl_options(&$curl, $options) {
    if (!curl_setopt_array($curl, $options)) {
        trigger_error('Failed to initialize cURL options for ' . spy($curl) . '.');
    }

    return $curl;
}

/**
 * set the query params for a curl resource
 *
 * @param resource $curl
 * @param mixed[] $request is the request params
 * @param bool $post set post fields (defaults to true)
 * @return resource the modified curl resource
 */
function curl_request(&$curl, $request, $post = true) {
    if ($post) {
        return curl_option($curl, CURLOPT_POSTFIELDS, $request);
    }

    $url     = url_strip(curl_url($curl));
    $request = http_build_query(array_merge(url_request($url), $request));

    return curl_url($curl, "$url?$request");
}