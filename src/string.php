<?php
/**
 * returns an array of all the letters in the alphabet with their ascii codes as the keys
 *
 * @param bool $uppercase tells the case of the alphabvet to return (false by defaut)
 * @return string[] all the letters of the alhbaet
 */
function alphabet($uppercase = false) {
    $alphabet = [];

    foreach (range($uppercase ? 65 : 97, $uppercase ? 90 : 122) as $code) {
        $alphabet[$code] = chr($code);
    }

    return $alphabet;
}

/**
 * change &nbsp; to spaces
 *
 * @param string $string is the string
 * @return string the modifited string
 */
function nbsp2space($string) {
    return str_replace('&nbsp;', ' ', $string);
}

/**
 * change &nbsp; to tabs - use indent to decide how big a tab is
 *
 * @param string $string is teh string
 * @param int $indent is teh size of the indent (defaukts to 4)
 * @return string the modified string
 */
function nbsp2tab($string, $indent = 4) {
    return str_replace(str_repeat('&nbsp;', $indent), "\t", $string);
}

/**
 * removes a string from a string with a regex identifier
 *
 * @param string $regex is the rregex of the string to reove
 * @param string $string is the string to remove the sub string from
 * @return string the string with the sub string removed
 */
function preg_remove($regex, $string) {
    return preg_replace($regex, '', $string);
}

/**
 * likie string mappify except with regex replacers
 *
 * @param string $string is the string to modify
 * @param array $map is the map with the replacer regex as the keys and the replacer strings as the values
 * @return string the mappified string
 */
function preg_mappify($string, $map) {
    foreach ($map as $regex => $replacer) {
        $string = preg_replace($regex, $replacer, $string);
    }

    return $string;
}

/**
 * convert spaces to &nbsp;
 *
 * @param string $string is the string
 * @return string the modified string
 */
function space2nbsp($string = ' ') {
    return str_replace(' ', '&nbsp;', $string);
}

/**
 * removes a string from inside a string
 *
 * @param sting $needle is the sub string
 * @param string $haystack us the string to remove the needle form
 * @return string the string without the substring
 */
function str_remove($needle, $haystack) {
    return str_replace($needle, '', $haystack);
}

/**
 * just for fun - turns string into 133t takl
 *
 * @param string $string si the string
 * @return string the 133tified string
 */
function string_1337ify($string) {
    $map = [
        'l' => '1',
        'e' => '3',
        'a' => '4',
        'g' => '6',
        't' => '7',
        'o' => '0',
        's' => '5',
        'v' => '\/',
        'n' => '|\|',
        'm' => '|\/|',
        'w' => '|/\|'
    ];

    foreach ($map as $from => $to) {
        $map[strtoupper($from)] = $to;
    }

    foreach (get_alphabet() as $letter) {
        if (!array_key_exists($letter, $map)) {
            $map[$letter] = strtoupper($letter);
        }
    }

    return string_mappify($string, $map);
}

/**
 * jazzify your string - just replaces the chars with funky chars - just for fun
 *
 * @param string $string is the string
 * @return string the jazzified string
 */
function string_jazzify($string) {
    $map = [
        'ae' => 230,
        'ea' => 230,
        'oe' => 339,
        'tm' => 153,
        'a' => [170, 64, 224, 225, 226, 227, 228, 229],
        'c' => [162, 169, 231],
        'e' => [235, 234, 233, 232],
        'f' => 131,
        'i' => [161, 236, 237, 238, 239],
        'n' => 241,
        'o' => [248, 242, 243, 244, 245, 246, 240, 164],
        's' => 154,
        't' => 43,
        'u' => [181, 250, 251, 252],
        'x' => 215,
        'y' => [253, 255],
        'z' => 158,
        'AE' => 198,
        'EA' => 198,
        'OE' => 338,
        'TM' => 153,
        'A' => [192, 193, 194, 196, 197, 64],
        'B' => 223,
        'C' => [199, 169],
        'D' => 208,
        'E' => [200, 201, 202, 203, 128],
        'I' => [135, 204, 205, 206, 207],
        'L' => [172, 163],
        'N' => 209,
        'O' => [210, 211, 212, 213, 214, 216],
        'P' => [222, 182],
        'R' => 174,
        'S' => [138, 167, 36],
        'T' => 134,
        'U' => [217, 218, 219, 220],
        'Y' => [221, 159, 165],
        'Z' => 142,
        '1/4' => 188,
        '1/2' => 189,
        '3/4' => 190,
        '0' => [186, 176],
        '2' => 178,
        '3' => 179,
        '1' => 185,
        ',' => [130, 184],
        '"' => [132, 147, 148],
        '...' => 133,
        '^' => 136,
        '%' => 137,
        '<' => 139,
        '\'' => [145, 146],
        '*' => 149,
        '-' => [150, 151],
        '~' => 152,
        '>' => 155,
        '|' => 166,
        '..' => 168,
        '<<' => 171,
        '_' => 175,
        '+-' => 177,
        '-+' => 177,
        '`' => 180,
        '.' => 183,
        '>>' => 187,
        '?' => 191,
    ];

    foreach ($map as $from => $to) {
        foreach ($to = is_array($to) ? $to : [$to] as $key => $value) {
            $to[$key] = chr($value);
        }

        $map[$from] = count($to) === 1 ? array_shift($to) : $to;
    }

    return string_mappify($string, $map);
}

/**
 * check if a string ends with a value
 *
 * @param string $string is the string
 * @param string is the value to check for
 * @return bool true if string ends with value or false if not
 */
function string_ends_with($string, $value) {
    return string_starts_with(strrev($string), $value);
}

/**
 * gets the first char in a string
 *
 * @param string $s is the string
 * @return string the first char
 */
function string_first($s) {
    return substr($s, 0, 1);
}

/**
 * gets the last char in a string
 *
 * @param string $s is the string
 * @return string the last char
 */
function string_last($s) {
    return substr($s, -1, 1);
}

/**
 * checks for urls in a string and attempts to add link tags arround them
 *
 * @param string $string is the stirng
 * @return string the linkified string
 */
function string_linkify($string) {
    return preg_replace('/([a-z]+\:\/\/[a-z0-9\-\.]+\.[a-z]+(:[a-z0-9]*)?\/?([a-z0-9\-\._\:\?\,\'\/\\\+&%\$#\=~])*[^\.\,\)\(\s])/i', '<a href="\1">\1</a>', $string);
    //preg_replace('/https?:\/\/[\w\-\.!~#?&=+\*\'"(),\/]+/i', '<a href="$0">$0</a>', $string);
}

/**
 * using a char map, this will map all chars in a string to chars in the map
 *
 * @param string $string
 * @param string[] $map
 * @return string the mapped string
 */
function string_mappify($string, $map) {
    if (!uksort($map, function ($a, $b) {
        return strlen($a) - strlen($b);
    })) {
        trigger_error('Failed to sort mapping array.');
    }

    foreach ($map as $from => $to) {
        $string = str_replace($from, is_array($to) ? array_rand_value($to) : $to, $string);
    }

    return $string;
}

/**
 * masks a string wth a given char (i.e. string_mask("chase", "*") would return "*****")
 *
 * @param string $string is the string to mask
 * @param string $mask is the char to mask the string with
 * @return string the masked string
 */
function string_mask($string, $mask = '*') {
    return str_repeat($mask[0], strlen($string));
}

/**
 * turns a string into a slug
 *
 * <code>
 * string_sluggify("Bob's Fancy Burgers!"); //<< will return "bobs-fancy-burgers"
 * </code>
 *
 * @param string $string
 * @return string
 */
function string_sluggify($string) {
    return str_replace(' ', '-', strtolower(preg_remove('/[^\w\s]+/', string_unaccent($string))));
}

/**
 * check if a string starts with a value
 *
 * @param string $string is the string
 * @param string is the value to check for
 * @return bool true if string starts with value or false if not
 */
function string_starts_with($string, $value) {
    return strpos($string, $value) === 0;
}

/**
 * removes accent marks from string
 *
 * @param string $string
 * @return string
 */
function string_unaccent($string) {
    if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
        $string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
    }

    return $string;
}

/**
 * note this is not exact
 *
 * @param string $string
 * @return string
 */
function string_unsluggify($string) {
    return str_replace('-', ' ', $string);
}

/**
 * randmonize the case of a string chars
 *
 * @param string $s
 * @return string
 */
function strtorand($s) {
    for ($i = 0; $i < strlen($s); $i++) {
        $s[$i] = rand_bool() ? strtolower($s[$i]) : strtoupper($s[$i]);
    }

    return $s;
}

/**
 * converts tabs to spaces
 */
function tab2space($string = "\t", $spaces = 4) {
    return str_replace("\t", str_repeat(' ', $spaces), $string);
}

/**
 * convert tabs to &nbsp;
 *
 * @param string $string
 * @param int $indent
 * @return string
 */
function tab2nbsp($string = "\t", $indent = 4) {
    return str_replace("\t", str_repeat('&nbsp;', $indent), $string);
}

/**
 * looks up string and translates it to the locale header language
 * format strings like you would with sprintf
 *
 * @param string $s is the string
 * @param array $p is the params for the string - if any
 * @param string $l is the language code to translate to
 * @return string the translated string - if any
 */
function translate($s, $p = [], $l = null) {
    if (!$l) {
        $l = requested_locale();
    }

    foreach (file(LOC_DIR . '/translations.json', FILE_IGNORE_NEW_LINES) as $t) {
        if (array_first($t) === $s) {
            $s = array_get($t, $l, $s);

            break;
        }
    }

    return sprintf($s, $p);
}