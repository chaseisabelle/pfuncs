<?php
// throwable interface for errors - throws exception on error
function error($error, $status = 500) {
    if (is_array($error)) {
        $message = $error['message'] ?? 'Unknown error.';
        $file    = $error['file'] ?? __FILE__;
        $line    = $error['line'] ?? __LINE__;
        $status  = $error['status'] ?? $status;
        $code    = $error['code'] ?? $status;
    } else {
        $message = $error;
    }

    $error = new Error($message, $status);

    $error->PFUNCS_ERROR = [
        'message' => $message,
        'file'    => $file ?? __FILE__,
        'line'    => $line ?? __LINE__,
        'trace'   => debug_backtrace(false),
        'status'  => $status,
        'code'    => $code ?? $status
    ];

    throw $error;
}

// throwable interface for errors - throws exception on error
set_error_handler(function ($code, $message, $file, $line) {
    error([
        'message' => $message,
        'file'    => $file,
        'line'    => $line,
        'status'  => 500,
        'code'    => $code
    ]);
}, E_ALL | E_STRICT | E_WARNING | E_NOTICE);

// handles exception if not caught by mvc
set_exception_handler(function (Throwable $exception) {
    // check if exception was thrown by mvc
    $error = $exception->PFUNCS_ERROR ?? [];

    // set values accordingly
    $message = $exception->getMessage();

    $code   = $error['code']    ?? $exception->getCode();
    $file   = $error['file']    ?? $exception->getFile();
    $line   = $error['line']    ?? $exception->getLine();
    $trace  = $error['trace']   ?? $exception->getTrace();
    $status = $error['status']  ?? 500;

    $file = basename($file);

    // build trace into readable string
    $trace = array_map(function ($entry) {
        $function = $entry['function'] ?? '?';
        $line     = $entry['line']     ?? '?';
        $file     = $entry['file']     ?? '?';

        if ($file !== '?') {
            $file = basename($file);
        }

        $args = implode(', ', array_map(function ($arg) {
            return is_scalar($arg) ? strval($arg) : gettype($arg);
        }, $entry['args'] ?? []));

        return "$file:$line $function($args)";
    }, $trace);

    // buffer the whole error output
    $output = PFUNCS_UID . " $file:$line $code $message\n\t" . implode("\n\t", $trace) . "\n";

    // log it
    error_log($output);

    // set the http status
    http_response_code($status);// define the content extension for error handling

    // get the correct content ext
    preg_match(
        '/\.(?P<ext>\w+)$/', parse_url(trim($_SERVER['REQUEST_URI'] ?? '', '/'),
        PHP_URL_PATH),
        $ext
    );

    // output the error depending on the content type
    switch ($ext['ext'] ?? 'html') {
        case 'json':
            die(json_encode([
                'uid'     => PFUNCS_UID,
                'file'    => $file,
                'line'    => $line,
                'code'    => $code,
                'message' => $message,
                'trace'   => $trace,
                'error'   => $output
            ], JSON_PRETTY_PRINT));
        case 'xml':
            die(
                '<?xml version="1.0" encoding="UTF-8"?><error>' .
                htmlspecialchars($output, ENT_XML1, 'UTF-8') .
                '</error>'
            );
        case 'txt':
            die($output);
        case 'js':
            die('if(typeof console.log != "undefined")console.log("' . addslashes($output) . '");');
        case 'css':
            die("/*\n$output\n*/");
        case 'html':
        default:
    }

    die('<pre>' . htmlentities($output) . '</pre>');
});

// set some path constants
$_ = $_SERVER['SERVER_NAME'] ?? 'localhost';

// begin the session
session_start();

// parse the request contents into $_REQUEST
$_REQUEST = array_merge($_REQUEST, parse_str(file_get_contents('php://input'), $request) ?? []);

// store cookies as json array so $_COOKIE can be used like $_SESSION
if (!array_key_exists($_, $_COOKIE)) {
    $_COOKIE[$_] = '{}';
}

$_COOKIE  = json_decode($_COOKIE[$_], true);

register_shutdown_function(function () use ($_) {
    $_COOKIE[$_] = json_encode($_COOKIE);
});

unset($_);