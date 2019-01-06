<?php
define('PFUNCS_UID', uniqid());

$_ = 'www';

if (php_sapi_name() === 'cli') {
    $_ = 'cli';
}

include_once(realpath(__DIR__ . "/../env/$_.php"));

date_default_timezone_set('UTC');

foreach (scandir(__DIR__) as $_) {
    if (strpos($_, '.') !== 0) {
        include_once(realpath(__DIR__ . "/$_"));
    }
}

unset($_);