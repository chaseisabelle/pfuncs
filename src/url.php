<?php
/**
 * checks if string is valid url
 *
 * @param mixed $string is ht e string to test
 * @param bool ping attempts to check if url is active (defaults to false) - functionality incomplete as of now
 * @return (bool) if url is good and false elsewise
 */
function is_url($string, $ping = false) {
    if ($ping) {
        error('Ping function incomplete.');
    }

    return filter_var($string, FILTER_VALIDATE_URL) === $string;
}

/**
 * gets the request (query string) from url and converts it to an array
 *
 * @param string $url is the url
 * @return array the request vars as key/value assoc array
 */
function url_request($url) {
    parse_str(parse_url($url, PHP_URL_QUERY), $request);

    return $request;
}

/**
 * strips off the query string from a url
 *
 * @param string $url is the url
 * @return string the url with no query string
 */
function url_strip($url) {
    $parsed = parse_url($url);

    if (!$parsed) {
        error('failed to parse url ' . spy($url));
    }

    $query = array_get($parsed, 'query', null);

    if (!$query) {
        return $url;
    }

    $scheme = array_get($parsed, 'scheme', null);
    $host   = array_get($parsed, 'host', null);
    $port   = intval(array_get($parsed, 'port', null));
    $path   = array_get($parsed, 'path', null);

    if ($port === 80 || !$port) {
        $port = '';
    }

    $url = '';

    if ($scheme) {
        $url = $scheme . '://';
    }

    if ($host) {
        $url .= $host;

        if ($port) {
            $url .= ':' . $port;
        }
    }

    return $url . $path;
}