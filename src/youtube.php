<?php
/**
 * close and destroy youtube connection
 *
 * @param resource $youtube is the youtube connection
 */
function youtube_close(&$youtube) {
    curl_close($youtube);
    destroy($youtube);
}

/**
 * opens a youtube connection
 *
 * @return resource youtube connection
 */
function youtube_open() {
    return curl_open();
}

/**
 * query youtube
 *
 * @param resource $youtube is the youtube connection
 * @param string $endpoint is the endpoint
 * @param array $request is the request params
 * @param string $method is the request method
 * @return array the response object
 */
function youtube_query($youtube, $endpoint, $request = [], $method = 'GET') {
    $youtube = curl_method($youtube, $method);
    $youtube = curl_request($youtube, $request);

    return xml_to_array(curl_query(curl_url($youtube, 'http://gdata.youtube.com/' . $endpoint)));
}

/**
 * fetches a user's youtube uploads
 *
 * @param resource $youtube youtube connection
 * @param string $username the user's username
 * @return array the response object
 */
function youtube_uploads($youtube, $username) {
    return youtube_query($youtube, 'feeds/api/users/' . $username . '/uploads')['feed'];
}

/**
 * fetches info about a youtube user
 *
 * @param resource $youtube the yotube connection
 * @param string $username
 * @return array the response object
 */
function youtube_user($youtube, $username) {
    return youtube_query($youtube, 'feeds/api/users/' . $username)['entry'];
}