<?php
/**
 * pfunc error triggerer - throws exception
 */
function pfunc_error($message) {
    throw new Exception($message);
}