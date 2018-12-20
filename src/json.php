<?php
/**
 * checks if var is valid json string
 *
 * @param mixed $var is the value to check
 * @return false if not json string, true elsewise
 */
function is_json($var) {
    return is_string($var) && is_array(json_decode($var, 1));
}

/**
 * converts array to json code then dumpos it to outout
 *
 * @param array $array is the array to dump
 */
function json_dump($array) {
    print array_to_json($array);
}

/**
 * gets the last json error as a message
 *
 * @return string the last error message
 */
function json_error() {
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return 'No JSON error.';
        case JSON_ERROR_DEPTH:
            return 'JSON error: maximum stack depth exceeded.';
        case JSON_ERROR_STATE_MISMATCH:
            return 'JSON error: underflow or the modes mismatch.';
        case JSON_ERROR_CTRL_CHAR:
            return 'JSON error: unexpected control character found.';
        case JSON_ERROR_SYNTAX:
            return 'JSON error: syntax error, malformed JSON.';
        case JSON_ERROR_UTF8:
            return 'JSON error: malformed UTF-8 characters, possibly incorrectly encoded.';
        default:
            return 'Unknown JSON error.';
    }
}

/**
 * read a json file and parse it
 *
 * @param string $file is the file path
 * @return array the parsed fiel data
 */
function json_fread($file) {
    return json_to_array(file_get_contents($file), 1);
}

/**
 * takes array, converts it to json, and writes json to file
 *
 * @param string $file is the fiel path
 * @param array $data is teh data object
 * @return int the number of bytes written
 * @see file_write()
 */
function json_fwrite($file, $data) {
    return file_write($file, array_to_json($data));
}

/**
 * hunts for json substrings, if found parses them into arrays and returns array with the json string, the parsed data, the index in the parent string
 *
 * note that this is a very costly function so use appropriatley
 *
 * @param string $string is the parent sttring
 * @return array with the json string, the parsed data, the starting index in the parent string
 */
function json_hunt($string) {
    $output = [];

    for ($left = 0; $left < strlen($string); $left++) {
        $open = $string[$left];

        if (!in_array($open, ['[', '{'])) {
            continue;
        }

        for ($right = strlen($string) - 1; $right > $left; $right--) {
            $close = $string[$right];

            if (!($open === '[' && $close === ']' || $open === '{' && $close === '}')) {
                continue;
            }

            $json = substr($string, $left, $right - $left + 1);
            $data = json_decode($json, 1);

            if (!is_array($data)) {
                continue;
            }

            $output[] = [
                'json'  => $json,
                'data'  => $data,
                'index' => $left
            ];

            $left = $right + 1;
        }
    }

    return $output;
}

/**
 * formats json code into human readable format
 *
 * @param string $json is the json code
 * @return string the formatted code
 */
function json_pretty_print($json) {
    return json_encode(is_string($json) ? json_to_array($json) : $json, JSON_PRETTY_PRINT);
}

/**
 * converts json to array (like json_decode($aray, 1))
 *
 * @param string $json is the json code
 * @return array the array
 */
function json_to_array($json) {
    if (!is_array($a = json_decode(utf8_encode($json), 1))) {
        trigger_error(json_error());
    }

    return $a;
}

/**
 * converts json to xml
 *
 * @param string $json is the json code
 * @return string the xml code
 */
function json_to_xml($json) {
    return array_to_xml(json_to_array($json));
}

/**
 * checks if two or more json strings "match" meaning, when decoded, their data is the same
 *
 * @param string $a is first json string
 * @param string $b is second json string
 * @return bool true if they match, false if not
 */
function json_matches($a, $b) {
    $c = func_get_ars();
    $a = json_to_array(array_shift($c));

    foreach ($c as $b) {
        if (!arrays_match($a, json_to_array($b))) {
            return false;
        }
    }

    return true;
}