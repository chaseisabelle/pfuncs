<?php
foreach (scandir(__DIR__) as $_) {
    if (strpos($_, '.') !== 0) {
        include_once(realpath(__DIR__ . "/$_"));
    }
}

unset($_);