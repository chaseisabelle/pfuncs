<?php
/**
 * authorize your facebook app - if user has not allowed yet, this will redirect them to
 * the facebook allow page - if has allowed then youre all set
 *
 * @param resource $fb is the facebook api resopirce
 * @param string[] $scope is the list of permissions you want fromt he user
 */
function facebook_auth($fb, $scope = []) {
    $error = request_get('error_message', 0);

    if ($error && is_string(request_get('error_code', 0))) {
        trigger_error($error);
    }

    if (!facebook_authed($fb)) {
        redirect(facebook_url($fb, $scope));
    }
}

/**
 * checks if the facebook app has been authorized
 *
 * @return bool true if the app has been authed, false elsewise
 */
function facebook_authed() {
    return boolval(facebook_token());
}

/**
 * close a facebook app connection
 *
 * @param resource $fb is the facebook app resource
 */
function facebook_close(&$fb) {
    destroy($fb);
}

/**
 * open a facebook app connection
 *
 * @param string $id is the app id from facebook
 * @param string $secret is teh app secret from facebook
 * @param string $redirect is the redirect url to send the user back to if they're not allowed your app yet
 * @param resource the conneciton
 */
function facebook_open($id, $secret, $redirect = null) {
    return [
        'redirect' => $redirect ?: server_root(1),
        'id'       => $id,
        'secret'   => $secret
    ];
}

/**
 * this is basically an aggreagtor for api responses that are paged - it will iterate through a bunch
 * of pages and aggregate all the responses into a single data object
 *
 * @param mixed[] $page is the initial response page
 * @param string $direction is the direction of pages to iterate (can be 'next', 'previous', or null for both)
 * @param int $limit is the max number of pages ot iterate over
 * @return mixed[] the data for all the pages in a single assoc array
 */
function facebook_pager($page, $direction = null, $limit = null) {
    foreach ($direction ?: ['next', 'previous'] as $direction) {
        while ((is_null($limit) || $limit-- > 0) && !empty($page['paging'][$direction])) {
            $tmp = json_fread($page['paging'][$direction]);

            if (!$tmp) {
                break;
            }

            $page['data'] += $tmp['data'];
        }
    }

    return $page['data'];
}

/**
 * checks if user is logged into facebook and has allowed us and triggers error if not
 */
function facebook_allowed() {
    if (!facebook_authed()) {
        trigger_error('Must login to Facebook and authorize app.');
    }
}

/**
 * perform a query to hte facebook api
 *
 * @param resource $fb
 * @param string|string[] $fields is teh fields to fetch
 * @param string $uri is teh uri of teh api to query
 * @param string $method is teh requedt method
 * @return mixed[] the data as an assoc array
 */
function facebook_query($fb, $fields = '', $uri = 'me', $method = 'GET') {
    facebook_allowed();

    if ($method !== 'GET') {
        trigger_error($method . ' not supported for facebook yet');
    }

    $response = json_fread('https://graph.facebook.com/' . ltrim($uri, '/') . '?' . http_build_query([
        'access_token'    => facebook_token(),
        'appsecret_proof' => hash_hmac('sha256', facebook_token(), $fb['secret']),
        'fields'          => is_array($fields) ? implode(',', $fields) : $fields
    ]));

    $error = array_get(array_get($response, 'error', []), 'message', null);

    if ($error) {
        trigger_error($error);
    }

    return $response;
}

/**
 * generate a custom share url for facebook
 *
 * @param resource $fb is the facebook api connection resource
 * @param string $title is the titel for the facebook share
 * @param string $description is the description for teh share
 * @param string $picture is the url for the picture
 * @param string $url is the url - use null for current url
 * @param string $redirect
 * @return string the url for the share button/link
 */
function facebook_sharer($fb, $title = null, $description = null, $picture = null, $url = null, $redirect = null) {
    $url = $url ?: server_root();

    return 'https://www.facebook.com/dialog/feed?' . http_build_query([
        'redirect_uri' => $redirect ?: $url,
        'app_id'       => $fb['id'],
        'link'         => $url,
        'picture'      => $picture,
        'description'  => $description,
        'name'         => $title
    ]);
}

/**
 * get the login url for teh facebook app
 *
 * @param resource $fb is the facebook api connectionr esource
 * @param string[] $scope is the permissions scope (see facebook api docs)
 * @return string the login url
 */
function facebook_url($fb, $scope = []) {
    return 'https://www.facebook.com/v2.9/dialog/oauth?' . http_build_query([
        'client_id'    => $fb['id'],
        'redirect_uri' => $fb['redirect'],
        'state'        => facebook_state(true),
        'scope'        => is_string($scope) ? $scope : implode(',', $scope)
    ], null, '&');
}

/**
 * gets & sets the facebook state from & to the session
 *
 * @param bool $reset whether to reset the state or not
 * @return string the state
 */
function facebook_state($reset = false) {
    $key = md5('facebook_state');

    if ($reset || empty($_SESSION[$key])) {
        $state = '';

        while (strlen($state) < 16) {
            $state .= md5(uniqid(mt_rand(), true), true);
        }

        $_SESSION[$key] = $state;
    }

    $state = session_get($key, null);

    if (!$state) {
        trigger_error('Failed to set/get Facebook state.');
    }

    return $state;
}

/**
 * gets or sets the facebook access token for the user to the session
 *
 * @param string $token is the access token - use null to fetch from session
 * @return string the access token or null if token not set
 */
function facebook_token($token = null) {
    $key = md5('facebook_token');

    if ($token) {
        $_SESSION[$key] = $token;
    }

    return session_get($key, null);
}