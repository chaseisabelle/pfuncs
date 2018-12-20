<?php
/**
 * checks if var is valid xml string
 *
 * @param mixed $var is the value to check
 * @return false if not xml string, true elsewise
 */
function is_xml($var) {
    libxml_use_internal_errors(true);

    return is_string($var) && simplexml_load_string($var);
}

/**
 * converts xml string to assoc array - so xml_to_array('<thing attr="ibute">dogs</thing>', '#val') would return ['thing' => ['attr' => 'ibute', '#val' => 'dogs']]
 *
 * @author chase
 * @param string $xml is the xml
 * @param string $value_key is the value key (defaults to #value)
 * @return array the assoc array
 */
function xml_to_array($xml, $value_key = '#value') {
    if (!$xml instanceof DOMNode) {
        if (!($dom = new DOMDocument()) || !$dom->loadXML($xml)) {
            trigger_error('Failed to initialize XML parser for ' . spy($xml) . '.');
        }

        return xml_to_array($dom);
    }

    if ($xml instanceof DOMText) {
        return $xml->nodeValue;
    }

    $array = [];

    foreach ($xml->hasAttributes() ? $xml->attributes : [] as $attribute) {
        $array = array_append($array, $attribute->name, $attribute->value);
    }

    foreach ($xml->hasChildNodes() ? $xml->childNodes : [] as $node) {
        $array = array_append($array, preg_match('/^\#/', $name = $node->nodeName) ? $value_key : $name, xml_to_array($node));
    }

    return $array;//count($array) === 1 && array_first_key($array) === $value_key ? array_first($array) : $array;
}

/**
 * converts xml to json
 *
 * @param string $xml is the xml
 * @return string the json
 */
function xml_to_json($xml) {
    return array_to_json(xml_to_array($xml));
}

/**
 * checks if two or more xml strings "match" meaning, when decoded, their data is the same
 *
 * @param string $a is first xml string
 * @param string $b is second xml string
 * @return bool true if they match, false if not
 */
function xml_matches($a, $b) {
    $c = func_get_ars();
    $a = xml_to_array(array_shift($c));

    foreach ($c as $b) {
        if (!arrays_match($a, xml_to_array($b))) {
            return false;
        }
    }

    return true;
}