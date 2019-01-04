<?php
/**
 * convert a length of time from one unit to another
 *
 * s = seconds, m = minutes, h = hours, d = days, w = weeks, M = months, y = years
 *
 * @param number $units is the length of time
 * @param string $from is the unit to converty from
 * @param string $to is the unit to convert to
 * @return number the length of time converted
 */
function convert_time($units, $from, $to) {
    $to   = string_first($to);
    $from = string_first($from);

    $rates = [
        's' => 1,
        'm' => 60,
        'h' => 60 * 60,
        'd' => 60 * 60 * 24,
        'w' => 60 * 60 * 24 * 7,
        'M' => 2.62974e6,
        'y' => 2.62974e6 * 12
    ];

    return $units * $rates[$from] / $rates[$to];
}