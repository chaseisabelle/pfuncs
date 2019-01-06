<?php
// throwable interface for errors - throws exception on error
function error($error) {
    if (is_array($error)) {
        $message = $error['message'] ?? 'Unknown error.';
        $file    = $error['file'] ?? __FILE__;
        $line    = $error['line'] ?? __LINE__;
        $code    = $error['code'] ?? null;
    } else {
        $message = $error;
    }

    $error = new Error($message, $code);

    $error->PFUNCS_ERROR = [
        'message' => $message,
        'file'    => $file ?? __FILE__,
        'line'    => $line ?? __LINE__,
        'trace'   => debug_backtrace(false),
        'code'    => $code
    ];

    throw $error;
}

// throwable interface for errors - throws exception on error
set_error_handler(function ($code, $message, $file, $line) {
    error([
        'message' => $message,
        'file'    => $file,
        'line'    => $line,
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

    // output the error
    die($output);
});