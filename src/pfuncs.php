<?php
foreach (scandir(__DIR__) as $_) {
    include_once(realpath(__DIR__ . "/$_"));
}

unset($_);