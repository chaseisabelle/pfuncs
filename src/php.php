<?php
/**
 * gets a constant by its name or a default if const undefined
 *
 * @param string $name is the constant name
 * @param mixed $default is teh value to return if the constant is undefined
 * @return mixed the value
 */
function const_get($name, $default) {
    return defined($name) ? constant($name) : $default;
}

/**
 *  minifies css - just a stub right now
 *
 * @param string $css is the css
 * @return string the minified css
 */
function css_minify($css) {
    return $css;
}

/**
 * destroys a variable
 *
 * @param mixed $var is the variable to destroy
 */
function destroy(&$var) {
    $var = null;

    unset($var);
}

/**
 * trims any trailing slashes in a directory string
 *
 * @param string $dir is the directory
 * @return string the trimmemd dir
 */
function dir_trim($dir) {
    return rtrim($dir, '/');
}

/**
 * like shell_exec
 *
 * @param string $command is the command to exec ute
 * @return string the output  from the command
 */
function execute($command) {
    if (!is_array($command)) {
        exec($command, $output, $code);

        if ($code !== 0) {
            trigger_error('Failed to execute ' . spy($command) . ' (' . spy($code) . ').');
        }

        return implode("\n", $output);
    }

    foreach ($command as $k => $c) {
        $command[$k] = execute($c);
    }

    return implode("\n", $command);
}

/**
 * attempts to get a $GLOBALS variable
 *
 * @param mixed $key is the key
 * @param mixed $default is what to return if the global key doesnt exist
 * @param boolean $case
 * @return mixed the value
 */
function global_get($key, $default = null, $case = false) {
    return array_get($GLOBALS, $key, $default, $case);
}

/**
 * minifies html - just a stub right now
 *
 * @param string $html is the html ot minify
 * @return string the minified html
 */
function html_minify($html) {
    return $html;
}

/**
 * include a file only if it exists
 *
 * @param string $file is the path to the file
 * @param bool $once include once or not
 * @return mixed
 */
function include_if_exists($file, $once = false) {
    if ($once) {
        return include_once_if_exists($file);
    }

    if (file_exists($file)) {
        return include($file);
    }
}

/**
 * include a file once only if it exists
 *
 * @param (string)file is the path to the file
 * @return mixed
 */
function include_once_if_exists($file) {
    if (file_exists($file)) {
        return include_once($file);
    }
}

function include_or_print($file) {
    return preg_match('/\.php$/i', $file) ? include($file) : print(file_read($file));
}

/**
 * checks if var is email address
 *
 * @param mixed $e is the variable to check
 * @return bool true if var is email address and false elsewise
 */
function is_email($e) {
    return filter_var($e, FILTER_VALIDATE_EMAIL) === $e;
}

/**
 * check if file is an image
 *
 * @param string $f is the file
 * @return bool true if file is an image and false elsewise
 */
function is_image($f) {
    return is_int(@exif_imagetype($f));
}

/**
 * check if string/number is a phone number
 *
 * @param mixed $p is the var in question
 * @return bool true if var is phone number false elsewise
 */
function is_phone($p) {
    return strlen(ltrim(preg_remove('/[^\d]+/', $p), 1)) === 10;
}

/**
 * minifies javascript code - just a stub now
 *
 * @param string $js is the javascript code
 * @return string the js minified
 */
function js_minify($js) {
    return $js;
}

/**
 * wrapper for rename - moves a file
 *
 * @param string $from is the source
 * @param string $to is the destination
 */
function mv($from, $to) {
    if (!rename($from, $to)) {
        trigger_error('Failed to rename file ' . spy($from) . ' to ' . spy($to) . '.');
    }
}

/**
 * minify some php code
 *
 * @param string $php is the php code
 * @return string the minified php code
 */
function php_minify($php) {
    $tmp = tempnam('/tmp', uniqid());

    file_write($tmp, $php);

    return php_strip_whitespace($tmp);
}

/**
 * removes comments from php code - not really tested that well - i'd refrain from using it
 *
 * @param string $file
 * @return string the code without comments
 */
function php_remove_comments($file) {
    $buffer   = '';
    $tokens = [T_COMMENT];

    if (defined('T_DOC_COMMENT')) {
        $tokens[] = T_DOC_COMMENT;
    }

    if (defined('T_ML_COMMENT')) {
        $tokens[] = T_ML_COMMENT;
    }

    foreach (token_get_all($file) as $token) {
        if (is_array($token)) {
            if (in_array($token[0], $tokens)) {
                continue;
            }

            $token = $token[1];
        }

        $buffer .= $token;
    }

    return $buffer;
}

/**
 * php linter
 *
 * @param string $php the php code or php file
 */
function php_lint($php) {
    if (is_file($php)) {
        return execute('php -l ' . realpath($php));
    }

    $file = tempnam(sys_get_temp_dir(), '');

    file_write($file, $php);

    return php_lint($file);
}

/**
 * chooses a random passed arg and gives it back
 *
 * @return mixed a random arg
 */
function rand_arg() {
    return array_rand_value(func_get_args());
}

/**
 * returns a random oolean value
 *
 * @return bool a random bool value
 */
function rand_bool() {
    return rand() % 2 ? true : false;
}

/**
 * require an entire dir of php files
 *
 * @param string $dir is the dir to incluid
 * @param bool $recursive is a flgag
 */
function require_dir($dir, $recursive = false) {
    foreach (scan_dir($dir) as $file) {
        if (is_file($file = dir_trim($dir) . '/' .  $file) && preg_match('/\w+\.php$/i', $file)) {
            include($file);
        }

        if ($recursive && is_dir($file)) {
            require_dir($file, $recursive);
        }
    }
}

/**
 * deletes a file
 *
 * @param string $file is teh file to delete
 */
function rm($file) {
    if (!unlink($file)) {
        trigger_error('Failed to delete file ' . spy($file) . '.');
    }
}

/**
 * a wrapper for scandir - gets all files that arent hidden and uses full file path a key and file name as value
 *
 * @param string $dir is the dir to scN
 * @return array the files
 */
function scan_dir($dir) {
    $files = [];

    foreach (scandir($dir) as $file) {
        if (preg_match('/^\.\.?$/', $file)) {
            continue;
        }

        $files[dir_trim($dir) . '/' . $file] = $file;
    }

    return $files;
}

/**
 * this function is deprecated - see https://developers.google.com/image-search/v1/jsondevguide?hl=fr
 *
 * @deprecated
 * @param string $q is the search request
 * @return array and array with the response data
 */
function search_google_images($q) {
    return json_to_array(url_get('https://ajax.googleapis.com/ajax/services/search/images', ['v' => '1.0', 'q' => $q]));
}

/**
 * attemps to send an email
 *
 * @param string $to is the email to send to
 * @param string $from is the email to send from
 * @param string $subject is the subjecfty
 * @param string $text is the text body of teh email
 */
function send_email($to, $from, $subject, $text) {
    $boundary = uniqid();

    $headers = "MIME-Version: 1.0\r\n";

    $headers .= "From: $from \r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Content-Type: multipart/alternative;boundary=$boundary\r\n";

    $message = "\r\n\r\n--" . $boundary . "\r\n";
    $message .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
    $message .= $text;
    $message .= "\r\n\r\n--$boundary\r\n";

    if (!mail('', $subject, $message, $headers, '-f ' . $from)) {
        trigger_error('Failed to send email.');
    }
}

/**
 * spies on a variuable - returns dump of variable type and value
 *
 * @param mixed $x is the var
 * @return string the dump
 */
function spy($x) {
    switch (1) {
        case func_num_args() > 1:
            $a = [];

            foreach (func_get_args() as $x) {
                $a[] = spy($x);
            }

            return implode(' and ', $a);
        case is_null($x):
            return 'NULL';
        case is_bool($x):
            return $x ? 'TRUE' : 'FALSE';
        case is_string($x):
            return '"' . $x . '"';
        case is_numeric($x):
            return $x;
        case is_array($x):
            break;
        case is_object($x):
            return get_class($x);
        case is_scalar($x):
            return strval($x);
        default:
            return gettype($x);
    }

    switch (count($x)) {
        case 0:
            return '[]';
        case 1:
            return '[' . spy(array_shift($x)) . ']';
        case 2:
            return '[' . spy(array_shift($x)) . ', ' . spy(array_pop($x)) . ']';
        default:
            return '[' . spy(array_shift($x)) . ', ..., ' . spy(array_pop($x)) . ']';
    }

    trigger_error('This is madness! Madness? This is PHP!');
}

/**
 * dumps vars - applies <pre> tags if in www env
 */
function wtf() {
    if (is_cli()) {
        foreach (func_get_args() as $arg) {
            var_dump($arg);
        }

        return;
    }

    $args = [];

    foreach (func_get_args() as $arg) {
        ob_start();

        var_dump($arg);

        $args[] = htmlspecialchars(ob_get_contents());

        ob_end_clean();
    }

    print '<pre style="text-align:left;font-size:13px;">' . implode('<pre style="text-align:left;font-size:13px;"></pre>', $args) . '</pre>';
}