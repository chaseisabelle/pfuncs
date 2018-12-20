<?php
/**
 * get the ordinal for a number
 *
 * <code>
 * ordinal(10); //<< returns 'th' for 10th
 * ordinal(3); //<< returns 'rd' for 3rd
 * </code>
 *
 * @param int $n is the number
 * @return string the ordinal
 */
function ordinal($n) {
    $m = abs($n) % 10;

    return (abs($n) % 100 < 21 && abs($n) % 100 > 4) ? 'th' : (($m < 4) ? ($m < 3) ? ($m < 2) ? ($m < 1) ? 'th' : 'st' : 'nd' : 'rd' : 'th');
}

/**
 * a wraper for base_convert - idk why i even made this one
 *
 * @param number $n is the number to convert
 * @param int $f is the base to convert from
 * @param int $t is the base to convert to
 * @return number the converted number
 */
function convert_numeric_base($n, $f, $t) {
    return floatval(base_convert($n, $f, $t));
}

/**
 * checks if a number is between two otehr numbers (inclusively)
 *
 * @param number $number is the number in question
 * @param number $min the lower bound
 * @param number #max the upper bound
 * @param bool $inclusive check inclusively if set to true
 * @return bool true if number is between mix and max, false elsewise
 */
function in_range($number, $min, $max, $inclusive = false) {
    return $inclusive && $number >= $min && $number <= $max || !$inclusive && $number > $min && $number < $max;
}

/**
 * checks if var is a number buit not whole number
 *
 * @param mixed $var is the cariable ti check
 * @return bool true if var is decimal and false elsewise
 */
function is_decimal($var) {
    return is_numeric($var) && !is_whole($var);
}

/**
 * checks if number is whole
 *
 * @param mixed $number is the number to check
 * @return bool true if number is whole and false elsewise
 */
function is_whole($number) {
    return is_numeric($number) && intval($number) - $number === 0;
}

/**
 * calculates the frequency for a count of an occurance within a boundary of time
 *
 * @param int $count is the count of the occurance
 * @param int $from is the timestamp for the start of the interval
 * @param int $to is the timestamp for the end of the interval
 * @return number the frequency (i.e. count per format)
 */
function calculate_frequency($count, $from, $to) {
    $interval = $to - $from;

    if (!$interval) {
        return INF;
    }

    return $count / $interval;
}

/**
 * calculates the percentage for a part over a whole
 *
 * @param number $part is the part/fraction
 * @param number $whole is the whole/total
 * @return number the percentage
 */
function calculate_percentage($part, $whole) {
    return $whole ? $part / $whole * 100 : 0;
}