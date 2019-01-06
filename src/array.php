<?php
/**
 * converts an object to an associative array
 *
 * @param object $object
 * @return array
 */
function object_to_array($object) {
    $string = json_encode($object);

    if (!$string) {
        error(json_last_error_msg());
    }

    return json_to_array($string, true);
}

/**
 * calculates teh avergae of an array of numeric values
 *
 * @param number[] $a is teh array of values
 * @return number the avergae of the values in teh array
 */
function array_avg($a) {
    $sum = array_sum($a);

    return $sum ? $sum / count($a) : 0;
}

/**
 * much like array_slice except works with associative arrays
 * all keys are preserved also
 *
 * <code>
 * print implode(', ', array_body(['a' => 'A', 'b' => 'B', 'c' => 'C'], 'b', 'c')); //<< prints "B, C"
 * </code>
 *
 * @param array $array is the array
 * @param mixed $from is hte left/lower boundary of the array body
 * @param mixed $to is the right/upper boundary of the body
 * return mixed[] the body of the array
 */
function array_body($array, $from, $to) {
    $offset = array_index($array, $from);

    return $array ? array_slice($array, $offset, array_index($array, $to) - $offset + 1, 1) : [];
}

/**
 * calculate the derivitives of an array of numeric values with a custom deriving function
 *
 * <code>
 * $mph = [0 => 2, 1 => 4, 2 => 16]; //<< miles per hour
 * $acc = array_derive($mph); //<< calc acceleration - results in ["0.5" => 2, "1.5" => 12]
 * </code>
 *
 * @param number[] is the array of numbers
 * @param function $deriver the deriving function - defaults to generic compare function
 * return number[] the derived values
 */
function array_derive($array, $deriver = null) {
    if (func_num_args() < 2) {
        $deriver = function ($a, $b) {
            return $b - $a;
        };
    }

    $derivation = [];

    foreach (array_chunk($array, 2, 1) as $chunk) {
        if (isset($last, $lkey)) {
            $dkey = strval((array_first_key($chunk) - $lkey) / 2 + $lkey);
            $dval = $deriver($last, array_first($chunk));

            $derivation[$dkey] = $dval;
        }

        if (count($chunk) !== 2) {
            break;
        }

        $lkey = array_last_key($chunk);
        $last = array_last($chunk);
        $dkey = strval(($lkey - array_first_key($chunk)) / 2 + array_first_key($chunk));
        $dval = $deriver(array_first($chunk), $last);

        $derivation[$dkey] = $dval;
    }

    return $derivation;
}

/**
 * recursive version of array_filter
 *
 * @param array $array is the array
 * @param function $function is the callback
 * @param int $flag is the flag
 * @return array the filtered array
 */
function array_rfilter($array, $function = null, $flag = 0) {
    $function = $function ?? function ($v) {
        return !empty($v);
    };

    $array = array_map(function ($v) use ($function, $flag) {
        return is_array($v) ? array_rfilter($v, $function, $flag) : $v;
    }, $array);

    return array_filter($array, $function, $flag);
}

/**
 * gets the first value of an array and does not modify the array
 *
 * @param mixed[] $a is teh array
 * @return mixed the first value in the array
 */
function array_first($a, $d = null) {
    if (count($a)) {
        return array_shift($a);
    }

    if (func_num_args() > 1) {
        return $d;
    }

    error('Cannot get first element of empty array ' . spy($a) . '.');
}

/**
 * gets the first key in the array
 *
 * @param mixed[] $a is teh array
 * @return mixed the value of the first key in the array
 */
function array_first_key($a, $d = null) {
    $a = array_keys($a);

    return func_num_args() > 1 ? array_first($a, $d) : array_first($a);
}

/**
 * calcualtes the frequency of entries on an arya of timestamps
 *
 * @param timestamp[] is the array of unix timestamps
 * @return (number) the frequency
 */
function array_frequency($array) {
    return calculate_frequency(count($array), min($array), max($array));
}

/**
 * attemtps to fetcvh a value from an array and returns a default if value does not exist
 *
 * @param mixed[] $a is tejh array
 * @param mixed $k is the key
 * @param mixed $d is the default to return
 * @param bool $c case-insenstive lookup on the key
 * @return mixed the value in the aray at the given key or the default if no value at key
 */
function array_get($a, $k, $d, $c = false) {
    if (!$c) {
        return array_key_exists($k, $a) ? $a[$k] : $d;
    }

    return array_get(array_change_key_case($a, CASE_LOWER), strtolower($k), $d, false);
}

/**
 * same as array_get except searches recursively into a nested array
 *
 * @param array $array
 * @param array $keys the chain of keys
 * @param mixed $default the default
 * @param bool $case whether to search case-insensatively
 * @return mixed
 */
function array_rget($array, $keys, $default, $case = false) {
    do {
        $check = uniqid();
    } while ($check === $default);

    while ($key = array_shift($keys)) {
        $value = array_get($array, $key, $check, $case);

        if ($value === $check || $keys && !is_array($value)) {
            return $default;
        }

        $array = $value;
    }

    return $value ?? $default;
}

/**
 * glues keys to values - preserves keys
 *
 * <code>
 * array_glue(['a' => 'A', 'two' => 2], '='); //<< outputs the array ['a' => 'a=A', 'two' => 'two=2']
 * </code>
 *
 * @param string[] $array is teh key => value array
 * @param string $glue is the "glue" - the string that goes between the key and the value
 * @param string $prefix is what will be prepended to all the values
 * @param string $suffix is what will be appended to all the values
 * @return string[] the glues array
 */
function array_glue($array, $glue = '=', $prefix = '', $suffix = '') {
    foreach ($array as $key => $value) {
        $array[$key] = $key . $glue . $value;
    }

    return array_prefix(array_suffix($array, $suffix), $prefix);
}

/**
 * cuts off the tail of an array
 *
 * @param mixed[] $array is the array
 * @param mixed $to is the key to cut the array at
 * @return mixed[] $array the left part of the cut array
 */
function array_head($array, $to) {
    return array_body($array, array_first_key($array), $to);
}

/**
 * gets the numeric index of a key in an array
 *
 * @param mixed[] $array is the array
 * @param mixed $key is the key
 * @return int the index of the key in the array
 */
function array_index($array, $key) {
    foreach (array_keys($array) as $index => $value) {
        if ($key === $value) {
            return $index;
        }
    }

    error('Failed to get index of ' . spy($key) . ' in ' . spy($array) . '.');
}

/**
 * checks if an array is indexed or associative
 *
 * @param mixed[] $array i sthe array in question
 * @return bool true if the arrya is indexed - false elsewise
 */
function array_indexed($array) {
    return array_keys($array) === range(0, count($array) - 1);
}

/**
 * gets the (first) key associated for a value in an array
 *
 * @param mixed[] $array is the array
 * @param mixed $value is the value
 * @param bool strict flag to use strict comparison or loose comparison
 * @return mixed the key for the value in the array - if no key found an error is thrown
 */
function array_key($array, $value, $strict = 1) {
    $key = array_search($value, $array, $strict);

    if ($key === false) {
        error('No key found for ' . spy($value) . ' in ' . spy($array) . '.');
    }

    return $key;
}

/**
 * wrapper of krsort except does not modifiy array and returns it instead
 *
 * @param array $array is the array
 * @param int $flags is the flags
 * @return array the sorted array
 */
function array_krsort($array, $flags = SORT_REGULAR) {
    if (!krsort($array, $flags)) {
        error('Failed to sort ' . spy($array) . ' by keys in reverse.');
    }

    return $array;
}

/**
 * a wrapper for ksort and uksort in that if ints are passed for flags then ksort is called
 * but if function is passed for flags then uksort is called
 *
 * @param mixed[] $array is the array
 * @param mixed $flags is the flags or the sorting functiion
 * @return array the sorted array
 */
function array_ksort($array, $flags = SORT_REGULAR) {
    $callable = is_callable($flags);

    if (
        $callable && uksort($array, $flags) ||
        !$callable && !ksort($array, $flags) ||
        !$callable && !is_whole($flags)
    ) {
        error('Failed to sort ' . spy($array) . ' by keys.');
    }

    return $array;
}

/**
 * gets the last element of an array
 *
 * @param mixed[] $a is the array
 * @return mixed the last element in the array
 */
function array_last($a, $d = null) {
    if (count($a)) {
        return array_pop($a);
    }

    if (func_num_args() > 1) {
        return $d;
    }

    error('Cannot get last element of empty array ' . spy($a) . '.');
}

/**
 * gets the last key in an array
 *
 * @param mixed[] $a is the array
 * @return mixed the last key in the array
 */
function array_last_key($a, $d = null) {
    $a = array_keys($a);

    return func_num_args() > 1 ? array_last($a, $d) : array_last($a);
}

/**
 * recursive array_map
 *
 * @param function $function is the mapping function
 * @param array $array is the array
 * @return array the mapped array
 */
function array_map_recursive($function, $array) {
    foreach ($array as &$value) {
        if (is_array($value)) {
            $value = array_map_recursive($function, $value);
        }
    }

    return array_map($function, $array);
}

/**
 * prefix an array of strings
 *
 * @param array $array
 * @param string $prefix
 * @return array
 */
function array_prefix($array, $prefix) {
    foreach ($array as $key => $string) {
        $array[$key] = $prefix . $string;
    }

    return $array;
}

/**
 * gets a random value in the array
 *
 * @param mixed[] $a is the array
 * @return mixed a random value in the array
 */
function array_rand_value($a) {
    return $a[array_rand($a)];
}

/**
 * like rsort except does not modify array and retruns it instead
 *
 * @param mixed[] $array is teh array
 * @param int|function $flags is the flags or compare function
 * @return array the srted array
 */
function array_rsort($array, $flags = SORT_REGULAR) {
    return array_reverse(array_sort($array, $flags));
}

/**
 * takes out a value in the array and places it at the head of teh array
 *
 * @param mixed[] $array is the arrya
 * @param mixed $key is the key of teh array
 * @return array the new array
 */
function array_set_first($array, $key) {
    $first = $array[$key];

    unset($array[$key]);

    return array_merge([$key => $first], $array);
}

/**
 * takes a value in the array and sets it as the first value in the array
 *
 * @param mixed[] $array is the array
 * @param mixed $key is the key of the element toset as last
 * @return mixed[] the new array
 */
function array_set_last($array, $key) {
    $last = $array[$key];

    return array_merge($array, [$key => $last]);
}

/**
 * a wrapper for shuffle except returns array instad of modifying it
 *
 * @param mixed[] $array is teh array
 * @return mixed[] the shuffled array
 */
function array_shuffle($array) {
    if (!shuffle($array)) {
        error('Failed to shuffle array ' . spy($array) . '.');
    }

    return $array;
}

/**
 * a wrapper for sort and usort except doesnt modify array and returns it instead
 *
 * @param mixed[] $array is the array
 * @param int|function flags is either the int flags or the sorter function
 * return (array) the sorted array
 */
function array_sort($array, $flags = SORT_REGULAR) {
    $callable = is_callable($flags);

    if (
        $callable && !uasort($array, $flags) ||
        !$callable && !asort($array, $flags) ||
        !$callable && !is_whole($flags)
    ) {
        error('Failed to sort array ' . spy($array) . '.');
    }

    return $array;
}

/**
 * append a suffix to an array of strings
 *
 * @param array $array
 * @param string $suffix
 * @return array
 */
function array_suffix($array, $suffix) {
    foreach ($array as $key => $string) {
        $array[$key] = $string . $suffix;
    }

    return $array;
}

/**
 * gets the tail-end of an array
 *
 * @param mixed[] $array is the array
 * @param mixed $from is the key to start the tail at
 * @return mixed[] the tail end of the array
 */
function array_tail($array, $from) {
    return array_body($array, $from, array_last_key($array));
}

/**
 * a wrapper for json_encode
 *
 * @param mixed[] $a is the array
 * @return string the array in json
 */
function array_to_json($a) {
    $json = json_encode($a);

    if (!is_string($json)) {
        error(json_last_error_msg());
    }

    return $json;
}

/**
 * converts an array to xml
 *
 * @param mixed[] $array is teh array
 * @return string the array as xml
 */
function array_to_xml($array) {
    return json_to_xml(array_to_json($array));
}

/**
 * unglues a glued array - see array_glue
 *
 * <code>
 * array_unglue(['a=A', 'two=2'], '='); //<< outputs the array ['a' => 'A', 'two' => 2]
 * </code>
 *
 * @param string[] $array is teh glued array
 * @param string $glue is the "glue" - the string gluing the key to the value
 * @param string $prefix is the prefix
 * @param string $suffix is the suffix
 * @return string[] the array of unglued values and keys
 */
function array_unglue($array, $glue = '=', $prefix = '', $suffix = '') {
    $unglued = [];

    foreach ($array as $value) {
        $pre = strstr($prefix, $value);
        $suf = strstr($suffix, strrev($value));

        if ($pre === 0) {
            $value = substr($value, 0, strlen($prefix) - 1);
        }

        if ($suf === 0) {
            $value = strrev(substr(strrev($value), 0, strlen($suffix) - 1));
        }

        $key   = array_first($value = explode($glue, $value));
        $value = array_pop($value);

        $unglued[$key] = $value;
    }

    return $unglued;
}

/**
 * takes a value, checks if it's an array, if so it returns it, of not it wraps it in an aray and returns it
 *
 * @param mixed $value is the value
 * @return array
 */
function array_value($value) {
    return is_array($value) ? $value : [$value];
}

/**
 * checks if two or more arrays "match" meaning they all have the same key/value pairs
 *
 * @param array $a is the first array
 * @param array $b is the secind array
 * @return bool true if they match, false elsewise
 */
function arrays_match($a, $b) {
    $c = func_get_args();
    $a = array_shift($c);

    foreach ($c as $b) {
        foreach ($a as $k => $v) {
            if (!array_key_exists($k, $b) || !is_array($v) && $b[$k] !== $v || is_array($v) && !arrays_match($v, $b[$k])) {
                return false;
            }
        }
    }

    return true;
}