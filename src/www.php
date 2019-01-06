<?php
/**
 * gets the extention of the requested content
 *
 * @return string the content extention - returns 'html' if no extention requested
 */
function content_ext() {
    $uri = trim(server_get('REQUEST_URI', ''), '/');
    $uri = parse_url($uri, PHP_URL_PATH);

    if (preg_match('/\.(?P<ext>\w+)$/i',$uri , $matches) && !empty($matches['ext'])) {
        return $matches['ext'];
    }

    return 'html';
}

/**
 * gets a value from the cookie or a default if value doesnt exist
 *
 * @param mixed $k is the key of the value
 * @param mixed $d is the default
 * @param bool $c case insensitve lookup
 * @return mixed the value of the key in the cookie or the default
 */
function cookie_get($k, $d, $c = false) {
    return array_get($_COOKIE, $k, $d, $c);
}

/**
 * for use with web stuff - redirects use to new lcoation
 *
 * @param string $to tis the url to rediect to
 */
function redirect($to) {
    header('Location: ' . $to);

    exit;
}

/**
 * gets a value from teh request array - also trims it if it's a string
 *
 * @param string $key is the name of the reuqest arg
 * @param mixed $default is teh default value if the request arg is not found
 * @param bool $case case insenstive lookup
 * @return mixed the value
 */
function request_get($key, $default, $case = false) {
    $value = array_get($_REQUEST, $key, $default, $case);

    if (is_string($value)) {
        $value = trim($value);
    }

    return $value;
}

/**
 * gets a value from the server array or default if key doesnt exist
 *
 * @param string $k i sthe key
 * @param mixed $d is the default
 * @param bool $c case insensive lookup
 * @return mixed the value of the key in the server array or default if no key
 */
function server_get($k, $d, $c = false) {
    return array_get($_SERVER, $k, $d, $c);
}

/**
 * builds the root url for the server
 *
 * @param bool $slash tell weather to add the trailing slash
 * @return string the root url
 */
function server_root($slash = 0) {
    $root = 'http';

    if (server_get('HTTPS', 0)) {
        $root .= 's';
    }

    $root .= '://' . $_SERVER['SERVER_NAME'];

    if (($port = intval(server_get('SERVER_PORT', 80))) !== 80) {
        $root .= ':' . $port;
    }

    $root = rtrim($root, '/');

    if ($slash) {
        $root .= '/';
    }

    return $root;
}

/**
 * builds the current url
 *
 * @param bool $query set to true to return the query string also (i.e. ?id=2&name=whatever)
 * @return the current url
 */
function requested_url($query = false) {
    $https = server_get('HTTPS', false) ? 's' : '';
    $root  = server_root();
    $uri   = server_get('REQUEST_URI', '');
    $url   = 'http' . $https . '://' . $root . $uri;

    if (!$query) {
        $url = url_strip($url);
    }

    return $url;
}

/**
 * gets a value from the session array or default if key doesnt exist
 *
 * @param string $k i sthe key
 * @param mixed $d is the default
 * @param bool $c
 * @return mixed the value of the key in the session array or default if no key
 */
function session_get($k, $d, $c = false) {
    return array_get($_SESSION, $k, $d, $c);
}

/**
 * gets an uploaded file or default if no file uploaded
 *
 * @param string $k is the name of teh file
 * @param mixed $d is the dwefault
 * @return mixed the file tmp path or the default
 */
function upload_get($k, $d, $c = false) {
    return array_get($_FILES, $k, ['tmp_name' => $d], $c)['tmp_name'];
}

/**
 * gets the requested language header
 *
 * @param string $default is the default language
 * @return string the requested langiaeg
 */
function requested_locale($default = 'en') {
    return server_get('Accept-Language', $default, true);
}