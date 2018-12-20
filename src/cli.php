<?php
/**
 * prompts user with question and returns 0/1 for matching answer
 *
 * @param string $question is teh question
 * @param string $answer is teh expected answer
 * @param bool $case set true to be case sensative - defaults to false
 * @return bool true if correct false elsewise
 */
function ask($question, $answer, $case = 0) {
    return !call_user_func('str' . ($case ? '' : 'case') . 'cmp', prompt($question), $answer);
}

/**
 * reads from stdin
 *
 * @return string the input
 */
function input() {
    return fgets(STDIN);
}

/**
 * checks if script is running in cli env
 *
 * @return bool true if running in cli env and false elsewise
 */
function is_cli() {
    return php_sapi_name() === 'cli';
}

/**
 * prints string to stdout
 *
 * @param string $output is the line to print
 */
function output($output) {
    print $output;
}

/**
 * print a line with new line at end
 *
 * @param string $line
 */
function println($line) {
    output("$line\n");
}

/**
 * prompts user for input
 *
 * @param string $prompt is teh prompt
 * @return string the input
 */
function prompt($prompt) {
    output($prompt);

    return trim(input());
}