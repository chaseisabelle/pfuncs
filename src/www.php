<?php
/**
 * use this function to get the action - for example, if the url is http://example.com/foo/bar, then "bar" would be the action, so this function would return "bar"
 *
 * @return string the action for the request uri
 */
function action() {
    return controller_and_action()['action'];
}

/**
 * check if an action exists for the current site
 *
 * @param string $a is the action in question
 * @param string $c is the controller - defaults to requested controller
 * @return bool true if the action exists for the controller, false elsewise
 */
function action_exists($a, $c = null) {
    if (func_num_args() === 1) {
        $c = controller();
    }

    return controller_exists($c) && in_array($a, actions());
}

/**
 * gets the list of actions
 *
 * @return string[] an array of the actions on your web app for the controller
 * @param string $controller is the controller to check for actions in - defaults to requested controller
 */
function actions($controller = null) {
    $actions = [];

    if (is_dir($path = CONTENT_DIR . '/' . (is_null($controller) ? controller() : $controller))) {
        foreach (scan_dir($path) as $file) {
            if (preg_match('/[\w-]+\.php$/', $file)) {
                $actions[] = preg_remove('/\.php$/', $file);
            }
        }
    }

    return $actions;
}

/**
 * gets the extention of the requested content
 *
 * @return string the content extention - returns 'html' if no extention requested
 */
function content_ext() {
    if (preg_match('/\.(?P<ext>\w+)$/i', parse_url(trim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH), $matches) && !empty($matches['ext'])) {
        return strtolower($matches['ext']);
    }

    return 'html';
}

/**
 * get the content extentions
 *
 * @return string[] the content extentions
 */
function content_exts() {
    return [
        'html',
        'json',
        'xml',
        'txt',
        'js',
        'css'
    ];
}

/**
 * gets the mime type type based on the requested content extention
 *
 * @param string $ext is the content extention to get the type for
 * @return string the mime type for the requested content extention (i.e. localhost/api/get-id.json would have a content type of 'application/json')
 */
function content_type($ext = null) {
    if (!$ext && is_www_content()) {
        return mime_content_type(www_content_path());
    }

    switch ($ext ?: content_ext()) {
        case 'json':
            return 'application/json';
        case 'xml':
            return 'text/xml';
        case 'txt':
            return 'text/plain';
        case 'js':
            return 'application/javascript';
        case 'css':
            return 'text/css';
        case 'html':
        default:
            return 'text/html';
    }

    pfunc_error('IDK WTF happned :-(');
}

/**
 * get the requested controller
 *
 * @return string the requested controller
 */
function controller() {
    return controller_and_action()['controller'];
}

/**
 * gets the controller and the action from the requesrtd uri in an associative array
 *
 * @return string[] array with requested controller and action
 */
function controller_and_action() {
    if (!is_array($_['uri'] = explode('/', ($_['uri'] = parse_url(trim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH))))) {
        pfunc_error('Failed to parse requested URI ' . spy($_SERVER['REQUEST_URI']) . '.');
    }

    if ($_['uri'][0] === '') {
        unset($_['uri'][0]);
    }

    if (!isset($_['uri'][1])) {
        $action = is_www_content() ? '' : 'index';
    } else {
        $action = preg_remove('/\.\w+$/i', $_['uri'][1]);
    }

    return [
        'controller' => isset($_['uri'][0]) ? preg_remove('/\.\w+$/i', $_['uri'][0]) : 'index',
        'action'     => $action
    ];
}

/**
 * check if a controller exists
 *
 * @param string $c is the controller in question
 * @return bool true if so, false if no
 */
function controller_exists($c) {
    return in_array($c, controllers());
}

/**
 * lists the controllers in your web app
 *
 * @return string[] the controllers in your web app
 */
function controllers() {
    $controllers = [];

    foreach (scan_dir(CONTENT_DIR) as $path => $file) {
        if (is_dir($path) && preg_match('/^[\w-]+$/', $file) && $file !== APP_NAME) {
            $controllers[] = $file;
        }
    }

    return $controllers;
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
 * check if action is requested action
 *
 * @param string $action is the action in question
 * @param string $controller is the controller to the action - defaults to current controller
 * @return bool true if action is active - false elsewise
 */
function is_action_active($action, $controller = null) {
    return is_controller_active(is_null($controller) ? controller() : $controller) && $action === action();
}

/**
 * attempts to check if browser is android browser
 *
 * @return bool true if browser is android false elsewise
 */
function is_android_browser() {
    return is_int(stripos($_SERVER['HTTP_USER_AGENT'], 'android'));
}

/**
 * checks if controller is requested controller
 *
 * @return bool
 */
function is_controller_active($controller) {
    return $controller === controller();
}

/**
 * check if browser is ipad
 *
 * @return bool
 */
function is_ipad_browser() {
    return is_int(stripos($_SERVER['HTTP_USER_AGENT'], 'ipad'));
}

/**
 * check if browser is iphone browser
 *
 * @return bool
 */
function is_iphone_browser() {
    return is_int(stripos($_SERVER['HTTP_USER_AGENT'], 'iphone'));
}

/**
 * check if user is on mobil browser
 *
 * @return bool
 */
function is_mobile_browser() {
    return preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', server_get('HTTP_USER_AGENT', '')) ? true : false;
}

/**
 * checks if in www env
 *
 * @return bool true if in webs erver env or false elsewise
 */
function is_www() {
    return preg_match('/apache/i', php_sapi_name()) ? true : false;
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
 * checks if the content requested should passthru and not be handled by php (i.e. images, etc)
 *
 * @return bool true if the content requested is not handled by php (i.e. images, etc)
 */
function is_www_content() {
    return !in_array(content_ext(), content_exts());
}

/**
 * gets the path to the requested content
 *
 * @return string the path to the requested content
 */
function www_content_path() {
    return WWW_DIR . '/' . implode('/', array_filter(controller_and_action())) . '.' . content_ext();
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