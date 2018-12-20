<?php
/**
 * close and destroy instagram connection
 *
 * @param resource $i the connection
 */
function instagram_close(&$i) {
    destroy($i);
}

/**
 * open an instagram connection
 *
 * @param string $id
 * @param string $secret
 * @return resource the instagram connection
 */
function instagram_open($id, $secret) {
    return [
        'id'     => $id,
        'secret' => $secret
    ];
}

/**
 * fetch a user's instagram profile
 *
 * @param resource $instagram the instagram connection
 * @param string $username
 * @return array the response object
 */
function instagram_profile($instagram, $username) {
    return array_first(instagram_query($instagram, 'users/search', ['q' => $username])['data']);
}

/**
 * query instagram api
 *
 * @param resource $instagram the connection
 * @param string $endpoint
 * @param array $request the request params
 * @return array the response object
 */
function instagram_query($instagram, $endpoint, $request = []) {
    $request['client_id'] = $instagram['id'];

    $response = json_to_array(url_get('https://api.instagram.com/v1/' . $endpoint, $request));

    if (!empty($response['meta']['error_message'])) {
        trigger_error($response['meta']['error_message']);
    }

    return $response;
}

/**
 * gets recents
 *
 * @param resource $instagram
 * @param string $user_id
 * @return array
 */
function instagram_recent($instagram, $user_id) {
    return instagram_query($instagram, 'users/' . $user_id . '/media/recent/')['data'];
}